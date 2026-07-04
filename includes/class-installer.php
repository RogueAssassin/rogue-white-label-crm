<?php
if (!defined('ABSPATH')) { exit; }

class RWLC_Installer {
    public static function activate() {
        self::create_tables();
        self::add_roles();
        self::default_settings();
        update_option('rwlc_version', RWLC_VERSION);
        flush_rewrite_rules();
    }

    public static function deactivate() {
        flush_rewrite_rules();
    }

    public static function create_tables() {
        global $wpdb;
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        $charset = $wpdb->get_charset_collate();

        $customers = rwlc_table('customers');
        $jobs = rwlc_table('jobs');
        $notes = rwlc_table('notes');

        dbDelta("CREATE TABLE $customers (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            company VARCHAR(190) DEFAULT '',
            contact_name VARCHAR(190) DEFAULT '',
            email VARCHAR(190) DEFAULT '',
            phone VARCHAR(80) DEFAULT '',
            website VARCHAR(190) DEFAULT '',
            address TEXT NULL,
            notes LONGTEXT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            KEY email (email)
        ) $charset;");

        dbDelta("CREATE TABLE $jobs (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            job_number VARCHAR(80) NOT NULL,
            customer_id BIGINT UNSIGNED DEFAULT 0,
            title VARCHAR(190) NOT NULL,
            description LONGTEXT NULL,
            status VARCHAR(80) DEFAULT 'Pending',
            assigned_user BIGINT UNSIGNED DEFAULT 0,
            due_date DATE NULL,
            amount DECIMAL(12,2) DEFAULT 0,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY job_number (job_number),
            KEY customer_id (customer_id),
            KEY status (status)
        ) $charset;");

        dbDelta("CREATE TABLE $notes (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            object_type VARCHAR(40) NOT NULL,
            object_id BIGINT UNSIGNED NOT NULL,
            user_id BIGINT UNSIGNED DEFAULT 0,
            note LONGTEXT NOT NULL,
            created_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            KEY object_lookup (object_type, object_id)
        ) $charset;");
    }

    public static function add_roles() {
        add_role('rwlc_manager', 'CRM Manager', ['read' => true, 'rwlc_manage_crm' => true]);
        add_role('rwlc_staff', 'CRM Staff', ['read' => true, 'rwlc_read_crm' => true]);
        $admin = get_role('administrator');
        if ($admin) {
            $admin->add_cap('rwlc_manage_crm');
            $admin->add_cap('rwlc_read_crm');
        }
    }

    public static function default_settings() {
        if (get_option('rwlc_settings', false) !== false) { return; }
        add_option('rwlc_settings', [
            'business_name' => get_bloginfo('name'),
            'business_email' => get_bloginfo('admin_email'),
            'business_phone' => '',
            'business_website' => home_url('/'),
            'primary_color' => '#1597ff',
            'secondary_color' => '#0b1220',
            'currency' => '$',
            'job_prefix' => 'JOB-',
            'job_statuses' => "Pending\nBooked\nIn Progress\nCompleted\nCancelled",
            'pdf_terms' => "Thank you for your business.",
            'logo_id' => 0,
        ]);
    }
}
