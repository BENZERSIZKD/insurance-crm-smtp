<?php
/**
 * Database Management Class
 */

if (!defined('ABSPATH')) {
    exit;
}

class Insurance_CRM_SMTP_Database {
    
    public static function create_tables() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'insurance_crm_smtp_logs';
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            timestamp datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            to_email text NOT NULL,
            from_email varchar(255) NOT NULL,
            subject text NOT NULL,
            message longtext NOT NULL,
            status varchar(20) NOT NULL,
            smtp_host varchar(255),
            smtp_port int(11),
            error_message text,
            retry_count int(11) DEFAULT 0,
            sent_at datetime NULL,
            PRIMARY KEY (id),
            KEY status (status),
            KEY timestamp (timestamp),
            KEY to_email (to_email(100))
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        
        // Update database version
        update_option('icsm_db_version', ICSM_DB_VERSION);
    }
    
    public static function get_logs($limit = 50, $offset = 0, $status = '') {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'insurance_crm_smtp_logs';
        
        $where = '';
        if (!empty($status)) {
            $where = $wpdb->prepare(" WHERE status = %s", $status);
        }
        
        $sql = $wpdb->prepare(
            "SELECT * FROM $table_name $where ORDER BY timestamp DESC LIMIT %d OFFSET %d",
            $limit,
            $offset
        );
        
        return $wpdb->get_results($sql);
    }
    
    public static function get_log_count($status = '') {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'insurance_crm_smtp_logs';
        
        if (!empty($status)) {
            return $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM $table_name WHERE status = %s",
                $status
            ));
        }
        
        return $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
    }
    
    public static function insert_log($data) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'insurance_crm_smtp_logs';
        
        $wpdb->insert(
            $table_name,
            array(
                'to_email' => $data['to_email'],
                'from_email' => $data['from_email'],
                'subject' => $data['subject'],
                'message' => $data['message'],
                'status' => $data['status'],
                'smtp_host' => $data['smtp_host'],
                'smtp_port' => $data['smtp_port'],
                'error_message' => $data['error_message'],
                'retry_count' => $data['retry_count'],
                'sent_at' => $data['sent_at']
            ),
            array('%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%d', '%s')
        );
        
        return $wpdb->insert_id;
    }
    
    public static function update_log($id, $data) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'insurance_crm_smtp_logs';
        
        return $wpdb->update(
            $table_name,
            $data,
            array('id' => $id),
            null,
            array('%d')
        );
    }
    
    public static function delete_old_logs($days = 30) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'insurance_crm_smtp_logs';
        
        $wpdb->query($wpdb->prepare(
            "DELETE FROM $table_name WHERE timestamp < DATE_SUB(NOW(), INTERVAL %d DAY)",
            $days
        ));
    }
}