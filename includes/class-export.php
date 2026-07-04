<?php
if (!defined('ABSPATH')) { exit; }

class RWLC_Export {
    public static function maybe_export() {
        if (empty($_GET['rwlc_export']) || !current_user_can('rwlc_manage_crm')) { return; }
        check_admin_referer('rwlc_export');
        global $wpdb;
        $type = sanitize_key($_GET['rwlc_export']);
        $table = $type === 'jobs' ? rwlc_table('jobs') : rwlc_table('customers');
        $rows = $wpdb->get_results("SELECT * FROM $table", ARRAY_A);
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="rogue-crm-' . $type . '.csv"');
        $out = fopen('php://output', 'w');
        if ($rows) { fputcsv($out, array_keys($rows[0])); foreach ($rows as $row) { fputcsv($out, $row); } }
        fclose($out); exit;
    }
}
