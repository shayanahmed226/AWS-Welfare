<?php
if(wp_get_environment_type()!=='local'){throw new RuntimeException('Local QA only.');}
function ok_awsw($condition,$label){if(!$condition){throw new RuntimeException('FAIL: '.$label);}echo 'PASS: '.$label."\n";}
wp_set_current_user(1);global $wpdb;
$campaign=(int)get_option('awsw_qa_campaign');ok_awsw(wp_delete_post($campaign,true)===false,'financially linked campaign cannot be permanently deleted');
$id=(int)$wpdb->get_var("SELECT id FROM {$wpdb->prefix}awsw_donations WHERE status='pending' ORDER BY id DESC LIMIT 1");
ok_awsw(is_wp_error(awsw_transition($id,'confirmed','<script></script>')),'empty sanitized audit reason rejected');
ok_awsw(is_wp_error(awsw_transition($id,'confirmed',str_repeat('x',3001))),'oversized audit reason rejected');
$wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE 'awsw_rate_%'");wp_cache_flush();ok_awsw(awsw_rate_limit('extended')===true&&awsw_rate_limit('extended')===false,'rate limit rejects a second immediate request');
ok_awsw(awsw_valid_iban('PK36SCBL0000001123456702')===true,'Pakistan IBAN checksum accepted for standard validation example');
ok_awsw(awsw_valid_iban('PK00SCBL0000001123456702')===false,'invalid IBAN checksum rejected');
$donations=(int)$wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}awsw_donations");$enquiries=(int)$wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}awsw_enquiries");
ok_awsw($enquiries>=2,'both enquiry kinds persisted');
$exporters=apply_filters('wp_privacy_personal_data_exporters',[]);$export=$exporters['aws-welfare']['callback']('enquiry@example.invalid',1);ok_awsw(count($export['data'])>=2,'WordPress privacy exporter includes enquiries');
$erasers=apply_filters('wp_privacy_personal_data_erasers',[]);$erased=$erasers['aws-welfare']['callback']('enquiry@example.invalid',1);ok_awsw($erased['items_removed']===true,'verified administrator privacy eraser deletes enquiries');
ok_awsw((int)$wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}awsw_donations")===$donations,'privacy enquiry erasure preserves financial history');
$donor=$wpdb->get_row("SELECT * FROM {$wpdb->prefix}awsw_donations WHERE donor_email='proof@example.invalid' ORDER BY id DESC LIMIT 1");$path=awsw_private_dir().'/'.$donor->proof_file;$erased=$erasers['aws-welfare']['callback']('proof@example.invalid',1);$now=awsw_donation($donor->id);ok_awsw($erased['items_removed']&&$now->donor_email==='erased@example.invalid'&&!is_file($path)&&$now->amount_minor===$donor->amount_minor,'privacy erasure removes private proof and identity, retains amount');
$metric=wp_insert_post(['post_type'=>'awsw_metric','post_title'=>'LOCAL QA metric missing evidence','post_status'=>'publish']);update_post_meta($metric,'_awsw_metric_value','999');ok_awsw(!str_contains(do_shortcode('[awsw_metrics]'),'999'),'metric without reporting period/evidence/editor not displayed');wp_delete_post($metric,true);
echo "EXTENDED QA: 12 assertions passed.\n";
