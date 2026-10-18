<?php
if (!defined('ABSPATH')) { exit; }
function awsw_activate() {
    global $wpdb;
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    $c = $wpdb->get_charset_collate(); $p = $wpdb->prefix;
    dbDelta("CREATE TABLE {$p}awsw_donations (
        id bigint unsigned NOT NULL AUTO_INCREMENT,
        reference varchar(48) NOT NULL,
        submission_hash char(64) NOT NULL,
        transfer_hash char(64) NOT NULL,
        campaign_id bigint unsigned NOT NULL DEFAULT 0,
        amount_minor bigint unsigned NOT NULL,
        status varchar(24) NOT NULL DEFAULT 'pending',
        donor_name varchar(190) NOT NULL,
        donor_email varchar(190) NOT NULL,
        donor_phone varchar(40) NOT NULL DEFAULT '',
        anonymous tinyint NOT NULL DEFAULT 0,
        marketing tinyint NOT NULL DEFAULT 0,
        transfer_ref varchar(190) NOT NULL,
        proof_file varchar(100) NOT NULL DEFAULT '',
        proof_mime varchar(40) NOT NULL DEFAULT '',
        language varchar(2) NOT NULL DEFAULT 'en',
        email_state varchar(20) NOT NULL DEFAULT 'not_sent',
        created_at datetime NOT NULL,
        updated_at datetime NOT NULL,
        PRIMARY KEY  (id),
        UNIQUE KEY reference (reference),
        UNIQUE KEY submission_hash (submission_hash),
        UNIQUE KEY transfer_hash (transfer_hash),
        KEY campaign_status (campaign_id,status),
        KEY created_at (created_at)
    ) ENGINE=InnoDB $c;");
    dbDelta("CREATE TABLE {$p}awsw_audit (
        id bigint unsigned NOT NULL AUTO_INCREMENT,
        donation_id bigint unsigned NOT NULL,
        actor_id bigint unsigned NOT NULL,
        old_status varchar(24) NOT NULL,
        new_status varchar(24) NOT NULL,
        note text NOT NULL,
        created_at datetime NOT NULL,
        PRIMARY KEY  (id),
        KEY donation_id (donation_id)
    ) ENGINE=InnoDB $c;");
    dbDelta("CREATE TABLE {$p}awsw_enquiries (
        id bigint unsigned NOT NULL AUTO_INCREMENT,
        kind varchar(16) NOT NULL,
        payload longtext NOT NULL,
        email_state varchar(20) NOT NULL DEFAULT 'not_sent',
        created_at datetime NOT NULL,
        PRIMARY KEY  (id)
    ) ENGINE=InnoDB $c;");
    add_role('awsw_donation_manager', 'Donation Manager', ['read' => true, 'awsw_manage_donations' => true, 'awsw_export_donations' => true]);
    if ($role = get_role('administrator')) { foreach (['awsw_manage_donations','awsw_export_donations','awsw_manage_settings','awsw_manage_enquiries'] as $cap) { $role->add_cap($cap); } }
    awsw_register_content(); flush_rewrite_rules(); update_option('awsw_db_version', AWSW_VERSION);
}
function awsw_register_content() {
    foreach (['campaign' => ['Campaigns','Campaign'], 'update' => ['Project updates','Project update'], 'report' => ['Reports','Report'], 'metric' => ['Verified metrics','Verified metric']] as $key => $label) {
        register_post_type('awsw_' . $key, ['labels' => ['name' => $label[0], 'singular_name' => $label[1]], 'public' => true, 'has_archive' => true, 'rewrite' => ['slug' => $key === 'campaign' ? 'campaign' : $key], 'show_in_rest' => true, 'supports' => ['title','editor','excerpt','thumbnail','revisions','custom-fields'], 'menu_icon' => $key === 'campaign' ? 'dashicons-heart' : 'dashicons-portfolio', 'capability_type' => 'post', 'map_meta_cap' => true]);
    }
    register_taxonomy('awsw_program', ['awsw_campaign','awsw_update','awsw_report','awsw_metric'], ['label' => 'Program', 'public' => true, 'hierarchical' => true, 'show_in_rest' => true]);
    foreach (['food' => 'Food & ration','education' => 'Education','water' => 'Clean water','medical' => 'Medical support'] as $slug => $name) { if (!term_exists($slug, 'awsw_program')) { wp_insert_term($name, 'awsw_program', ['slug' => $slug]); } }
}
// PKR represented in paisa. No float conversion or arithmetic.
function awsw_parse_money($value) {
    if (!is_string($value) || !preg_match('/^(0|[1-9][0-9]{0,8})(?:\.([0-9]{1,2}))?$/D', trim($value), $m)) { return false; }
    return ((int) $m[1] * 100) + (int) str_pad($m[2] ?? '', 2, '0');
}
function awsw_money($minor) { $n = (int) $minor; return 'PKR ' . number_format(intdiv($n, 100)) . '.' . str_pad((string) ($n % 100), 2, '0', STR_PAD_LEFT); }
function awsw_total($campaign = null, $from = '', $to = '') {
    global $wpdb; $sql = "SELECT COALESCE(SUM(amount_minor),0) FROM {$wpdb->prefix}awsw_donations WHERE status='confirmed'"; $args = [];
    if ($campaign !== null) { $sql .= ' AND campaign_id=%d'; $args[] = (int) $campaign; }
    if ($from) { $sql .= ' AND created_at >= %s'; $args[] = $from; } if ($to) { $sql .= ' AND created_at < %s'; $args[] = $to; }
    return (int) $wpdb->get_var($args ? $wpdb->prepare($sql, $args) : $sql);
}
function awsw_progress($id) {
    $target = (int) get_post_meta($id, '_awsw_target', true); $raised = awsw_total($id);
    return ['target' => $target, 'raised' => $raised, 'remaining' => max(0, $target - $raised), 'percent' => $target > 0 ? round($raised * 100 / $target, 1) : null];
}
function awsw_donation($id) { global $wpdb; return $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}awsw_donations WHERE id=%d", $id)); }
function awsw_acknowledge($id) {
    global $wpdb; $d = awsw_donation($id); if (!$d || $d->status !== 'confirmed') { return false; }
    $ur = $d->language === 'ur';
    $subject = $ur ? 'AWS Welfare: عطیہ کی تصدیق' : 'AWS Welfare: donation confirmed';
    $body = ($ur ? "آپ کے عطیے کی تصدیق ہو گئی ہے۔\nحوالہ: " : "Your donation has been verified.\nReference: ") . $d->reference . "\n" . awsw_money($d->amount_minor) . "\n\n" . ($ur ? 'یہ عطیہ وصول ہونے کی تصدیق ہے، ٹیکس رسید نہیں۔' : 'This is a donation acknowledgment, not a tax receipt.');
    $sent = wp_mail($d->donor_email, $subject, $body);
    $wpdb->update($wpdb->prefix . 'awsw_donations', ['email_state' => $sent ? 'accepted' : 'failed'], ['id' => $id]);
    return $sent;
}
function awsw_transition($id, $status, $note) {
    $note = sanitize_textarea_field($note);
    if (!current_user_can('awsw_manage_donations')) { return new WP_Error('forbidden', 'Permission denied.'); }
    if (!in_array($status, ['confirmed','rejected','refunded'], true) || !trim($note) || mb_strlen($note) > 3000) { return new WP_Error('invalid', 'Choose a status and record a reason.'); }
    global $wpdb; $wpdb->query('START TRANSACTION');
    $d = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}awsw_donations WHERE id=%d FOR UPDATE", $id));
    if (!$d) { $wpdb->query('ROLLBACK'); return new WP_Error('missing', 'Donation not found.'); }
    if ($d->status === $status) { $wpdb->query('ROLLBACK'); return true; }
    $allowed = ['pending' => ['confirmed','rejected'], 'confirmed' => ['refunded'], 'rejected' => [], 'refunded' => []];
    if (!in_array($status, $allowed[$d->status] ?? [], true)) { $wpdb->query('ROLLBACK'); return new WP_Error('transition', 'This status transition is not permitted.'); }
    $updated = $wpdb->update($wpdb->prefix . 'awsw_donations', ['status' => $status,'updated_at' => current_time('mysql', true)], ['id' => $id]);
    $logged = $wpdb->insert($wpdb->prefix . 'awsw_audit', ['donation_id' => $id,'actor_id' => get_current_user_id(),'old_status' => $d->status,'new_status' => $status,'note' => sanitize_textarea_field($note),'created_at' => current_time('mysql', true)]);
    if ($updated === false || !$logged) { $wpdb->query('ROLLBACK'); return new WP_Error('database', 'Could not record verification. No change saved.'); }
    $wpdb->query('COMMIT');
    if ('confirmed' === $status) { awsw_acknowledge($id); }
    do_action('awsw_donation_status_changed', $id, $d->status, $status);
    return true;
}
function awsw_private_dir() {
    if (!defined('AWSW_PRIVATE_DIR')) { return false; }
    $path = realpath(AWSW_PRIVATE_DIR); $root = realpath(ABSPATH); $doc = isset($_SERVER['DOCUMENT_ROOT']) ? realpath($_SERVER['DOCUMENT_ROOT']) : false;
    if (!$path || !is_dir($path) || !is_writable($path)) { return false; }
    foreach (array_filter([$root, $doc]) as $public) { if ($path === $public || str_starts_with($path . '/', rtrim($public, '/') . '/')) { return false; } }
    return $path;
}
// Preserve the campaign association once financial records exist. Unpublish instead.
add_filter('pre_delete_post', function ($delete, $post, $force_delete) {
    if ($post->post_type === 'awsw_campaign') {
        global $wpdb;
        if ($wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->prefix}awsw_donations WHERE campaign_id=%d", $post->ID))) { return false; }
    }
    return $delete;
}, 10, 3);
