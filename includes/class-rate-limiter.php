<?php
/**
 * Rate Limiting Class
 */

if (!defined('ABSPATH')) {
    exit;
}

class Insurance_CRM_SMTP_Rate_Limiter {
    
    private static $transient_prefix = 'icsm_rate_limit_';
    
    public static function is_rate_limited($identifier = null) {
        if (!$identifier) {
            $identifier = self::get_client_identifier();
        }
        
        $rate_limit_settings = self::get_rate_limit_settings();
        
        if (!$rate_limit_settings['enabled']) {
            return false;
        }
        
        $transient_key = self::$transient_prefix . md5($identifier);
        $current_count = get_transient($transient_key);
        
        if ($current_count === false) {
            // First request in this time window
            set_transient($transient_key, 1, $rate_limit_settings['window']);
            return false;
        }
        
        if ($current_count >= $rate_limit_settings['limit']) {
            Insurance_CRM_SMTP_Logger::log('warning', sprintf('Rate limit exceeded for %s: %d emails in %d seconds', $identifier, $current_count, $rate_limit_settings['window']));
            return true;
        }
        
        // Increment counter
        set_transient($transient_key, $current_count + 1, $rate_limit_settings['window']);
        return false;
    }
    
    public static function record_email_sent($identifier = null) {
        if (!$identifier) {
            $identifier = self::get_client_identifier();
        }
        
        $rate_limit_settings = self::get_rate_limit_settings();
        
        if (!$rate_limit_settings['enabled']) {
            return;
        }
        
        $transient_key = self::$transient_prefix . md5($identifier);
        $current_count = get_transient($transient_key);
        
        if ($current_count === false) {
            set_transient($transient_key, 1, $rate_limit_settings['window']);
        } else {
            set_transient($transient_key, $current_count + 1, $rate_limit_settings['window']);
        }
    }
    
    public static function get_remaining_limit($identifier = null) {
        if (!$identifier) {
            $identifier = self::get_client_identifier();
        }
        
        $rate_limit_settings = self::get_rate_limit_settings();
        
        if (!$rate_limit_settings['enabled']) {
            return -1; // Unlimited
        }
        
        $transient_key = self::$transient_prefix . md5($identifier);
        $current_count = get_transient($transient_key);
        
        if ($current_count === false) {
            return $rate_limit_settings['limit'];
        }
        
        return max(0, $rate_limit_settings['limit'] - $current_count);
    }
    
    public static function get_reset_time($identifier = null) {
        if (!$identifier) {
            $identifier = self::get_client_identifier();
        }
        
        $transient_key = self::$transient_prefix . md5($identifier);
        $timeout = get_option('_transient_timeout_' . $transient_key);
        
        return $timeout ? $timeout : time();
    }
    
    private static function get_client_identifier() {
        // Combine IP address and user ID for identification
        $ip = self::get_client_ip();
        $user_id = get_current_user_id();
        
        return $ip . '_' . $user_id;
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
        
        return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1';
    }
    
    private static function get_rate_limit_settings() {
        $defaults = array(
            'enabled' => get_option('icsm_rate_limit_enabled', true),
            'limit' => get_option('icsm_rate_limit_emails', 50), // 50 emails
            'window' => get_option('icsm_rate_limit_window', 3600) // per hour (3600 seconds)
        );
        
        return apply_filters('icsm_rate_limit_settings', $defaults);
    }
    
    public static function clear_rate_limits($identifier = null) {
        global $wpdb;
        
        if ($identifier) {
            $transient_key = self::$transient_prefix . md5($identifier);
            delete_transient($transient_key);
        } else {
            // Clear all rate limit transients
            $wpdb->query(
                $wpdb->prepare(
                    "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
                    '_transient_' . self::$transient_prefix . '%',
                    '_transient_timeout_' . self::$transient_prefix . '%'
                )
            );
        }
        
        Insurance_CRM_SMTP_Logger::log('info', 'Rate limits cleared' . ($identifier ? ' for: ' . $identifier : ''));
    }
    
    public static function get_rate_limit_status() {
        $settings = self::get_rate_limit_settings();
        $identifier = self::get_client_identifier();
        $remaining = self::get_remaining_limit($identifier);
        $reset_time = self::get_reset_time($identifier);
        
        return array(
            'enabled' => $settings['enabled'],
            'limit' => $settings['limit'],
            'window' => $settings['window'],
            'remaining' => $remaining,
            'reset_time' => $reset_time,
            'reset_in' => max(0, $reset_time - time()),
            'is_limited' => self::is_rate_limited($identifier)
        );
    }
    
    public static function format_time_remaining($seconds) {
        if ($seconds < 60) {
            return sprintf(_n('%d second', '%d seconds', $seconds, 'insurance-crm-smtp'), $seconds);
        } elseif ($seconds < 3600) {
            $minutes = ceil($seconds / 60);
            return sprintf(_n('%d minute', '%d minutes', $minutes, 'insurance-crm-smtp'), $minutes);
        } else {
            $hours = ceil($seconds / 3600);
            return sprintf(_n('%d hour', '%d hours', $hours, 'insurance-crm-smtp'), $hours);
        }
    }
    
    public static function handle_rate_limited_request() {
        $status = self::get_rate_limit_status();
        
        if ($status['is_limited']) {
            $message = sprintf(
                __('Rate limit exceeded. You can send %d more emails in %s.', 'insurance-crm-smtp'),
                $status['remaining'],
                self::format_time_remaining($status['reset_in'])
            );
            
            wp_die($message, __('Rate Limit Exceeded', 'insurance-crm-smtp'), array('response' => 429));
        }
    }
}