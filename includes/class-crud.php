<?php
if (!defined('ABSPATH')) { exit; }

class RWLC_CRUD {
    public static function save_customer($data) {
        global $wpdb;
        $now = current_time('mysql');
        $row = [
            'company' => sanitize_text_field($data['company'] ?? ''),
            'contact_name' => sanitize_text_field($data['contact_name'] ?? ''),
            'email' => sanitize_email($data['email'] ?? ''),
            'phone' => sanitize_text_field($data['phone'] ?? ''),
            'website' => esc_url_raw($data['website'] ?? ''),
            'address' => sanitize_textarea_field($data['address'] ?? ''),
            'notes' => sanitize_textarea_field($data['notes'] ?? ''),
            'updated_at' => $now,
        ];
        if (!empty($data['id'])) {
            $wpdb->update(rwlc_table('customers'), $row, ['id' => absint($data['id'])]);
            return absint($data['id']);
        }
        $row['created_at'] = $now;
        $wpdb->insert(rwlc_table('customers'), $row);
        return (int) $wpdb->insert_id;
    }

    public static function save_job($data) {
        global $wpdb;
        $now = current_time('mysql');
        $job_number = sanitize_text_field($data['job_number'] ?? '');
        if (!$job_number) { $job_number = self::next_job_number(); }
        $row = [
            'job_number' => $job_number,
            'customer_id' => absint($data['customer_id'] ?? 0),
            'title' => sanitize_text_field($data['title'] ?? ''),
            'description' => sanitize_textarea_field($data['description'] ?? ''),
            'status' => sanitize_text_field($data['status'] ?? 'Pending'),
            'assigned_user' => absint($data['assigned_user'] ?? 0),
            'due_date' => sanitize_text_field($data['due_date'] ?? ''),
            'amount' => (float) ($data['amount'] ?? 0),
            'updated_at' => $now,
        ];
        if (!empty($data['id'])) {
            $wpdb->update(rwlc_table('jobs'), $row, ['id' => absint($data['id'])]);
            return absint($data['id']);
        }
        $row['created_at'] = $now;
        $wpdb->insert(rwlc_table('jobs'), $row);
        return (int) $wpdb->insert_id;
    }

    public static function next_job_number() {
        global $wpdb;
        $prefix = rwlc_get_option('job_prefix', 'JOB-');
        $last = (int) $wpdb->get_var("SELECT id FROM " . rwlc_table('jobs') . " ORDER BY id DESC LIMIT 1");
        return $prefix . str_pad((string) ($last + 1), 4, '0', STR_PAD_LEFT);
    }

    public static function delete_job($id) {
        global $wpdb;
        $id = absint($id);
        $wpdb->delete(rwlc_table('notes'), ['object_type' => 'job', 'object_id' => $id]);
        return $wpdb->delete(rwlc_table('jobs'), ['id' => $id]);
    }

    public static function delete_customer($id) {
        global $wpdb;
        $id = absint($id);
        $wpdb->delete(rwlc_table('notes'), ['object_type' => 'customer', 'object_id' => $id]);
        return $wpdb->delete(rwlc_table('customers'), ['id' => $id]);
    }
}
