<?php
if (!defined('ABSPATH')) { exit; }

function rwlc_table($name) {
    global $wpdb;
    return $wpdb->prefix . 'rwlc_' . sanitize_key($name);
}

function rwlc_get_option($key = null, $default = null) {
    $opts = get_option('rwlc_settings', []);
    if ($key === null) { return is_array($opts) ? $opts : []; }
    return isset($opts[$key]) ? $opts[$key] : $default;
}

function rwlc_brand_name() {
    return esc_html(rwlc_get_option('business_name', get_bloginfo('name') ?: 'Rogue CRM'));
}

function rwlc_statuses() {
    $raw = rwlc_get_option('job_statuses', "Pending\nBooked\nIn Progress\nCompleted\nCancelled");
    $items = array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $raw)));
    return $items ?: ['Pending', 'Booked', 'In Progress', 'Completed', 'Cancelled'];
}

function rwlc_money($amount) {
    $currency = rwlc_get_option('currency', '$');
    return esc_html($currency . number_format((float) $amount, 2));
}
