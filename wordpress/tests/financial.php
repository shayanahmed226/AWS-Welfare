<?php
// Execute only on a disposable local WordPress install: wp eval-file tests/financial.php.
if (wp_get_environment_type() !== 'local') { throw new RuntimeException('Local QA environment required.'); }
$GLOBALS['awsw_passed']=0;
function expect_awsw($condition,$label){if(!$condition){throw new RuntimeException('FAIL: '.$label);}echo 'PASS: '.$label."\n";$GLOBALS['awsw_passed']++;}
wp_set_current_user(1);global $wpdb;
expect_awsw(!awsw_bank_ready(),'missing bank configuration disables donations');
expect_awsw(awsw_parse_money('1234.56')===123456,'money converts exactly to paisa');
expect_awsw(awsw_parse_money('0.01')===1,'one paisa precision');
foreach(['1e3','-1','1.001','NaN','1,000','1000000000'] as $bad){expect_awsw(awsw_parse_money($bad)===false,'reject invalid money '.$bad);}
expect_awsw(awsw_csv_cell(' =HYPERLINK("x")')[0]==="'",'CSV formula injection protection');
expect_awsw(awsw_private_dir()!==false,'private storage is outside public root');
$campaign=wp_insert_post(['post_type'=>'awsw_campaign','post_title'=>'LOCAL QA — clean water (not live)','post_status'=>'publish','post_content'=>'<!-- wp:paragraph --><p>Local fixture only. No real appeal.</p><!-- /wp:paragraph -->']);
update_post_meta($campaign,'_awsw_target',100000);update_post_meta($campaign,'_awsw_status','active');update_post_meta($campaign,'_awsw_ur_title','صرف مقامی جانچ — صاف پانی');update_post_meta($campaign,'_awsw_ur_content','<p>یہ صرف مقامی جانچ کی مثال ہے، حقیقی اپیل نہیں۔</p>');
$term=get_term_by('slug','water','awsw_program');wp_set_post_terms($campaign,[$term->term_id],'awsw_program');
$now=current_time('mysql',true);
$wpdb->insert($wpdb->prefix.'awsw_donations',['reference'=>'AWS-'.strtoupper(bin2hex(random_bytes(12))),'submission_hash'=>hash('sha256',random_bytes(24)),'transfer_hash'=>hash('sha256',random_bytes(24)),'campaign_id'=>$campaign,'amount_minor'=>123456,'status'=>'pending','donor_name'=>'Local QA','donor_email'=>'qa@example.invalid','transfer_ref'=>'LOCAL-QA-FINANCIAL','created_at'=>$now,'updated_at'=>$now]);$id=(int)$wpdb->insert_id;
expect_awsw($id>0,'donation database insert');expect_awsw(awsw_total($campaign)===0,'pending excluded from totals');
add_filter('pre_wp_mail','__return_false');
expect_awsw(awsw_transition($id,'confirmed','Matched to local test statement')===true,'authorized approval');expect_awsw(awsw_total($campaign)===123456,'approval included exactly once');expect_awsw(awsw_donation($id)->email_state==='failed','mail failure recorded without losing confirmation');
expect_awsw(awsw_transition($id,'confirmed','Duplicate approval')===true,'repeated approval idempotent');expect_awsw(awsw_total($campaign)===123456,'repeated approval does not duplicate total');
expect_awsw((int)$wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->prefix}awsw_audit WHERE donation_id=%d",$id))===1,'one audit event for repeated approval');
$p=awsw_progress($campaign);expect_awsw($p['percent']===123.5&&$p['raised']===123456&&$p['remaining']===0,'overfunding retains actual total and percent');
update_post_meta($campaign,'_awsw_target',0);expect_awsw(awsw_progress($campaign)['percent']===null,'zero target handled safely');update_post_meta($campaign,'_awsw_target',200000);expect_awsw(awsw_progress($campaign)['remaining']===76544,'target changes recalculate remaining');
expect_awsw(awsw_transition($id,'refunded','Completed refund on local statement')===true,'refund recording');expect_awsw(awsw_total($campaign)===0,'refund excluded from confirmed total');expect_awsw(is_wp_error(awsw_transition($id,'confirmed','Try to restore refund')),'immutable terminal financial state');
$wpdb->insert($wpdb->prefix.'awsw_donations',['reference'=>'AWS-'.strtoupper(bin2hex(random_bytes(12))),'submission_hash'=>hash('sha256',random_bytes(24)),'transfer_hash'=>hash('sha256',random_bytes(24)),'campaign_id'=>$campaign,'amount_minor'=>50000,'status'=>'pending','donor_name'=>'Local QA','donor_email'=>'qa2@example.invalid','transfer_ref'=>'LOCAL-QA-REJECT','created_at'=>$now,'updated_at'=>$now]);$rejected=(int)$wpdb->insert_id;expect_awsw(awsw_transition($rejected,'rejected','Not present on test bank statement')===true,'rejection recorded');expect_awsw(awsw_total($campaign)===0,'rejection excluded');
$editor_user=get_user_by('login','awsw_qa_editor');$editor=$editor_user?$editor_user->ID:wp_insert_user(['user_login'=>'awsw_qa_editor','user_pass'=>wp_generate_password(40),'user_email'=>'editor@example.invalid','role'=>'editor']);
wp_set_current_user($editor);expect_awsw(!current_user_can('awsw_manage_settings'),'content editor cannot change payment settings');expect_awsw(!current_user_can('awsw_manage_donations'),'content editor cannot access private donations or proofs');expect_awsw(is_wp_error(awsw_transition($id,'confirmed','Unauthorized')),'content editor cannot verify donation');
$manager_user=get_user_by('login','awsw_qa_manager');$manager=$manager_user?$manager_user->ID:wp_insert_user(['user_login'=>'awsw_qa_manager','user_pass'=>wp_generate_password(40),'user_email'=>'manager@example.invalid','role'=>'awsw_donation_manager']);wp_set_current_user($manager);expect_awsw(current_user_can('awsw_manage_donations')&&!current_user_can('awsw_manage_settings'),'donation manager has least privilege');
wp_set_current_user(1);$before=(int)$wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}awsw_donations");switch_theme('twentytwentyfive');expect_awsw((int)$wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}awsw_donations")===$before&&get_post_type($campaign)==='awsw_campaign','campaigns and donations survive theme switch');switch_theme('aws-welfare-theme');
update_option('awsw_qa_campaign',$campaign);update_option('awsw_qa_financial_ids',[$id,$rejected]);
echo 'FINANCIAL QA: '.$GLOBALS['awsw_passed']." assertions passed.\n";
