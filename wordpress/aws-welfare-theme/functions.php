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
add_action('customize_register',function($wp_customize){$wp_customize->add_section('awsw_brand',['title'=>'AWS Welfare brand colors']);$wp_customize->add_setting('awsw_announcement_enabled',['default'=>true,'sanitize_callback'=>'rest_sanitize_boolean']);$wp_customize->add_control('awsw_announcement_enabled',['type'=>'checkbox','section'=>'awsw_brand','label'=>'Show announcement strip']);foreach(['emerald'=>'#064E3B','primary'=>'#15803D','ivory'=>'#FAF9F6','charcoal'=>'#17211C','gold'=>'#C5A059'] as $key=>$default){$wp_customize->add_setting('awsw_'.$key,['default'=>$default,'sanitize_callback'=>'sanitize_hex_color']);$wp_customize->add_control(new WP_Customize_Color_Control($wp_customize,'awsw_'.$key,['label'=>ucfirst($key),'section'=>'awsw_brand']));}});
function awsw_theme_t($en,$ur){return function_exists('awsw_t')?awsw_t($en,$ur):$en;}
function awsw_theme_url($slug=''){return function_exists('awsw_url')?awsw_url($slug):home_url('/'.$slug);}
function awsw_theme_brand(){if(has_custom_logo()){the_custom_logo();}else{echo '<a class="awsw-wordmark" href="'.esc_url(awsw_theme_url()).'" aria-label="AWS Welfare"><span>A<span>W</span>S</span><small>W E L F A R E</small></a>';}}
function awsw_theme_navigation(){
    $lang=function_exists('awsw_lang')?awsw_lang():'en';$location='primary_'.$lang;
    if(has_nav_menu($location)){wp_nav_menu(['theme_location'=>$location,'container'=>false,'menu_class'=>'awsw-menu','depth'=>2]);return;}
    $groups=[
      ['About','ہمارے بارے میں',['about'=>['About AWS Welfare','ادارے کا تعارف'],'team'=>['Team & governance','ٹیم اور انتظام']]],
      ['Our Work','ہمارا کام',['programs'=>['Program directory','تمام شعبے'],'food'=>['Food & ration','خوراک اور راشن'],'education'=>['Education','تعلیم'],'water'=>['Clean water','صاف پانی'],'medical'=>['Medical assistance','طبی معاونت'],'impact'=>['Impact','اثرات'],'updates'=>['Project updates','منصوبوں کی خبریں']]],
      ['Get Involved','حصہ بنیں',['get-involved'=>['Ways to help','تعاون کے طریقے'],'volunteer'=>['Volunteering','رضاکارانہ خدمت'],'volunteer-application'=>['Apply to volunteer','رضاکار درخواست'],'partnerships'=>['Corporate partnerships','اداروں کے ساتھ شراکت'],'in-kind'=>['In-kind support','اشیاء کی مدد'],'request-assistance'=>['Request assistance','معاونت کی درخواست']]],
      ['Resources','وسائل',['blog'=>['Insights','مضامین'],'transparency'=>['Reports & transparency','رپورٹس اور شفافیت'],'gallery'=>['Gallery','تصاویر'],'faq'=>['FAQs','سوالات'],'donation-status'=>['Donation status','عطیے کی حالت']]],
    ];
    foreach($groups as $i=>$g){if($i===2){echo '<a href="'.esc_url(awsw_theme_url('campaigns/')).'">'.esc_html(awsw_theme_t('Campaigns','مہمات')).'</a>';}
      echo '<details class="awsw-nav-group"><summary>'.esc_html(awsw_theme_t($g[0],$g[1])).(function_exists('awsw_icon')?awsw_icon('chevron'):'').'</summary><div class="awsw-submenu">';
      foreach($g[2] as $slug=>$label){echo '<a href="'.esc_url(awsw_theme_url($slug.'/')).'">'.esc_html(awsw_theme_t(...$label)).'</a>';}
      echo '</div></details>';
    }
    echo '<a href="'.esc_url(awsw_theme_url('contact/')).'">'.esc_html(awsw_theme_t('Contact','رابطہ')).'</a>';
}
add_action('admin_notices',function(){if(!function_exists('awsw_activate')&&current_user_can('activate_plugins')){echo '<div class="notice notice-warning"><p>AWS Welfare: activate the separate AWS Welfare Core plugin to enable campaigns, bank-transfer forms and bilingual starter pages. Existing data remains in the database.</p></div>';}});
