<?php
if (!defined('ABSPATH')) { exit; }
function awsw_wants_json() { return isset($_SERVER['HTTP_X_AWSW_FORM']) && $_SERVER['HTTP_X_AWSW_FORM'] === '1'; }
function awsw_redirect($url) { if (awsw_wants_json()) { wp_send_json_success(['redirect'=>$url]); } wp_safe_redirect($url); exit; }
function awsw_issue_access($id) {
    global $wpdb; $raw=bin2hex(random_bytes(32));
    $wpdb->update($wpdb->prefix.'awsw_donations',['access_hash'=>hash('sha256',$raw),'access_until'=>gmdate('Y-m-d H:i:s',time()+30*DAY_IN_SECONDS)],['id'=>$id]);return $raw;
}
function awsw_access_record($raw) {
    if(!is_string($raw)||!preg_match('/^[a-f0-9]{64}$/D',$raw)){return null;}
    global $wpdb;return $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}awsw_donations WHERE access_hash=%s AND access_until>%s",hash('sha256',$raw),current_time('mysql',true)));
}
function awsw_donation_redirect($reference) {
    global $wpdb;$id=$wpdb->get_var($wpdb->prepare("SELECT id FROM {$wpdb->prefix}awsw_donations WHERE reference=%s",$reference));
    if(!$id){awsw_fail('Submission unavailable. Contact the society.','درخواست دستیاب نہیں۔ ادارے سے رابطہ کریں۔',503);}
    $raw=awsw_issue_access((int)$id);$d=awsw_donation($id);
    // A failed email never erases a pending record; the donor also receives verified submission access.
    wp_mail($d->donor_email,$d->language==='ur'?'AWS Welfare: درخواست تصدیق کی منتظر ہے':'AWS Welfare: transfer pending verification',($d->language==='ur'?'یہ ادائیگی کی تصدیق نہیں۔ محفوظ حالت کا لنک:':'This is not payment confirmation. Private status link:')."\n".add_query_arg(['access'=>$raw,'lang'=>$d->language],home_url('/donation-status/')));
    awsw_redirect(add_query_arg(['access'=>$raw,'ref'=>$reference],awsw_url('donation-confirmation/')));
}
function awsw_private_summary($d) {
    $campaign=$d->campaign_id?awsw_post_title($d->campaign_id):awsw_t('General fund','عمومی فنڈ');
    $html='<div class="awsw-status-summary"><span class="awsw-status">'.esc_html(awsw_status_label($d->status)).'</span><h2>'.esc_html(awsw_money($d->amount_minor)).'</h2><dl><dt>'.esc_html(awsw_t('Reference','حوالہ')).'</dt><dd><bdi>'.esc_html($d->reference).'</bdi></dd><dt>'.esc_html(awsw_t('Purpose','مقصد')).'</dt><dd>'.esc_html($campaign).'</dd><dt>'.esc_html(awsw_t('Submitted (Asia/Karachi)','جمع کرانے کی تاریخ (پاکستان)')).'</dt><dd><bdi>'.esc_html(awsw_time($d->created_at)).'</bdi></dd></dl></div>';return $html;
}
add_action('init',function(){
    remove_shortcode('awsw_confirmation');add_shortcode('awsw_confirmation',function(){
        $d=awsw_access_record(sanitize_text_field($_GET['access']??''));
        if(!$d){return awsw_empty('This private link is missing, expired or replaced. Request a new link from Donation Status.','محفوظ لنک موجود نہیں، پرانا یا تبدیل ہو چکا ہے۔ عطیے کی حالت کے صفحے سے نیا لنک حاصل کریں۔').awsw_button(awsw_t('Donation status','عطیے کی حالت'),awsw_url('donation-status/'));}
        return '<div class="awsw-confirmation"><span aria-hidden="true">♡</span><h2>'.esc_html(awsw_t('Your submission is safely recorded.','آپ کی درخواست محفوظ ہو گئی ہے۔')).'</h2>'.awsw_private_summary($d).'<p>'.esc_html(awsw_t('Submission is not proof of payment. Staff compare the transfer with the bank statement. Keep this private link; a new emailed link replaces older access and expires in 30 days.','درخواست ادائیگی کا ثبوت نہیں۔ عملہ بینک اسٹیٹمنٹ سے ٹرانسفر ملائے گا۔ یہ لنک نجی رکھیں؛ نیا ای میل لنک پچھلے لنک کی جگہ لیتا ہے اور ۳۰ دن میں ختم ہو جاتا ہے۔')).'</p>'.awsw_button(awsw_t('View private status','محفوظ حالت دیکھیں'),add_query_arg('access',sanitize_text_field($_GET['access']),awsw_url('donation-status/'))).'</div>';
    });
});
add_shortcode('awsw_donation_status',function(){
    $raw=sanitize_text_field($_GET['access']??'');$d=awsw_access_record($raw);
    if($d){$html=awsw_private_summary($d);if($d->status==='confirmed'){$html.='<p>'.esc_html(awsw_t('This is a confirmed donation acknowledgment, not a tax receipt.','یہ تصدیق شدہ عطیے کا اقرار ہے، ٹیکس رسید نہیں۔')).'</p><a class="awsw-button" href="'.esc_url(add_query_arg(['action'=>'awsw_ack_download','access'=>$raw],admin_url('admin-post.php'))).'">'.esc_html(awsw_t('Download acknowledgment','تصدیق نامہ ڈاؤن لوڈ کریں')).'</a>';}return $html;}
    ob_start();if($raw){echo '<p class="awsw-notice">'.esc_html(awsw_t('This link is invalid or expired. Request a fresh link below.','یہ لنک درست نہیں یا ختم ہو چکا ہے۔ نیچے نیا لنک حاصل کریں۔')).'</p>';}if(isset($_GET['sent'])){echo '<p class="awsw-notice" role="status">'.esc_html(awsw_t('If the details match a record, a private link has been requested for that email. Email delivery must be available. Contact the society if it does not arrive.','اگر معلومات ریکارڈ سے ملیں تو ای میل پر نجی لنک کی درخواست کی گئی ہے۔ ای میل ترسیل ضروری ہے؛ لنک نہ ملے تو ادارے سے رابطہ کریں۔')).'</p>';}
    echo '<p>'.esc_html(awsw_t('Use your submission reference and the email used for your donation. No donor information is shown through reference alone.','اپنی درخواست کا حوالہ اور عطیے والی ای میل استعمال کریں۔ صرف حوالے سے ذاتی معلومات ظاہر نہیں ہوتیں۔')).'</p><form class="awsw-form" method="post" action="'.esc_url(add_query_arg('lang',awsw_lang(),admin_url('admin-post.php'))).'">';wp_nonce_field('awsw_status','_awsw_nonce');echo '<input type="hidden" name="action" value="awsw_status_link">';awsw_input('reference',awsw_t('Submission reference','درخواست کا حوالہ'));awsw_input('email',awsw_t('Donation email','عطیے کی ای میل'),'email');echo '<button class="awsw-button" type="submit">'.esc_html(awsw_t('Email my secure link','محفوظ لنک ای میل کریں')).'</button></form>';return ob_get_clean();
});
function awsw_status_request(){
    awsw_form_guard('status');if(!awsw_rate_limit('status_link')){awsw_fail('Please wait before another request.','دوبارہ درخواست سے پہلے انتظار کریں۔',429);}
    $email=sanitize_email(awsw_request('email'));$ref=sanitize_text_field(awsw_request('reference'));global $wpdb;
    $d=$wpdb->get_row($wpdb->prepare("SELECT id,donor_email,language FROM {$wpdb->prefix}awsw_donations WHERE reference=%s AND donor_email=%s",$ref,$email));
    if($d&&is_email($email)){$raw=awsw_issue_access($d->id);wp_mail($email,$d->language==='ur'?'AWS Welfare: محفوظ حالت کا لنک':'AWS Welfare: private donation status',add_query_arg(['access'=>$raw,'lang'=>$d->language],home_url('/donation-status/'))."\n".($d->language==='ur'?'یہ لنک نجی رکھیں۔ ۳۰ دن تک کارآمد ہے۔':'Keep this link private. Valid for 30 days.'));}
    awsw_redirect(add_query_arg('sent','1',awsw_url('donation-status/')));
}
foreach(['admin_post_','admin_post_nopriv_'] as $prefix){add_action($prefix.'awsw_status_link','awsw_status_request');add_action($prefix.'awsw_ack_download',function(){
    $d=awsw_access_record(sanitize_text_field($_GET['access']??''));if(!$d||$d->status!=='confirmed'){wp_die('Acknowledgment unavailable.','',['response'=>403]);}
    nocache_headers();header('Referrer-Policy: no-referrer');header('X-Robots-Tag: noindex,nofollow');header('Content-Type: text/plain; charset=utf-8');header('Content-Disposition: attachment; filename="AWS-donation-acknowledgment.txt"');
    echo ($d->language==='ur'?"AWS Welfare — عطیہ تصدیق نامہ\n":"AWS Welfare — Donation acknowledgment\n").$d->reference."\n".awsw_money($d->amount_minor)."\n".($d->campaign_id?get_the_title($d->campaign_id):'General fund')."\n".awsw_time($d->created_at)."\n\n".($d->language==='ur'?'یہ ٹیکس رسید نہیں۔':'This is not a tax receipt.');exit;
});}
