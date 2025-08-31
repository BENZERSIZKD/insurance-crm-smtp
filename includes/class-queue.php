<?php
/**
 * Email Queue Management Class
 */

if (!defined('ABSPATH')) {
    exit;
}

class Insurance_CRM_SMTP_Queue {
    
    private static $table_name = 'insurance_crm_smtp_queue';
    
    public static function init() {
        add_action('wp_loaded', array(__CLASS__, 'process_queue'));
        add_action('icsm_process_queue', array(__CLASS__, 'process_queue'));
        
        // Schedule queue processing if not already scheduled
        if (!wp_next_scheduled('icsm_process_queue')) {
            wp_schedule_event(time(), 'hourly', 'icsm_process_queue');
        }
    }
    
    public static function create_queue_table() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . self::$table_name;
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            to_email text NOT NULL,
            subject text NOT NULL,
            message longtext NOT NULL,
            headers text,
            attachments longtext,
            priority tinyint(4) DEFAULT 5,
            status varchar(20) DEFAULT 'pending',
            attempts int(11) DEFAULT 0,
            max_attempts int(11) DEFAULT 3,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            scheduled_at datetime DEFAULT CURRENT_TIMESTAMP,
            sent_at datetime NULL,
            error_message text,
            PRIMARY KEY (id),
            KEY status (status),
            KEY scheduled_at (scheduled_at),
            KEY priority (priority)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    public static function add_to_queue($to, $subject, $message, $headers = '', $attachments = array(), $priority = 5, $schedule_time = null) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . self::$table_name;
        
        if ($schedule_time === null) {
            $schedule_time = current_time('mysql');
        }
        
        $data = array(
            'to_email' => is_array($to) ? implode(',', $to) : $to,
            'subject' => $subject,
            'message' => $message,
            'headers' => is_array($headers) ? implode("\n", $headers) : $headers,
            'attachments' => json_encode($attachments),
            'priority' => $priority,
            'scheduled_at' => $schedule_time
        );
        
        return $wpdb->insert($table_name, $data);
    }
    
    public static function process_queue($limit = 10) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . self::$table_name;
        $current_time = current_time('mysql');
        
        // Get pending emails ready to be sent
        $emails = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_name 
             WHERE status = 'pending' 
             AND scheduled_at <= %s 
             AND attempts < max_attempts 
             ORDER BY priority ASC, scheduled_at ASC 
             LIMIT %d",
            $current_time,
            $limit
        ));
        
        foreach ($emails as $email) {
            self::send_queued_email($email);
        }
        
        // Clean up old failed emails (older than 7 days)
        $wpdb->query($wpdb->prepare(
            "DELETE FROM $table_name WHERE status = 'failed' AND created_at < %s",
            date('Y-m-d H:i:s', strtotime('-7 days'))
        ));
        
        // Clean up old sent emails (older than 30 days)
        $wpdb->query($wpdb->prepare(
            "DELETE FROM $table_name WHERE status = 'sent' AND sent_at < %s",
            date('Y-m-d H:i:s', strtotime('-30 days'))
        ));
    }
    
    private static function send_queued_email($email) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . self::$table_name;
        
        // Update attempt count
        $wpdb->update(
            $table_name,
            array('attempts' => $email->attempts + 1),
            array('id' => $email->id)
        );
        
        // Prepare email data
        $to = $email->to_email;
        $subject = $email->subject;
        $message = $email->message;
        $headers = !empty($email->headers) ? explode("\n", $email->headers) : array();
        $attachments = !empty($email->attachments) ? json_decode($email->attachments, true) : array();
        
        // Attempt to send
        $result = wp_mail($to, $subject, $message, $headers, $attachments);
        
        if ($result) {
            // Success
            $wpdb->update(
                $table_name,
                array(
                    'status' => 'sent',
                    'sent_at' => current_time('mysql'),
                    'error_message' => ''
                ),
                array('id' => $email->id)
            );
            
            Insurance_CRM_SMTP_Logger::log('info', sprintf('Queued email sent successfully (Queue ID: %d)', $email->id));
        } else {
            // Failed
            $error_message = 'Unknown error occurred';
            
            // Check if we've reached max attempts
            if (($email->attempts + 1) >= $email->max_attempts) {
                $status = 'failed';
            } else {
                $status = 'pending';
                // Schedule retry with exponential backoff
                $retry_delay = pow(2, $email->attempts) * 300; // Start with 5 minutes, double each time
                $retry_time = date('Y-m-d H:i:s', time() + $retry_delay);
                
                $wpdb->update(
                    $table_name,
                    array('scheduled_at' => $retry_time),
                    array('id' => $email->id)
                );
            }
            
            $wpdb->update(
                $table_name,
                array(
                    'status' => $status,
                    'error_message' => $error_message
                ),
                array('id' => $email->id)
            );
            
            Insurance_CRM_SMTP_Logger::log('error', sprintf('Queued email failed (Queue ID: %d, Attempt: %d): %s', $email->id, $email->attempts + 1, $error_message));
        }
    }
    
    public static function get_queue_stats() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . self::$table_name;
        
        $stats = $wpdb->get_row(
            "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'sent' THEN 1 ELSE 0 END) as sent,
                SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed
             FROM $table_name",
            ARRAY_A
        );
        
        return $stats ?: array('total' => 0, 'pending' => 0, 'sent' => 0, 'failed' => 0);
    }
    
    public static function clear_queue($status = null) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . self::$table_name;
        
        if ($status) {
            $wpdb->delete($table_name, array('status' => $status));
        } else {
            $wpdb->query("TRUNCATE TABLE $table_name");
        }
        
        Insurance_CRM_SMTP_Logger::log('info', 'Email queue cleared' . ($status ? ' for status: ' . $status : ''));
    }
    
    public static function retry_failed_emails() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . self::$table_name;
        
        $result = $wpdb->update(
            $table_name,
            array(
                'status' => 'pending',
                'attempts' => 0,
                'scheduled_at' => current_time('mysql'),
                'error_message' => ''
            ),
            array('status' => 'failed')
        );
        
        Insurance_CRM_SMTP_Logger::log('info', sprintf('Retrying %d failed emails', $result));
        
        return $result;
    }
}