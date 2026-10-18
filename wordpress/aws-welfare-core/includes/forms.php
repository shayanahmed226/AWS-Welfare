<?php
if (!defined('ABSPATH')) { exit; }
function awsw_request($key) { return isset($_POST[$key]) && is_scalar($_POST[$key]) ? trim(wp_unslash((string) $_POST[$key])) : ''; }
function awsw_fail($en, $ur, $code = 400) { if (awsw_wants_json()) { wp_send_json_error(['message'=>awsw_t($en,$ur)],$code); } wp_die(esc_html(awsw_t($en, $ur)), esc_html(awsw_t('Please check your submission', 'درخواست چیک کریں')), ['response' => $code, 'back_link' => true]); }
function awsw_form_guard($kind) {
    if (!wp_verify_nonce(awsw_request('_awsw_nonce'), 'awsw_' . $kind)) { if(awsw_wants_json()){wp_send_json_error(['message'=>awsw_t('The form expired. Security has been refreshed; retry without clearing your details.','فارم کی حفاظتی میعاد ختم ہو گئی تھی۔ معلومات موجود ہیں؛ دوبارہ بھیجیں۔'),'nonce'=>wp_create_nonce('awsw_'.$kind)],403);} awsw_fail('The form expired. Reload the page and try again.', 'فارم کی میعاد ختم ہو گئی ہے۔ صفحہ دوبارہ کھولیں۔', 403); }
    if (awsw_request('website_url') !== '') { awsw_fail('Unable to accept this submission.', 'درخواست قبول نہیں کی جا سکی۔'); }
}
function awsw_rate_limit($kind) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown'; // Never trust user-supplied forwarding headers.
    $key = 'awsw_rate_' . hash_hmac('sha256', $kind . '|' . $ip, wp_salt());
    $now = time(); $old = get_option($key);
    if (is_array($old) && $old['until'] > $now) { return false; }
    // add_option is atomic; a concurrent request cannot acquire this slot twice.
    if ($old) { global $wpdb; $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->options} WHERE option_name=%s AND option_value=%s", $key, maybe_serialize($old))); wp_cache_delete($key, 'options'); }
    return add_option($key, ['until' => $now + 20], '', false);
}
add_action('awsw_cleanup_rates', function () { global $wpdb; $keys = $wpdb->get_results($wpdb->prepare("SELECT option_name, option_value FROM {$wpdb->options} WHERE option_name LIKE %s LIMIT 1000", $wpdb->esc_like('awsw_rate_') . '%')); foreach ($keys as $r) { $v = maybe_unserialize($r->option_value); if (is_array($v) && ($v['until'] ?? 0) < time()) { delete_option($r->option_name); } } });
add_action('init', function () { if (!wp_next_scheduled('awsw_cleanup_rates')) { wp_schedule_event(time() + 3600, 'daily', 'awsw_cleanup_rates'); } });
function awsw_bank_ready() { foreach (['bank_title','bank_name'] as $k) { if (!awsw_setting($k)) { return false; } } return (bool) (awsw_setting('bank_account') || awsw_setting('bank_iban')); }
function awsw_upload_proof() {
    if (empty($_FILES['proof']['name'])) { return ['', '']; }
    $dir = awsw_private_dir(); if (!$dir) { return new WP_Error('storage', 'Private proof storage is not configured.'); }
    $file = $_FILES['proof'];
    if (!is_scalar($file['name']) || (int) $file['error'] !== UPLOAD_ERR_OK || (int) $file['size'] > 5 * 1024 * 1024 || (int) $file['size'] < 1 || !is_uploaded_file($file['tmp_name'])) { return new WP_Error('upload', 'Invalid upload or file larger than 5 MB.'); }
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION)); $types = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'pdf' => 'application/pdf'];
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    if (!isset($types[$ext]) || $types[$ext] !== $mime) { return new WP_Error('type', 'Only genuine JPEG, PNG and PDF files are accepted.'); }
    if ($mime !== 'application/pdf' && !@getimagesize($file['tmp_name'])) { return new WP_Error('image', 'The image is invalid.'); }
    $name = bin2hex(random_bytes(24)) . '.' . $ext;
    if (!move_uploaded_file($file['tmp_name'], $dir . '/' . $name)) { return new WP_Error('write', 'Could not save the private proof.'); }
    chmod($dir . '/' . $name, 0600); return [$name, $mime];
}
function awsw_donate_submit() {
    awsw_form_guard('donate');
    if (!awsw_bank_ready()) { awsw_fail('Bank transfer details are awaiting verification. No donation was recorded.', 'بینک کی تصدیق شدہ معلومات دستیاب نہیں۔ عطیہ درج نہیں ہوا۔', 503); }
    $token = awsw_request('submission');
    if (!preg_match('/^[a-f0-9]{48}$/D', $token) || !hash_equals(hash_hmac('sha256', $token, wp_salt('nonce')), awsw_request('signature'))) { awsw_fail('Invalid submission. Reload the form.', 'فارم دوبارہ کھولیں۔ درخواست درست نہیں۔'); }
    global $wpdb; $table = $wpdb->prefix . 'awsw_donations'; $hash = hash('sha256', $token);
    $existing = $wpdb->get_row($wpdb->prepare("SELECT reference FROM $table WHERE submission_hash=%s", $hash));
    if ($existing) { awsw_donation_redirect($existing->reference); }
    $minor = awsw_parse_money(awsw_request('amount')); $campaign = absint(awsw_request('campaign'));
    if ($minor === false || $minor < 100 || $minor > 99999999999) { awsw_fail('Enter a valid amount from PKR 1 to 999,999,999.99.', 'درست رقم درج کریں: ۱ سے ۹۹۹،۹۹۹،۹۹۹٫۹۹ روپے تک۔'); }
    if ($campaign && (get_post_type($campaign) !== 'awsw_campaign' || get_post_status($campaign) !== 'publish' || get_post_meta($campaign, '_awsw_status', true) !== 'active')) { awsw_fail('This campaign is not accepting donations.', 'یہ مہم فی الحال عطیات قبول نہیں کر رہی۔'); }
    $name = sanitize_text_field(awsw_request('donor_name')); $email = sanitize_email(awsw_request('donor_email')); $phone = sanitize_text_field(awsw_request('donor_phone')); $transfer = sanitize_text_field(awsw_request('transfer_ref'));
    if (!$name || mb_strlen($name) > 190 || !is_email($email) || strlen($email) > 190 || mb_strlen($phone) > 40 || mb_strlen($transfer) < 4 || mb_strlen($transfer) > 190 || awsw_request('consent') !== '1') { awsw_fail('Check your name, email, transfer reference and privacy consent.', 'نام، ای میل، ٹرانسفر حوالہ اور رازداری کی رضامندی چیک کریں۔'); }
    // A bank reference can be recorded only once, even with a new form token.
    $transfer_hash = hash('sha256', strtoupper(preg_replace('/\s+/u', '', $transfer)));
    if ($wpdb->get_var($wpdb->prepare("SELECT id FROM $table WHERE transfer_hash=%s", $transfer_hash))) { awsw_fail('This transfer reference is already recorded. Contact the society if you need a correction.', 'یہ ٹرانسفر حوالہ پہلے سے درج ہے۔ تصحیح کے لیے ادارے سے رابطہ کریں۔', 409); }
    if (!awsw_rate_limit('donation')) { awsw_fail('Please wait 20 seconds before another submission.', 'اگلی درخواست سے پہلے ۲۰ سیکنڈ انتظار کریں۔', 429); }
    $proof = awsw_upload_proof(); if (is_wp_error($proof)) { awsw_fail($proof->get_error_message(), 'فائل محفوظ نہیں ہو سکی۔ صرف اصل JPG، PNG یا PDF فائل، زیادہ سے زیادہ ۵ میگابائٹ، منتخب کریں۔'); }
    $ref = 'AWS-' . strtoupper(bin2hex(random_bytes(12))); $now = current_time('mysql', true);
    $ok = $wpdb->insert($table, ['reference' => $ref, 'submission_hash' => $hash, 'transfer_hash' => $transfer_hash, 'campaign_id' => $campaign, 'amount_minor' => $minor,'status' => 'pending','donor_name' => $name,'donor_email' => $email,'donor_phone' => $phone,'anonymous' => awsw_request('anonymous') === '1' ? 1 : 0,'marketing' => awsw_request('marketing') === '1' ? 1 : 0,'transfer_ref' => $transfer,'proof_file' => $proof[0],'proof_mime' => $proof[1],'language' => awsw_lang(),'created_at' => $now,'updated_at' => $now]);
    if (!$ok) {
        if ($proof[0]) { @unlink(awsw_private_dir() . '/' . $proof[0]); }
        $same = $wpdb->get_var($wpdb->prepare("SELECT reference FROM $table WHERE submission_hash=%s", $hash));
        if ($same) { awsw_donation_redirect($same); }
        awsw_fail('The donation could not be saved. The reference may already be recorded; contact the society before resubmitting.', 'عطیہ محفوظ نہیں ہوا۔ دوبارہ بھیجنے سے پہلے ادارے سے رابطہ کریں۔', 409);
    }
    awsw_donation_redirect($ref);
}
function awsw_enquiry_submit() { awsw_case_submit(); }
foreach (['awsw_donate' => 'awsw_donate_submit','awsw_enquiry' => 'awsw_enquiry_submit'] as $action => $handler) { add_action('admin_post_' . $action, $handler); add_action('admin_post_nopriv_' . $action, $handler); }
add_action('admin_post_awsw_proof', function () {
    if (!current_user_can('awsw_manage_donations')) { wp_die('Permission denied.', '', ['response' => 403]); }
    $id = absint($_GET['id'] ?? 0); check_admin_referer('awsw_proof_' . $id); $d = awsw_donation($id); $dir = awsw_private_dir();
    if (!$d || !$dir || !$d->proof_file || !preg_match('/^[a-f0-9]{48}\.(jpg|jpeg|png|pdf)$/D', $d->proof_file) || !is_file($dir . '/' . $d->proof_file)) { wp_die('Proof unavailable.', '', ['response' => 404]); }
    nocache_headers(); header('X-Robots-Tag: noindex, nofollow'); header('Content-Security-Policy: sandbox'); header('Content-Type: ' . $d->proof_mime); header('Content-Disposition: attachment; filename="proof-' . (int) $id . '.' . pathinfo($d->proof_file, PATHINFO_EXTENSION) . '"'); readfile($dir . '/' . $d->proof_file); exit;
});
