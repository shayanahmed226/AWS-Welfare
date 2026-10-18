<?php
if (!defined('ABSPATH')) { exit; }
function awsw_lang() { return isset($_GET['lang']) && 'ur' === sanitize_key(wp_unslash($_GET['lang'])) ? 'ur' : 'en'; }
function awsw_t($en, $ur) { return 'ur' === awsw_lang() ? $ur : $en; }
function awsw_url($path = '', $lang = null) { return add_query_arg('lang', $lang ?: awsw_lang(), home_url('/' . ltrim($path, '/'))); }
function awsw_settings() { return (array) get_option('awsw_settings', []); }
function awsw_setting($key) { $s = awsw_settings(); return isset($s[$key]) ? $s[$key] : ''; }
function awsw_field($post_id, $key, $english = '') {
    if ('ur' !== awsw_lang()) { return $english; }
    $value = get_post_meta($post_id, '_awsw_ur_' . $key, true);
    return $value ?: awsw_t('Translation pending', 'اردو ترجمہ زیرِ تیاری ہے۔') . ($english ? ' — ' . $english : '');
}
add_filter('language_attributes', function ($output) { return is_admin() ? $output : 'lang="' . (awsw_lang() === 'ur' ? 'ur-PK' : 'en-PK') . '" dir="' . (awsw_lang() === 'ur' ? 'rtl' : 'ltr') . '"'; });
add_filter('the_content', function ($content) {
    if ('ur' === awsw_lang() && is_singular('page') && in_the_loop() && is_main_query()) {
        $ur = get_post_meta(get_the_ID(), '_awsw_ur_content', true);
        if ($ur) { return do_blocks($ur); }
        if (strpos($content, '[awsw_') === false) { return '<p class="awsw-notice">اردو ترجمہ زیرِ تیاری ہے۔ ذیل میں اصل انگریزی متن ہے۔</p>' . $content; }
    }
    return $content;
}, 8);
add_action('wp_head', function () {
    if (is_404() || is_search()) { return; }
    $url = is_singular() ? get_permalink() : (is_front_page() ? home_url('/') : get_post_type_archive_link(get_post_type() ?: 'awsw_campaign'));
    if (!$url) { return; }
    foreach (['en' => 'en-PK', 'ur' => 'ur-PK'] as $lang => $code) { echo '<link rel="alternate" hreflang="' . esc_attr($code) . '" href="' . esc_url(add_query_arg('lang', $lang, $url)) . '">'; }
    echo '<meta property="og:site_name" content="AWS Welfare"><meta property="og:title" content="' . esc_attr(wp_get_document_title()) . '">';
    echo '<meta property="og:url" content="' . esc_url(add_query_arg('lang', awsw_lang(), $url)) . '"><meta property="og:type" content="website">';
    $description = is_singular() ? get_post_meta(get_the_ID(), '_awsw_description', true) : '';
    if ($description) { echo '<meta name="description" content="' . esc_attr($description) . '"><meta property="og:description" content="' . esc_attr($description) . '">'; }
    $og_image = is_singular() ? get_post_meta(get_the_ID(), '_awsw_og_image', true) : '';
    if (!$og_image && is_singular() && has_post_thumbnail()) { $og_image = get_the_post_thumbnail_url(null, 'large'); }
    if ($og_image) { echo '<meta property="og:image" content="' . esc_url($og_image) . '">'; }
    echo '<meta property="og:locale" content="' . (awsw_lang() === 'ur' ? 'ur_PK' : 'en_PK') . '">';
    if (is_front_page()) { echo '<script type="application/ld+json">' . wp_json_encode(['@context' => 'https://schema.org', '@type' => 'Organization', 'name' => 'AWS Welfare', 'url' => home_url('/')], JSON_HEX_TAG | JSON_HEX_AMP) . '</script>'; }
});
remove_action('wp_head', 'rel_canonical');
add_action('wp_head', function () { if (is_singular()) { echo '<link rel="canonical" href="' . esc_url(add_query_arg('lang', awsw_lang(), get_permalink())) . '">'; } });
add_filter('pre_get_document_title', function ($title) {
    if (awsw_lang() === 'ur' && is_singular()) { return awsw_post_title(get_queried_object_id()) . ' | AWS Welfare'; }
    return $title;
});
add_filter('wp_sitemaps_posts_query_args', function ($args, $type) {
    if ($type === 'page') { foreach (['donation-confirmation','payment-cancelled'] as $slug) { $p = get_page_by_path($slug); if ($p) { $args['post__not_in'][] = $p->ID; } } }
    return $args;
}, 10, 2);
