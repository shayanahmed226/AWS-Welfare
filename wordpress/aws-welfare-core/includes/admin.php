<?php
if (!defined('ABSPATH')) { exit; }
add_action('admin_menu', function () {
    add_menu_page('AWS Welfare', 'AWS Welfare', 'awsw_manage_donations', 'awsw-dashboard', 'awsw_admin_dashboard', 'dashicons-heart', 26);
    add_submenu_page('awsw-dashboard', 'Donations', 'Donations', 'awsw_manage_donations', 'awsw-donations', 'awsw_admin_donations');
    add_submenu_page('awsw-dashboard', 'Organization settings', 'Organization settings', 'awsw_manage_settings', 'awsw-settings', 'awsw_admin_settings');
});
function awsw_admin_guard($cap = 'awsw_manage_donations') { if (!current_user_can($cap)) { wp_die('Permission denied.', '', ['response' => 403]); } }
function awsw_admin_start($title) { echo '<div class="wrap"><h1>' . esc_html($title) . '</h1>'; if (isset($_GET['awsw_saved'])) { echo '<div class="notice notice-success"><p>Changes saved. Email “accepted” means passed to the mail transport, not verified delivery.</p></div>'; } }
function awsw_admin_dashboard() {
    awsw_admin_guard(); global $wpdb; awsw_admin_start('AWS Welfare · Operations');
    $pending = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}awsw_donations WHERE status='pending'");
    $active = new WP_Query(['post_type' => 'awsw_campaign','post_status' => 'publish','meta_key' => '_awsw_status','meta_value' => 'active','posts_per_page' => 1]);
    echo '<div class="awsw-admin-stats"><article><span>Net confirmed · all time</span><strong>'.esc_html(awsw_money(awsw_total())).'</strong></article><article><span>Pending verification</span><strong>'.$pending.'</strong></article><article><span>Active campaigns</span><strong>'.(int)$active->found_posts.'</strong></article></div>';
    echo '<p>Times display in Asia/Karachi. Amounts include only confirmed records; refunded donations are excluded. Project delivery progress is edited separately.</p>';
    echo '<p><a class="button button-primary" href="' . esc_url(admin_url('admin.php?page=awsw-donations')) . '">Review donations</a></p>';
    echo '<h2>Campaign confirmed totals</h2><ul>';
    foreach (get_posts(['post_type' => 'awsw_campaign','numberposts' => 50]) as $p) { echo '<li>' . esc_html($p->post_title . ' — ' . awsw_money(awsw_total($p->ID))) . '</li>'; }
    echo '</ul><h2>Recent donation submissions</h2><ul>';
    foreach ($wpdb->get_results("SELECT id,reference,status,amount_minor,created_at FROM {$wpdb->prefix}awsw_donations ORDER BY id DESC LIMIT 10") as $d) { echo '<li><a href="' . esc_url(admin_url('admin.php?page=awsw-donations&id=' . $d->id)) . '">' . esc_html($d->reference) . '</a> — ' . esc_html($d->status . ' · ' . awsw_money($d->amount_minor) . ' · ' . awsw_time($d->created_at)) . '</li>'; }
    echo '</ul></div>';
}
function awsw_time($utc) { return wp_date('Y-m-d H:i T', strtotime($utc . ' UTC'), new DateTimeZone('Asia/Karachi')); }
function awsw_admin_settings() {
    awsw_admin_guard('awsw_manage_settings'); awsw_admin_start('Organization & bank settings');
    echo '<p>Never enter unverified bank details. Without an account title, bank name and account number or valid Pakistani IBAN, payment submission remains disabled.</p>';
    echo '<p>Private proofs: <strong>' . (awsw_private_dir() ? 'configured outside public web root' : 'disabled — define AWSW_PRIVATE_DIR in wp-config.php to an existing private writable directory outside the public web root') . '</strong></p>';
    echo '<form action="' . esc_url(admin_url('admin-post.php')) . '" method="post"><input type="hidden" name="action" value="awsw_settings">'; wp_nonce_field('awsw_settings');
    $fields = ['org_email'=>'Public email','org_phone'=>'Public telephone','whatsapp'=>'Official WhatsApp (international digits only)','office_hours'=>'Confirmed office hours','social_facebook'=>'Official Facebook URL','social_instagram'=>'Official Instagram URL','social_linkedin'=>'Official LinkedIn URL','address'=>'Confirmed address','registration'=>'Verified registration details','team'=>'Approved team/governance details','notification_email'=>'Admin notification email','announcement_en'=>'Announcement (English)','announcement_ur'=>'Announcement (Urdu)','bank_title'=>'Account title','bank_name'=>'Bank name','bank_account'=>'Account number','bank_iban'=>'Pakistani IBAN','bank_branch'=>'Branch details','bank_qr'=>'Approved QR image URL (Media Library)','retention_days'=>'Enquiry / unconfirmed record retention days (review manually)'];
    foreach ($fields as $key => $label) { echo '<p><label for="s-' . esc_attr($key) . '"><strong>' . esc_html($label) . '</strong></label><br><input class="regular-text" id="s-' . esc_attr($key) . '" name="settings[' . esc_attr($key) . ']" value="' . esc_attr(awsw_setting($key)) . '" type="' . (str_contains($key, 'email') ? 'email' : 'text') . '"></p>'; }
    echo '<p><label><input type="checkbox" name="settings[policies_reviewed]" value="1" ' . checked(awsw_setting('policies_reviewed'), '1', false) . '> Organization has reviewed and approved the policy texts</label></p>';
    submit_button('Save verified settings'); echo '</form><hr><h2>Starter pages</h2><p>Creates missing starter pages only. Existing pages are never overwritten. Policies remain drafts for review. No campaigns, donations, photos or impact numbers are invented.</p><form action="' . esc_url(admin_url('admin-post.php')) . '" method="post"><input type="hidden" name="action" value="awsw_pages">'; wp_nonce_field('awsw_pages'); submit_button('Create missing starter pages', 'secondary'); echo '</form><h2>Privacy and retention</h2><p>Deletion and retention run only on explicit administrator action. Financial records are never silently deleted. Use WordPress Tools → Export/Erase Personal Data for a verified email request. Erasure anonymizes donor contact details and deletes proofs, preserving amounts and verification history. Enquiries are removed. Consult the organization’s retention policy before processing.</p></div>';
}
add_action('admin_post_awsw_settings', function () {
    awsw_admin_guard('awsw_manage_settings'); check_admin_referer('awsw_settings'); $raw = isset($_POST['settings']) && is_array($_POST['settings']) ? wp_unslash($_POST['settings']) : []; $s = awsw_settings();
    $keys = ['org_email','org_phone','whatsapp','office_hours','social_facebook','social_instagram','social_linkedin','address','registration','team','notification_email','announcement_en','announcement_ur','bank_title','bank_name','bank_account','bank_iban','bank_branch','bank_qr','retention_days','policies_reviewed'];
    foreach ($keys as $k) { $v = isset($raw[$k]) && is_scalar($raw[$k]) ? (string) $raw[$k] : ''; $s[$k] = str_starts_with($k, 'social_') ? esc_url_raw($v,['https']) : (str_contains($k, 'email') ? sanitize_email($v) : ($k === 'bank_qr' ? esc_url_raw($v, ['https']) : sanitize_text_field($v))); }
    $s['bank_iban'] = strtoupper(preg_replace('/\s+/', '', $s['bank_iban']));
    if ($s['bank_iban'] && !awsw_valid_iban($s['bank_iban'])) { wp_die('Invalid Pakistani IBAN. Settings were not changed.'); }
    if ($s['whatsapp'] && !preg_match('/^[1-9][0-9]{6,14}$/D', $s['whatsapp'])) { wp_die('WhatsApp must use international digits only.'); }
    if ($s['bank_account'] && !preg_match('/^[0-9 -]{6,40}$/D', $s['bank_account'])) { wp_die('Check the bank account number.'); }
    update_option('awsw_settings', $s); wp_safe_redirect(admin_url('admin.php?page=awsw-settings&awsw_saved=1')); exit;
});
function awsw_valid_iban($iban) {
    if (!preg_match('/^PK[0-9]{2}[A-Z]{4}[0-9]{16}$/D', $iban)) { return false; }
    $value = substr($iban, 4) . substr($iban, 0, 4); $remainder = 0;
    foreach (str_split($value) as $char) { $digits = ctype_alpha($char) ? (string) (ord($char) - 55) : $char; foreach (str_split($digits) as $digit) { $remainder = ($remainder * 10 + (int) $digit) % 97; } }
    return $remainder === 1;
}
function awsw_admin_donations() {
    awsw_admin_guard(); global $wpdb; awsw_admin_start('Donation verification'); $id = absint($_GET['id'] ?? 0);
    if ($id) {
        $d = awsw_donation($id); if (!$d) { echo '<p>Record not found.</p></div>'; return; }
        echo '<h2>' . esc_html($d->reference) . '</h2><dl>';
        foreach (['status','donor_name','donor_email','donor_phone','transfer_ref','anonymous','marketing','email_state'] as $key) { echo '<dt><strong>' . esc_html($key) . '</strong></dt><dd>' . esc_html($d->$key) . '</dd>'; }
        echo '<dt>Amount</dt><dd>' . esc_html(awsw_money($d->amount_minor)) . '</dd><dt>Campaign</dt><dd>' . esc_html($d->campaign_id ? get_the_title($d->campaign_id) : 'General fund') . '</dd><dt>Submitted (Karachi)</dt><dd>' . esc_html(awsw_time($d->created_at)) . '</dd></dl>';
        if ($d->proof_file) { echo '<p><a class="button" href="' . esc_url(wp_nonce_url(admin_url('admin-post.php?action=awsw_proof&id=' . $id), 'awsw_proof_' . $id)) . '">Download private proof</a></p>'; }
        $options = $d->status === 'pending' ? ['confirmed' => 'Confirm after matching bank statement','rejected' => 'Reject'] : ($d->status === 'confirmed' ? ['refunded' => 'Record completed refund'] : []);
        if ($options) { echo '<h3>Verify against the bank statement</h3><p>A screenshot alone is not proof of settled funds. A refund status records a refund completed outside this website; it does not move money.</p><form method="post" action="' . esc_url(admin_url('admin-post.php')) . '"><input type="hidden" name="action" value="awsw_transition"><input type="hidden" name="id" value="' . $id . '">'; wp_nonce_field('awsw_transition_' . $id); echo '<p><label>Status <select name="status">'; foreach ($options as $v => $label) { echo '<option value="' . esc_attr($v) . '">' . esc_html($label) . '</option>'; } echo '</select></label></p><p><label>Verification / refund reason (required)<br><textarea name="note" required rows="4" cols="60" maxlength="3000"></textarea></label></p><p><label><input name="confirm" type="checkbox" value="1" required> I checked the bank statement or completed refund and confirm this financial change.</label></p>'; submit_button('Record verification'); echo '</form>'; }
        if ($d->status === 'confirmed') { echo '<h3>Donation acknowledgment</h3><p>Not a tax receipt. Mail status: ' . esc_html($d->email_state) . '.</p><form method="post" action="' . esc_url(admin_url('admin-post.php')) . '"><input type="hidden" name="action" value="awsw_resend"><input type="hidden" name="id" value="' . $id . '">'; wp_nonce_field('awsw_resend_' . $id); submit_button('Send / retry acknowledgment', 'secondary'); echo '</form>'; }
        echo '<h3>Verification audit history</h3><ul>';
        foreach ($wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}awsw_audit WHERE donation_id=%d ORDER BY id", $id)) as $a) { echo '<li>' . esc_html(awsw_time($a->created_at) . ' · staff user #' . $a->actor_id . ' · ' . $a->old_status . ' → ' . $a->new_status . ' · ' . $a->note) . '</li>'; }
        echo '</ul></div>'; return;
    }
    $status = sanitize_key($_GET['status'] ?? ''); $campaign = absint($_GET['campaign'] ?? 0); $args = []; $where = ' WHERE 1=1';
    if (in_array($status, ['pending','confirmed','rejected','refunded'], true)) { $where .= ' AND status=%s'; $args[] = $status; }
    if ($campaign) { $where .= ' AND campaign_id=%d'; $args[] = $campaign; }
    // Day boundaries explicitly use Karachi time, while records are stored in UTC.
    foreach (['from' => '>=', 'to' => '<'] as $key => $op) { $v = sanitize_text_field($_GET[$key] ?? ''); if (preg_match('/^\d{4}-\d{2}-\d{2}$/D', $v)) { $date = DateTimeImmutable::createFromFormat('!Y-m-d', $v, new DateTimeZone('Asia/Karachi')); if (!$date || $date->format('Y-m-d') !== $v) { continue; } if ($key === 'to') { $date = $date->modify('+1 day'); } $where .= " AND created_at $op %s"; $args[] = $date->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s'); } }
    echo '<form><input type="hidden" name="page" value="awsw-donations"><label>Status <select name="status"><option value="">All</option>';
    foreach (['pending','confirmed','rejected','refunded'] as $s) { echo '<option ' . selected($status, $s, false) . '>' . esc_html($s) . '</option>'; }
    echo '</select></label> <label>Campaign ID <input type="number" name="campaign" min="0" value="' . $campaign . '"></label> <label>From (Karachi) <input type="date" name="from" value="' . esc_attr(sanitize_text_field($_GET['from'] ?? '')) . '"></label> <label>To <input type="date" name="to" value="' . esc_attr(sanitize_text_field($_GET['to'] ?? '')) . '"></label> <button class="button">Filter</button></form>';
    $page = max(1, absint($_GET['paged'] ?? 1)); $sql = "SELECT * FROM {$wpdb->prefix}awsw_donations" . $where . ' ORDER BY id DESC LIMIT 50 OFFSET ' . (($page - 1) * 50); $rows = $wpdb->get_results($args ? $wpdb->prepare($sql, $args) : $sql);
    echo '<table class="widefat striped"><thead><tr><th>Reference</th><th>Amount</th><th>Status</th><th>Submitted (Karachi)</th></tr></thead><tbody>';
    foreach ($rows as $r) { echo '<tr><td><a href="' . esc_url(admin_url('admin.php?page=awsw-donations&id=' . $r->id)) . '">' . esc_html($r->reference) . '</a></td><td>' . esc_html(awsw_money($r->amount_minor)) . '</td><td>' . esc_html($r->status) . '</td><td>' . esc_html(awsw_time($r->created_at)) . '</td></tr>'; }
    if (!$rows) { echo '<tr><td colspan="4">No matching donation records.</td></tr>'; } echo '</tbody></table>';
    if ($page > 1) { echo '<a href="' . esc_url(add_query_arg('paged', $page - 1)) . '">Previous</a> '; } if (count($rows) === 50) { echo '<a href="' . esc_url(add_query_arg('paged', $page + 1)) . '">Next</a>'; }
    if (current_user_can('awsw_export_donations')) { echo '<p><a class="button" href="' . esc_url(wp_nonce_url(admin_url('admin-post.php?action=awsw_export'), 'awsw_export')) . '">Export all donation records (CSV, private)</a></p>'; }
    echo '</div>';
}
add_action('admin_post_awsw_transition', function () { awsw_admin_guard(); $id = absint(awsw_request('id')); check_admin_referer('awsw_transition_' . $id); if (awsw_request('confirm') !== '1') { wp_die('Explicit confirmation is required.'); } $r = awsw_transition($id, sanitize_key(awsw_request('status')), awsw_request('note')); if (is_wp_error($r)) { wp_die(esc_html($r->get_error_message())); } wp_safe_redirect(admin_url('admin.php?page=awsw-donations&id=' . $id . '&awsw_saved=1')); exit; });
add_action('admin_post_awsw_resend', function () { awsw_admin_guard(); $id = absint(awsw_request('id')); check_admin_referer('awsw_resend_' . $id); awsw_acknowledge($id); wp_safe_redirect(admin_url('admin.php?page=awsw-donations&id=' . $id . '&awsw_saved=1')); exit; });
function awsw_csv_cell($v) { $s = (string) $v; return preg_match('/^[\s]*[=+\-@\t\r\n]/u', $s) ? "'" . $s : $s; }
add_action('admin_post_awsw_export', function () {
    awsw_admin_guard('awsw_export_donations'); check_admin_referer('awsw_export'); nocache_headers(); header('Content-Type: text/csv; charset=utf-8'); header('Content-Disposition: attachment; filename="aws-welfare-private-donations.csv"');
    $f = fopen('php://output', 'w'); fputcsv($f, ['reference','campaign_id','amount_paisa','status','name','email','phone','anonymous','marketing','transfer_reference','created_at_UTC'], ',', '"', '');
    global $wpdb; $last = 0; do { $rows = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}awsw_donations WHERE id>%d ORDER BY id LIMIT 500", $last)); foreach ($rows as $d) { fputcsv($f, array_map('awsw_csv_cell', [$d->reference,$d->campaign_id,$d->amount_minor,$d->status,$d->donor_name,$d->donor_email,$d->donor_phone,$d->anonymous,$d->marketing,$d->transfer_ref,$d->created_at]), ',', '"', ''); $last = $d->id; } } while (count($rows) === 500); fclose($f); exit;
});
function awsw_admin_enquiries() { awsw_cases_admin(); }
add_action('admin_post_awsw_delete_enquiry', function () { $id = absint(awsw_request('id')); global $wpdb; $kind = $wpdb->get_var($wpdb->prepare("SELECT kind FROM {$wpdb->prefix}awsw_enquiries WHERE id=%d",$id)); awsw_admin_guard(awsw_case_cap($kind)); check_admin_referer('awsw_delete_' . $id); if (awsw_request('confirm') !== '1') { wp_die('Confirmation required.'); } global $wpdb; $wpdb->delete($wpdb->prefix . 'awsw_case_events', ['case_id'=>$id]);$wpdb->delete($wpdb->prefix . 'awsw_enquiries', ['id' => $id]); wp_safe_redirect(admin_url('admin.php?page='.($kind==='assistance'?'awsw-assistance':'awsw-enquiries').'&kind='.sanitize_key($kind).'&awsw_saved=1')); exit; });
add_action('add_meta_boxes', function () {
    foreach (['page','post','awsw_campaign','awsw_update','awsw_report','awsw_metric','awsw_member','awsw_photo'] as $type) { add_meta_box('awsw-content-fields', 'AWS Welfare · Translations & verified fields', 'awsw_meta_box', $type, 'normal', 'high'); }
});
function awsw_meta_box($post) {
    wp_nonce_field('awsw_meta_' . $post->ID, 'awsw_meta_nonce');
    $fields = ['ur_title' => 'Urdu title','ur_excerpt' => 'Urdu excerpt','role' => 'Approved role / responsibility','ur_role' => 'Approved role (Urdu)','credit' => 'Approved image credit','approved' => 'Gallery consent & approval: enter 1 only after review', 'ur_content' => 'Urdu content (safe HTML or Gutenberg blocks)', 'description' => 'SEO description','og_image' => 'Approved Open Graph image URL (optional)'];
    if ($post->post_type === 'awsw_campaign') { $fields += ['target' => 'Fundraising target in PKR (blank = no target)','status' => 'Campaign status: draft, active, paused, completed','location' => 'General location (no private beneficiary addresses)','ur_location' => 'General location (Urdu)','use_funds' => 'Reviewed use of funds (English)','ur_use_funds' => 'Reviewed use of funds (Urdu)','start' => 'Start date YYYY-MM-DD','end' => 'End date YYYY-MM-DD','milestones' => 'Delivery milestones, one per line: YYYY-MM-DD | completed action','ur_milestones' => 'Urdu delivery milestones, one per line: YYYY-MM-DD | completed action']; }
    if (in_array($post->post_type, ['awsw_update','awsw_report'], true)) { $fields += ['location'=>'General location','ur_location'=>'General location (Urdu)','milestone_status'=>'Verified delivery milestone','ur_milestone_status'=>'Delivery milestone (Urdu)','related_report'=>'Related published report ID','campaign' => 'Related campaign ID','period' => 'Reporting period','ur_period' => 'Reporting period (Urdu)']; }
    if ($post->post_type === 'awsw_report') { $fields += ['document' => 'Approved public PDF URL (Media Library, no private donor documents)']; }
    if ($post->post_type === 'awsw_metric') { $fields += ['metric_value' => 'Verified metric value','period' => 'Reporting period','ur_period' => 'Reporting period (Urdu)','evidence' => 'Internal evidence reference (not public)']; }
    foreach ($fields as $key => $label) {
        $value = get_post_meta($post->ID, '_awsw_' . $key, true);
        if ($key === 'target' && $value !== '') { $value = intdiv((int) $value, 100) . '.' . str_pad((string) ((int) $value % 100), 2, '0', STR_PAD_LEFT); }
        echo '<p><label for="awsw-' . esc_attr($key) . '"><strong>' . esc_html($label) . '</strong></label><br>';
        if (str_contains($key, 'content') || str_contains($key, 'milestones')) { echo '<textarea style="width:100%" rows="5" id="awsw-' . esc_attr($key) . '" name="awsw[' . esc_attr($key) . ']" ' . (str_starts_with($key, 'ur_') ? 'dir="rtl" lang="ur"' : '') . '>' . esc_textarea($value) . '</textarea>'; }
        else { echo '<input style="width:100%" id="awsw-' . esc_attr($key) . '" name="awsw[' . esc_attr($key) . ']" value="' . esc_attr($value) . '">'; }
        echo '</p>';
    }
    echo '<p>Use the Program taxonomy to connect related content. Featured images must be approved organizational photographs. For galleries, use the native Gutenberg Gallery block. Do not publish private financial evidence in public blocks. Urdu translations stay with the same record and URL.</p>';
}
add_action('save_post', function ($id) {
    if (wp_is_post_revision($id) || (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) || !current_user_can('edit_post', $id) || !isset($_POST['awsw_meta_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['awsw_meta_nonce'])), 'awsw_meta_' . $id)) { return; }
    $raw = isset($_POST['awsw']) && is_array($_POST['awsw']) ? wp_unslash($_POST['awsw']) : [];
    $keys = ['ur_title','ur_content','ur_excerpt','role','ur_role','credit','approved','description','og_image','milestone_status','ur_milestone_status','related_report','target','status','location','ur_location','use_funds','ur_use_funds','start','end','milestones','ur_milestones','campaign','period','ur_period','document','metric_value','evidence'];
    foreach ($keys as $key) {
        if (!array_key_exists($key, $raw) || !is_scalar($raw[$key])) { continue; }
        $v = (string) $raw[$key];
        if ($key === 'target') { $v = $v === '' ? '' : awsw_parse_money($v); if ($v === false) { continue; } }
        elseif ($key === 'status') { if (!in_array($v, ['draft','active','paused','completed'], true)) { $v = 'draft'; } }
        elseif ($key === 'ur_content') { $v = wp_kses_post($v); }
        elseif ($key === 'og_image') { $v = esc_url_raw($v, ['https','http']); }
        elseif ($key === 'document') { $v = esc_url_raw($v, ['https','http']); if ($v && strtolower(pathinfo((string) wp_parse_url($v, PHP_URL_PATH), PATHINFO_EXTENSION)) !== 'pdf') { continue; } }
        elseif ($key === 'campaign') { $v = absint($v); }
        elseif (in_array($key, ['start','end'], true)) { if ($v && (!preg_match('/^\d{4}-\d{2}-\d{2}$/D', $v) || !strtotime($v))) { continue; } }
        else { $v = sanitize_textarea_field($v); }
        update_post_meta($id, '_awsw_' . $key, $v);
    }
    if (get_post_type($id) === 'awsw_metric') { update_post_meta($id, '_awsw_verified_by', get_current_user_id()); }
});
// Privacy tools require a WordPress-confirmed data subject request and authorized admin.
add_filter('wp_privacy_personal_data_exporters', function ($exporters) {
    $exporters['aws-welfare'] = ['exporter_friendly_name' => 'AWS Welfare records', 'callback' => function ($email, $page = 1) {
        global $wpdb; $items = []; $rows = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}awsw_donations WHERE donor_email=%s ORDER BY id LIMIT 100 OFFSET %d", $email, ($page - 1) * 100));
        foreach ($rows as $r) { $items[] = ['group_id' => 'awsw-donations','group_label' => 'AWS Welfare donations','item_id' => 'donation-' . $r->id,'data' => [['name' => 'Reference','value' => $r->reference],['name' => 'Name','value' => $r->donor_name],['name' => 'Email','value' => $r->donor_email],['name' => 'Phone','value' => $r->donor_phone],['name' => 'Amount','value' => awsw_money($r->amount_minor)],['name' => 'Status','value' => $r->status],['name' => 'Transfer reference','value' => $r->transfer_ref],['name' => 'Marketing consent','value' => $r->marketing ? 'Yes' : 'No']]]; }
        $enquiries = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}awsw_enquiries"); if ($page === 1) { foreach ($enquiries as $e) { $p = json_decode($e->payload, true); if (strtolower(($p['email'] ?? '') ?: ($p['contact_method'] ?? '')) === strtolower($email)) { $data = []; foreach ($p as $k => $v) { $data[] = ['name' => $k,'value' => $v]; } $items[] = ['group_id'=>'awsw-enquiries','group_label'=>'AWS Welfare enquiries','item_id'=>'enquiry-' . $e->id,'data'=>$data]; } } }
        return ['data' => $items,'done' => count($rows) < 100];
    }]; return $exporters;
});
add_filter('wp_privacy_personal_data_erasers', function ($erasers) {
    $erasers['aws-welfare'] = ['eraser_friendly_name' => 'AWS Welfare records', 'callback' => function ($email, $page = 1) {
        global $wpdb; $removed = false; $rows = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}awsw_donations WHERE donor_email=%s LIMIT 100", $email));
        foreach ($rows as $r) {
            if ($r->proof_file && awsw_private_dir() && basename($r->proof_file) === $r->proof_file) { @unlink(awsw_private_dir() . '/' . $r->proof_file); }
            $wpdb->update($wpdb->prefix . 'awsw_donations', ['donor_name'=>'[erased]','donor_email'=>'erased@example.invalid','donor_phone'=>'','transfer_ref'=>'[erased]','anonymous'=>1,'marketing'=>0,'proof_file'=>'','proof_mime'=>'','access_hash'=>null,'access_until'=>null], ['id'=>$r->id]);
            $wpdb->insert($wpdb->prefix . 'awsw_audit', ['donation_id'=>$r->id,'actor_id'=>get_current_user_id(),'old_status'=>$r->status,'new_status'=>$r->status,'note'=>'Personal data erased under a verified privacy request; financial history retained.','created_at'=>current_time('mysql',true)]); $removed = true;
        }
        foreach ($wpdb->get_results("SELECT * FROM {$wpdb->prefix}awsw_enquiries") as $e) { $p = json_decode($e->payload,true); if (strtolower(($p['email'] ?? '') ?: ($p['contact_method'] ?? '')) === strtolower($email)) { $wpdb->delete($wpdb->prefix . 'awsw_case_events',['case_id'=>$e->id]);$wpdb->delete($wpdb->prefix . 'awsw_enquiries',['id'=>$e->id]); $removed = true; } }
        return ['items_removed'=>$removed,'items_retained'=>false,'messages'=>['Financial amounts, pseudonymous transfer fingerprints and verification audit history are retained. Review audit notes for personal data before closing the request.'],'done'=>count($rows)<100];
    }]; return $erasers;
});
