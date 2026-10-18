<?php
if (!defined('ABSPATH')) { exit; }
add_action('after_setup_theme', function () {
    add_theme_support('title-tag'); add_theme_support('post-thumbnails'); add_theme_support('custom-logo',['height'=>100,'width'=>300,'flex-height'=>true,'flex-width'=>true]); add_theme_support('html5',['search-form','gallery','caption','style','script']); add_theme_support('editor-styles'); add_editor_style('assets/theme.css'); add_theme_support('wp-block-styles'); add_theme_support('align-wide'); add_theme_support('responsive-embeds');
    register_nav_menus(['primary_en'=>'Primary navigation (English)','primary_ur'=>'Primary navigation (Urdu)']);
    register_block_pattern_category('aws-welfare',['label'=>'AWS Welfare']);
});
add_action('wp_enqueue_scripts',function(){wp_enqueue_style('awsw-theme',get_theme_file_uri('assets/theme.css'),[],wp_get_theme()->get('Version'));wp_enqueue_script('awsw-theme',get_theme_file_uri('assets/theme.js'),[],wp_get_theme()->get('Version'),true);
    $css=':root{';foreach(['emerald'=>'#064E3B','primary'=>'#15803D','ivory'=>'#FAF9F6','charcoal'=>'#17211C','gold'=>'#C5A059'] as $key=>$default){$css.='--awsw-'.$key.':'.(sanitize_hex_color(get_theme_mod('awsw_'.$key,$default))?:$default).';';}wp_add_inline_style('awsw-theme',$css.'}');
});
add_action('customize_register',function($wp_customize){$wp_customize->add_section('awsw_brand',['title'=>'AWS Welfare brand colors']);foreach(['emerald'=>'#064E3B','primary'=>'#15803D','ivory'=>'#FAF9F6','charcoal'=>'#17211C','gold'=>'#C5A059'] as $key=>$default){$wp_customize->add_setting('awsw_'.$key,['default'=>$default,'sanitize_callback'=>'sanitize_hex_color']);$wp_customize->add_control(new WP_Customize_Color_Control($wp_customize,'awsw_'.$key,['label'=>ucfirst($key),'section'=>'awsw_brand']));}});
function awsw_theme_t($en,$ur){return function_exists('awsw_t')?awsw_t($en,$ur):$en;}
function awsw_theme_url($slug=''){return function_exists('awsw_url')?awsw_url($slug):home_url('/'.$slug);}
function awsw_theme_brand(){if(has_custom_logo()){the_custom_logo();}else{echo '<a class="awsw-wordmark" href="'.esc_url(awsw_theme_url()).'" aria-label="AWS Welfare"><span>A<span>W</span>S</span><small>W E L F A R E</small></a>';}}
function awsw_theme_navigation(){
    $lang=function_exists('awsw_lang')?awsw_lang():'en';$location='primary_'.$lang;
    if(has_nav_menu($location)){wp_nav_menu(['theme_location'=>$location,'container'=>false,'menu_class'=>'awsw-menu','depth'=>1]);return;}
    foreach(['about'=>['Who we are','ہمارے بارے میں'],'programs'=>['Our programs','ہمارے شعبے'],'campaigns'=>['Campaigns','مہمات'],'transparency'=>['Transparency','شفافیت'],'volunteer'=>['Get involved','حصہ بنیں']] as $slug=>$label){echo '<a href="'.esc_url(awsw_theme_url($slug.'/')).'">'.esc_html(awsw_theme_t(...$label)).'</a>';}
}
add_action('admin_notices',function(){if(!function_exists('awsw_activate')&&current_user_can('activate_plugins')){echo '<div class="notice notice-warning"><p>AWS Welfare: activate the separate AWS Welfare Core plugin to enable campaigns, bank-transfer forms and bilingual starter pages. Existing data remains in the database.</p></div>';}});
