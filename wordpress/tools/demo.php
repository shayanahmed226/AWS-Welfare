<?php
// Optional WP-CLI demo importer/remover, never part of production activation.
if (!defined('WP_CLI') || !WP_CLI || wp_get_environment_type() !== 'local') { throw new RuntimeException('Demo content is restricted to WP-CLI on WP_ENVIRONMENT_TYPE=local.'); }
$mode=$args[0]??'';
if(!in_array($mode,['import','remove'],true)){WP_CLI::error('Usage: wp eval-file tools/demo.php import|remove');}
$existing=get_posts(['post_type'=>['awsw_campaign','awsw_update'],'post_status'=>'any','numberposts'=>-1,'meta_key'=>'_awsw_demo','meta_value'=>'1']);
if($mode==='remove'){foreach($existing as $p){if($p->post_type==='awsw_campaign'){global $wpdb;$count=$wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->prefix}awsw_donations WHERE campaign_id=%d",$p->ID));if($count){wp_update_post(['ID'=>$p->ID,'post_status'=>'draft']);WP_CLI::warning('A demo campaign with financial records was unpublished instead of deleted. Records retained.');continue;}}wp_delete_post($p->ID,true);}WP_CLI::success('Demo content removed/unpublished; financial records preserved.');return;}
if($existing){WP_CLI::success('Demo content already exists. No duplicates created.');return;}
$id=wp_insert_post(['post_type'=>'awsw_campaign','post_status'=>'publish','post_title'=>'DEMO ONLY — Community clean water','post_content'=>'<!-- wp:paragraph --><p>LOCAL DEMO ONLY. No real project, beneficiary claim or donation appeal. This campaign is paused.</p><!-- /wp:paragraph -->']);
update_post_meta($id,'_awsw_demo','1');update_post_meta($id,'_awsw_status','paused');update_post_meta($id,'_awsw_target',1000000);update_post_meta($id,'_awsw_use_funds','Demo budget only — no real use of funds.');update_post_meta($id,'_awsw_ur_title','صرف نمونہ — اجتماعی صاف پانی');update_post_meta($id,'_awsw_ur_content','<p>صرف مقامی نمونہ۔ حقیقی منصوبہ یا عطیات کی اپیل نہیں۔ یہ مہم معطل ہے۔</p>');update_post_meta($id,'_awsw_ur_use_funds','صرف فرضی مقامی بجٹ، حقیقی اخراجات نہیں۔');
$t=get_term_by('slug','water','awsw_program');wp_set_post_terms($id,[$t->term_id],'awsw_program');WP_CLI::success('One labeled, paused local demo campaign created. No bank details, donations or impact figures seeded.');
