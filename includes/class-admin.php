<?php
/**
 * Admin Interface Class
 */

if (!defined('ABSPATH')) {
    exit;
}

class Insurance_CRM_SMTP_Admin {
    
    public static function display_settings_page() {
        // Handle form submission
        if (isset($_POST['submit']) && wp_verify_nonce($_POST['icsm_nonce'], 'icsm_save_settings')) {
            self::save_settings();
        }
        
        // Handle test connection
        if (isset($_POST['test_connection']) && wp_verify_nonce($_POST['icsm_nonce'], 'icsm_save_settings')) {
            $test_result = self::test_smtp_connection();
        }
        
        $providers = Insurance_CRM_SMTP_Mailer::get_email_providers();
        $current_provider = get_option('icsm_provider_preset', 'manual');
        $smtp_status = Insurance_CRM_SMTP_Mailer::get_smtp_status();
        
        include ICSM_PLUGIN_DIR . 'admin/settings-page.php';
    }
    
    public static function display_logs_page() {
        $page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
        $per_page = 20;
        $offset = ($page - 1) * $per_page;
        
        $logs = Insurance_CRM_SMTP_Database::get_logs($per_page, $offset);
        $total_logs = Insurance_CRM_SMTP_Database::get_log_count();
        $total_pages = ceil($total_logs / $per_page);
        $stats = Insurance_CRM_SMTP_Logger::get_log_stats();
        
        include ICSM_PLUGIN_DIR . 'admin/logs-page.php';
    }
    
    public static function display_test_page() {
        // Handle test email submission
        if (isset($_POST['send_test']) && wp_verify_nonce($_POST['icsm_test_nonce'], 'icsm_send_test')) {
            $test_result = self::send_test_email();
        }
        
        include ICSM_PLUGIN_DIR . 'admin/test-page.php';
    }
    
    private static function save_settings() {
        $settings = array(
            'host' => sanitize_text_field($_POST['icsm_smtp_host']),
            'port' => intval($_POST['icsm_smtp_port']),
            'security' => sanitize_text_field($_POST['icsm_smtp_security']),
            'username' => sanitize_text_field($_POST['icsm_smtp_username']),
            'password' => $_POST['icsm_smtp_password'],
            'from_email' => sanitize_email($_POST['icsm_from_email']),
            'from_name' => sanitize_text_field($_POST['icsm_from_name']),
            'provider_preset' => sanitize_text_field($_POST['icsm_provider_preset'])
        );
        
        // Validate settings
        $errors = Insurance_CRM_SMTP_Mailer::validate_settings($settings);
        
        if (!empty($errors)) {
            add_settings_error('icsm_settings', 'validation_error', implode('<br>', $errors), 'error');
            return false;
        }
        
        // Save settings
        update_option('icsm_smtp_host', $settings['host']);
        update_option('icsm_smtp_port', $settings['port']);
        update_option('icsm_smtp_security', $settings['security']);
        update_option('icsm_smtp_username', $settings['username']);
        update_option('icsm_from_email', $settings['from_email']);
        update_option('icsm_from_name', $settings['from_name']);
        update_option('icsm_provider_preset', $settings['provider_preset']);
        
        // Encrypt and save password
        $plugin = Insurance_CRM_SMTP::get_instance();
        $plugin->encrypt_and_save_password($settings['password']);
        
        // Update SMTP active status
        $active = isset($_POST['icsm_smtp_active']) ? true : false;
        update_option('icsm_smtp_active', $active);
        
        add_settings_error('icsm_settings', 'settings_saved', __('Settings saved successfully!', 'insurance-crm-smtp'), 'updated');
        
        Insurance_CRM_SMTP_Logger::log('info', 'SMTP settings updated by user: ' . wp_get_current_user()->user_login);
        
        return true;
    }
    
    private static function test_smtp_connection() {
        $host = sanitize_text_field($_POST['icsm_smtp_host']);
        $port = intval($_POST['icsm_smtp_port']);
        $security = sanitize_text_field($_POST['icsm_smtp_security']);
        $username = sanitize_text_field($_POST['icsm_smtp_username']);
        $password = $_POST['icsm_smtp_password'];
        
        $result = Insurance_CRM_SMTP_Mailer::test_connection($host, $port, $security, $username, $password);
        
        if ($result['success']) {
            add_settings_error('icsm_settings', 'connection_success', $result['message'], 'updated');
        } else {
            add_settings_error('icsm_settings', 'connection_error', $result['message'], 'error');
        }
        
        return $result;
    }
    
    private static function send_test_email() {
        $to_email = sanitize_email($_POST['test_email']);
        $subject = sanitize_text_field($_POST['test_subject']);
        $message = sanitize_textarea_field($_POST['test_message']);
        
        if (empty($to_email) || !is_email($to_email)) {
            add_settings_error('icsm_test', 'invalid_email', __('Please enter a valid email address', 'insurance-crm-smtp'), 'error');
            return false;
        }
        
        $result = Insurance_CRM_SMTP_Mailer::send_test_email($to_email, $subject, $message);
        
        if ($result) {
            add_settings_error('icsm_test', 'test_success', __('Test email sent successfully!', 'insurance-crm-smtp'), 'updated');
        } else {
            add_settings_error('icsm_test', 'test_error', __('Failed to send test email. Please check your SMTP settings and logs.', 'insurance-crm-smtp'), 'error');
        }
        
        return $result;
    }
    
    public static function enqueue_admin_scripts($hook) {
        // Only load on our plugin pages
        if (strpos($hook, 'insurance-crm-smtp') === false) {
            return;
        }
        
        wp_enqueue_script(
            'icsm-admin-js',
            ICSM_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery'),
            ICSM_VERSION,
            true
        );
        
        wp_enqueue_style(
            'icsm-admin-css',
            ICSM_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            ICSM_VERSION
        );
        
        // Localize script for AJAX
        wp_localize_script('icsm-admin-js', 'icsm_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('icsm_ajax_nonce'),
            'strings' => array(
                'testing_connection' => __('Testing connection...', 'insurance-crm-smtp'),
                'connection_success' => __('Connection successful!', 'insurance-crm-smtp'),
                'connection_failed' => __('Connection failed!', 'insurance-crm-smtp')
            )
        ));
    }
}