<?php
/**
 * Plugin Name: AWS Welfare Core
 * Description: Bilingual campaigns, private bank-transfer verification, reports and enquiries for AWS Welfare.
 * Version: 1.0.0
 * Requires at least: 6.6
 * Requires PHP: 8.1
 * Text Domain: aws-welfare
 * License: GPL-2.0-or-later
 */
if (!defined('ABSPATH')) { exit; }
define('AWSW_VERSION', '1.0.0');
define('AWSW_DIR', plugin_dir_path(__FILE__));
define('AWSW_URL', plugin_dir_url(__FILE__));
foreach (['language', 'data', 'forms', 'public', 'admin', 'content', 'home-blocks'] as $awsw_file) { require_once AWSW_DIR . 'includes/' . $awsw_file . '.php'; }
register_activation_hook(__FILE__, 'awsw_activate');
add_action('init', 'awsw_register_content');
add_action('init', function () { if (get_option('awsw_db_version') !== AWSW_VERSION) { awsw_activate(); } });
add_action('wp_enqueue_scripts', function () {
    global $post;
    $types = ['awsw_campaign','awsw_update','awsw_report','awsw_metric'];
    if (wp_get_theme()->get_stylesheet() !== 'aws-welfare-theme' && !is_singular($types) && !is_post_type_archive($types) && !($post && str_contains($post->post_content, '[awsw_'))) { return; }
    wp_enqueue_style('awsw-core', AWSW_URL . 'assets/core.css', [], AWSW_VERSION);
    wp_enqueue_script('awsw-core', AWSW_URL . 'assets/core.js', [], AWSW_VERSION, true);
});
add_action('template_redirect', function () {
    if (is_page(['donate', 'donation-confirmation', 'volunteer', 'contact']) || isset($_GET['awsw_notice'])) {
        nocache_headers();
        if (is_page('donation-confirmation') || isset($_GET['awsw_notice'])) { header('X-Robots-Tag: noindex, nofollow'); header('Referrer-Policy: no-referrer'); }
    }
});
add_action('send_headers', function () { header('X-Content-Type-Options: nosniff'); header('Referrer-Policy: strict-origin-when-cross-origin'); });
add_filter('wp_robots', function ($robots) { if (is_page('donation-confirmation')) { $robots['noindex'] = true; } return $robots; });
// Future approved providers register server-side handlers here. No gateway is enabled by default.
function awsw_gateways() { return apply_filters('awsw_payment_gateways', []); }
