<?php
/**
 * Logger Class
 */

if (!defined('ABSPATH')) {
    exit;
}

class Insurance_CRM_SMTP_Logger {
    
    public static function log($level, $message, $context = array()) {
        if (!get_option('icsm_enable_logging', true)) {
            return;
        }
        
        $log_entry = array(
            'timestamp' => current_time('mysql'),
            'level' => $level,
            'message' => $message,
            'context' => json_encode($context),
            'ip_address' => self::get_client_ip(),
            'user_id' => get_current_user_id()
        );
        
        // Store in WordPress logs if WP_DEBUG_LOG is enabled
        if (WP_DEBUG_LOG) {
            error_log(sprintf('[Insurance CRM SMTP] [%s] %s', strtoupper($level), $message));
        }
        
        // Store in plugin's custom log if enabled
        self::store_custom_log($log_entry);
    }
    
    public static function log_email_attempt($phpmailer) {
        $to_emails = array();
        
        // Extract TO addresses
        foreach ($phpmailer->getToAddresses() as $to) {
            $to_emails[] = $to[0];
        }
        
        $log_data = array(
            'to_email' => implode(', ', $to_emails),
            'from_email' => $phpmailer->From,
            'subject' => $phpmailer->Subject,
            'message' => substr($phpmailer->Body, 0, 1000), // Limit message length
            'status' => 'pending',
            'smtp_host' => $phpmailer->Host,
            'smtp_port' => $phpmailer->Port,
            'error_message' => '',
            'retry_count' => 0,
            'sent_at' => null
        );
        
        return Insurance_CRM_SMTP_Database::insert_log($log_data);
    }
    
    public static function log_email_success($log_id) {
        Insurance_CRM_SMTP_Database::update_log($log_id, array(
            'status' => 'sent',
            'sent_at' => current_time('mysql')
        ));
        
        self::log('info', sprintf(__('Email sent successfully (Log ID: %d)', 'insurance-crm-smtp'), $log_id));
    }
    
    public static function log_email_failure($log_id, $error_message) {
        Insurance_CRM_SMTP_Database::update_log($log_id, array(
            'status' => 'failed',
            'error_message' => $error_message
        ));
        
        self::log('error', sprintf(__('Email failed to send (Log ID: %d): %s', 'insurance-crm-smtp'), $log_id, $error_message));
    }
    
    public static function increment_retry_count($log_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'insurance_crm_smtp_logs';
        
        $wpdb->query($wpdb->prepare(
            "UPDATE $table_name SET retry_count = retry_count + 1 WHERE id = %d",
            $log_id
        ));
    }
    
    private static function store_custom_log($log_entry) {
        $log_file = WP_CONTENT_DIR . '/insurance-crm-smtp.log';
        
        $log_line = sprintf(
            "[%s] [%s] %s %s\n",
            $log_entry['timestamp'],
            strtoupper($log_entry['level']),
            $log_entry['message'],
            !empty($log_entry['context']) ? '- Context: ' . $log_entry['context'] : ''
        );
        
        error_log($log_line, 3, $log_file);
    }
    
    private static function get_client_ip() {
        $ip_keys = array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR');
        
        foreach ($ip_keys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP, 
                        FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
        
        return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
    }
    
    public static function get_recent_logs($limit = 10) {
        return Insurance_CRM_SMTP_Database::get_logs($limit);
    }
    
    public static function clean_old_logs() {
        $retention_days = get_option('icsm_log_retention_days', 30);
        Insurance_CRM_SMTP_Database::delete_old_logs($retention_days);
        
        self::log('info', sprintf(__('Cleaned logs older than %d days', 'insurance-crm-smtp'), $retention_days));
    }
    
    public static function get_log_stats() {
        return array(
            'total' => Insurance_CRM_SMTP_Database::get_log_count(),
            'sent' => Insurance_CRM_SMTP_Database::get_log_count('sent'),
            'failed' => Insurance_CRM_SMTP_Database::get_log_count('failed'),
            'pending' => Insurance_CRM_SMTP_Database::get_log_count('pending')
        );
    }
}