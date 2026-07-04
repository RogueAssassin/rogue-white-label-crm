<?php
if (!defined('ABSPATH')) { exit; }

class RWLC_Admin {
    public static function init() {
        add_action('admin_menu', [__CLASS__, 'menu']);
        add_action('admin_enqueue_scripts', [__CLASS__, 'assets']);
        add_action('admin_init', ['RWLC_Export', 'maybe_export']);
        add_action('admin_post_rwlc_save_customer', [__CLASS__, 'save_customer']);
        add_action('admin_post_rwlc_save_job', [__CLASS__, 'save_job']);
        add_action('admin_post_rwlc_delete_customer', [__CLASS__, 'delete_customer']);
        add_action('admin_post_rwlc_delete_job', [__CLASS__, 'delete_job']);
    }

    public static function menu() {
        $cap = 'rwlc_read_crm';
        add_menu_page('Rogue CRM', 'Rogue CRM', $cap, 'rwlc-dashboard', [__CLASS__, 'dashboard'], 'dashicons-index-card', 26);
        add_submenu_page('rwlc-dashboard', 'Dashboard', 'Dashboard', $cap, 'rwlc-dashboard', [__CLASS__, 'dashboard']);
        add_submenu_page('rwlc-dashboard', 'Customers', 'Customers', $cap, 'rwlc-customers', [__CLASS__, 'customers']);
        add_submenu_page('rwlc-dashboard', 'Jobs', 'Jobs', $cap, 'rwlc-jobs', [__CLASS__, 'jobs']);
        add_submenu_page('rwlc-dashboard', 'Settings', 'Settings', 'rwlc_manage_crm', 'rwlc-settings', [__CLASS__, 'settings']);
    }

    public static function assets($hook) {
        if (strpos($hook, 'rwlc') === false) { return; }
        wp_enqueue_style('rwlc-admin', RWLC_URL . 'assets/css/admin.css', [], RWLC_VERSION);
        wp_enqueue_media();
        wp_enqueue_script('rwlc-admin', RWLC_URL . 'assets/js/admin.js', ['jquery'], RWLC_VERSION, true);
    }

    private static function notice_redirect($page, $msg = 'saved') {
        wp_safe_redirect(admin_url('admin.php?page=' . $page . '&rwlc_msg=' . urlencode($msg))); exit;
    }

    public static function save_customer() {
        if (!current_user_can('rwlc_manage_crm')) { wp_die('Permission denied'); }
        check_admin_referer('rwlc_save_customer');
        RWLC_CRUD::save_customer($_POST);
        self::notice_redirect('rwlc-customers');
    }

    public static function save_job() {
        if (!current_user_can('rwlc_manage_crm')) { wp_die('Permission denied'); }
        check_admin_referer('rwlc_save_job');
        RWLC_CRUD::save_job($_POST);
        self::notice_redirect('rwlc-jobs');
    }

    public static function delete_customer() {
        if (!current_user_can('rwlc_manage_crm')) { wp_die('Permission denied'); }
        check_admin_referer('rwlc_delete_customer');
        RWLC_CRUD::delete_customer($_GET['id'] ?? 0);
        self::notice_redirect('rwlc-customers', 'deleted');
    }

    public static function delete_job() {
        if (!current_user_can('rwlc_manage_crm')) { wp_die('Permission denied'); }
        check_admin_referer('rwlc_delete_job');
        RWLC_CRUD::delete_job($_GET['id'] ?? 0);
        self::notice_redirect('rwlc-jobs', 'deleted');
    }

    public static function dashboard() {
        global $wpdb;
        $customers = (int) $wpdb->get_var('SELECT COUNT(*) FROM ' . rwlc_table('customers'));
        $jobs = (int) $wpdb->get_var('SELECT COUNT(*) FROM ' . rwlc_table('jobs'));
        $completed = (int) $wpdb->get_var($wpdb->prepare('SELECT COUNT(*) FROM ' . rwlc_table('jobs') . ' WHERE status=%s', 'Completed'));
        $revenue = (float) $wpdb->get_var('SELECT SUM(amount) FROM ' . rwlc_table('jobs'));
        echo '<div class="wrap rwlc"><h1>' . rwlc_brand_name() . ' CRM Dashboard</h1><div class="rwlc-cards">';
        foreach ([['Customers',$customers],['Active Jobs',$jobs],['Completed',$completed],['Revenue',rwlc_money($revenue)]] as $card) {
            echo '<div class="rwlc-card"><span>' . esc_html($card[0]) . '</span><strong>' . esc_html($card[1]) . '</strong></div>';
        }
        echo '</div><p class="rwlc-muted">Configure branding, users, job statuses, and business details from CRM Settings.</p></div>';
    }

    public static function customers() {
        global $wpdb;
        if (!empty($_GET['rwlc_msg'])) { echo '<div class="notice notice-success"><p>Customer ' . esc_html($_GET['rwlc_msg']) . '.</p></div>'; }
        $rows = $wpdb->get_results('SELECT * FROM ' . rwlc_table('customers') . ' ORDER BY id DESC LIMIT 200');
        echo '<div class="wrap rwlc"><h1>Customers</h1>';
        if (current_user_can('rwlc_manage_crm')) self::customer_form();
        echo '<p><a class="button" href="' . esc_url(wp_nonce_url(admin_url('admin.php?page=rwlc-customers&rwlc_export=customers'), 'rwlc_export')) . '">Export CSV</a></p>';
        echo '<table class="widefat striped"><thead><tr><th>ID</th><th>Company</th><th>Contact</th><th>Email</th><th>Phone</th><th>Actions</th></tr></thead><tbody>';
        foreach ($rows as $r) {
            $del = wp_nonce_url(admin_url('admin-post.php?action=rwlc_delete_customer&id=' . $r->id), 'rwlc_delete_customer');
            echo '<tr><td>' . absint($r->id) . '</td><td>' . esc_html($r->company) . '</td><td>' . esc_html($r->contact_name) . '</td><td>' . esc_html($r->email) . '</td><td>' . esc_html($r->phone) . '</td><td><a class="button button-link-delete" onclick="return confirm(\'Delete this customer?\')" href="' . esc_url($del) . '">Delete</a></td></tr>';
        }
        echo '</tbody></table></div>';
    }

    private static function customer_form() {
        echo '<div class="rwlc-panel"><h2>Add Customer</h2><form method="post" action="' . esc_url(admin_url('admin-post.php')) . '"><input type="hidden" name="action" value="rwlc_save_customer">';
        wp_nonce_field('rwlc_save_customer');
        echo '<div class="rwlc-grid"><input name="company" placeholder="Company"><input name="contact_name" placeholder="Contact name"><input name="email" placeholder="Email"><input name="phone" placeholder="Phone"><input name="website" placeholder="Website"></div><textarea name="address" placeholder="Address"></textarea><textarea name="notes" placeholder="Notes"></textarea><p><button class="button button-primary">Save Customer</button></p></form></div>';
    }

    public static function jobs() {
        global $wpdb;
        if (!empty($_GET['rwlc_msg'])) { echo '<div class="notice notice-success"><p>Job ' . esc_html($_GET['rwlc_msg']) . '.</p></div>'; }
        $rows = $wpdb->get_results('SELECT j.*, c.company FROM ' . rwlc_table('jobs') . ' j LEFT JOIN ' . rwlc_table('customers') . ' c ON c.id=j.customer_id ORDER BY j.id DESC LIMIT 200');
        echo '<div class="wrap rwlc"><h1>Jobs</h1>';
        if (current_user_can('rwlc_manage_crm')) self::job_form();
        echo '<p><a class="button" href="' . esc_url(wp_nonce_url(admin_url('admin.php?page=rwlc-jobs&rwlc_export=jobs'), 'rwlc_export')) . '">Export CSV</a></p>';
        echo '<table class="widefat striped"><thead><tr><th>Job</th><th>Title</th><th>Customer</th><th>Status</th><th>Due</th><th>Amount</th><th>Actions</th></tr></thead><tbody>';
        foreach ($rows as $r) {
            $del = wp_nonce_url(admin_url('admin-post.php?action=rwlc_delete_job&id=' . $r->id), 'rwlc_delete_job');
            echo '<tr><td>' . esc_html($r->job_number) . '</td><td>' . esc_html($r->title) . '</td><td>' . esc_html($r->company) . '</td><td>' . esc_html($r->status) . '</td><td>' . esc_html($r->due_date) . '</td><td>' . rwlc_money($r->amount) . '</td><td><a class="button button-link-delete" onclick="return confirm(\'Delete this job?\')" href="' . esc_url($del) . '">Delete</a></td></tr>';
        }
        echo '</tbody></table></div>';
    }

    private static function job_form() {
        global $wpdb;
        $customers = $wpdb->get_results('SELECT id, company, contact_name FROM ' . rwlc_table('customers') . ' ORDER BY company ASC');
        echo '<div class="rwlc-panel"><h2>Add Job</h2><form method="post" action="' . esc_url(admin_url('admin-post.php')) . '"><input type="hidden" name="action" value="rwlc_save_job">';
        wp_nonce_field('rwlc_save_job');
        echo '<div class="rwlc-grid"><input name="title" placeholder="Job title"><select name="customer_id"><option value="0">No customer</option>';
        foreach ($customers as $c) echo '<option value="' . absint($c->id) . '">' . esc_html($c->company ?: $c->contact_name) . '</option>';
        echo '</select><select name="status">'; foreach (rwlc_statuses() as $s) echo '<option>' . esc_html($s) . '</option>';
        echo '</select><input type="date" name="due_date"><input name="amount" type="number" step="0.01" placeholder="Amount"></div><textarea name="description" placeholder="Job description"></textarea><p><button class="button button-primary">Save Job</button></p></form></div>';
    }

    public static function settings() {
        $opts = rwlc_get_option();
        echo '<div class="wrap rwlc"><h1>CRM Settings</h1><form method="post" action="options.php">';
        settings_fields('rwlc_settings_group');
        echo '<div class="rwlc-panel"><h2>White Label Branding</h2><div class="rwlc-grid">';
        foreach (['business_name'=>'Business Name','business_email'=>'Business Email','business_phone'=>'Business Phone','business_website'=>'Website','primary_color'=>'Primary Colour','secondary_color'=>'Secondary Colour','currency'=>'Currency Symbol','job_prefix'=>'Job Prefix'] as $key=>$label) {
            echo '<label><span>' . esc_html($label) . '</span><input name="rwlc_settings[' . esc_attr($key) . ']" value="' . esc_attr($opts[$key] ?? '') . '"></label>';
        }
        echo '</div><label><span>Job Statuses, one per line</span><textarea name="rwlc_settings[job_statuses]">' . esc_textarea($opts['job_statuses'] ?? '') . '</textarea></label><label><span>PDF / Quote Terms</span><textarea name="rwlc_settings[pdf_terms]">' . esc_textarea($opts['pdf_terms'] ?? '') . '</textarea></label><p><button class="button button-primary">Save Settings</button></p></div></form></div>';
    }
}
