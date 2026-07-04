<?php
if (!defined('ABSPATH')) { exit; }

class RWLC_Settings {
    public static function init() {
        add_action('admin_init', [__CLASS__, 'register']);
    }

    public static function register() {
        register_setting('rwlc_settings_group', 'rwlc_settings', [__CLASS__, 'sanitize']);
    }

    public static function sanitize($input) {
        $old = rwlc_get_option();
        $out = [];
        $text = ['business_name','business_email','business_phone','business_website','primary_color','secondary_color','currency','job_prefix'];
        foreach ($text as $key) { $out[$key] = sanitize_text_field($input[$key] ?? ($old[$key] ?? '')); }
        $out['job_statuses'] = sanitize_textarea_field($input['job_statuses'] ?? ($old['job_statuses'] ?? ''));
        $out['pdf_terms'] = wp_kses_post($input['pdf_terms'] ?? ($old['pdf_terms'] ?? ''));
        $out['logo_id'] = absint($input['logo_id'] ?? ($old['logo_id'] ?? 0));
        return $out;
    }
}
