<?php
/**
 * Main Plugin Class
 */

if (!defined('ABSPATH')) {
    exit;
}

class Insurance_CRM_SMTP {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function __construct() {
        // Constructor
    }
    
    public function init() {
        // Initialize plugin hooks
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'admin_init'));
        add_action('init', array($this, 'load_textdomain'));
        
        // Override wp_mail if SMTP is configured and active
        if ($this->is_smtp_configured() && $this->is_smtp_active()) {
            add_action('phpmailer_init', array($this, 'configure_phpmailer'));
        }
        
        // Check if setup wizard should run
        if (get_option('icsm_show_setup_wizard', true)) {
            add_action('admin_notices', array($this, 'setup_wizard_notice'));
        }
    }
    
    public function add_admin_menu() {
        add_menu_page(
            __('Insurance CRM SMTP', 'insurance-crm-smtp'),
            __('InsuranceCRM SMTP', 'insurance-crm-smtp'),
            'manage_options',
            'insurance-crm-smtp',
            array('Insurance_CRM_SMTP_Admin', 'display_settings_page'),
            'dashicons-email-alt',
            30
        );
        
        add_submenu_page(
            'insurance-crm-smtp',
            __('Settings', 'insurance-crm-smtp'),
            __('Settings', 'insurance-crm-smtp'),
            'manage_options',
            'insurance-crm-smtp',
            array('Insurance_CRM_SMTP_Admin', 'display_settings_page')
        );
        
        add_submenu_page(
            'insurance-crm-smtp',
            __('Logs', 'insurance-crm-smtp'),
            __('Logs', 'insurance-crm-smtp'),
            'manage_options',
            'insurance-crm-smtp-logs',
            array('Insurance_CRM_SMTP_Admin', 'display_logs_page')
        );
        
        add_submenu_page(
            'insurance-crm-smtp',
            __('Test Email', 'insurance-crm-smtp'),
            __('Test Email', 'insurance-crm-smtp'),
            'manage_options',
            'insurance-crm-smtp-test',
            array('Insurance_CRM_SMTP_Admin', 'display_test_page')
        );
        
        add_submenu_page(
            'insurance-crm-smtp',
            __('Email Queue', 'insurance-crm-smtp'),
            __('Queue', 'insurance-crm-smtp'),
            'manage_options',
            'insurance-crm-smtp-queue',
            array('Insurance_CRM_SMTP_Admin', 'display_queue_page')
        );
    }
    
    public function admin_init() {
        // Register settings
        register_setting('icsm_settings', 'icsm_smtp_host');
        register_setting('icsm_settings', 'icsm_smtp_port');
        register_setting('icsm_settings', 'icsm_smtp_security');
        register_setting('icsm_settings', 'icsm_smtp_username');
        register_setting('icsm_settings', 'icsm_smtp_password');
        register_setting('icsm_settings', 'icsm_from_email');
        register_setting('icsm_settings', 'icsm_from_name');
        register_setting('icsm_settings', 'icsm_smtp_active');
        register_setting('icsm_settings', 'icsm_provider_preset');
    }
    
    public function load_textdomain() {
        load_plugin_textdomain('insurance-crm-smtp', false, dirname(plugin_basename(__FILE__)) . '/languages/');
    }
    
    public function configure_phpmailer($phpmailer) {
        // Check rate limiting first
        if (Insurance_CRM_SMTP_Rate_Limiter::is_rate_limited()) {
            Insurance_CRM_SMTP_Logger::log('warning', 'Email sending blocked due to rate limiting');
            return; // Don't configure SMTP, will fall back to default
        }
        
        $smtp_host = get_option('icsm_smtp_host');
        $smtp_port = get_option('icsm_smtp_port', 587);
        $smtp_security = get_option('icsm_smtp_security', 'tls');
        $smtp_username = get_option('icsm_smtp_username');
        $smtp_password = $this->decrypt_password(get_option('icsm_smtp_password'));
        
        $phpmailer->isSMTP();
        $phpmailer->Host = $smtp_host;
        $phpmailer->Port = $smtp_port;
        $phpmailer->SMTPAuth = !empty($smtp_username);
        $phpmailer->Username = $smtp_username;
        $phpmailer->Password = $smtp_password;
        
        // Set security protocol
        if ($smtp_security === 'ssl') {
            $phpmailer->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
        } elseif ($smtp_security === 'tls') {
            $phpmailer->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        }
        
        // Set from email and name
        $from_email = get_option('icsm_from_email');
        $from_name = get_option('icsm_from_name');
        
        if (!empty($from_email)) {
            $phpmailer->setFrom($from_email, $from_name);
        }
        
        // Enable debug if in development
        if (WP_DEBUG) {
            $phpmailer->SMTPDebug = 2;
        }
        
        // Log the email attempt
        $log_id = Insurance_CRM_SMTP_Logger::log_email_attempt($phpmailer);
        
        // Add custom action for successful send
        add_action('wp_mail_succeeded', function($mail_data) use ($log_id) {
            Insurance_CRM_SMTP_Logger::log_email_success($log_id);
            Insurance_CRM_SMTP_Rate_Limiter::record_email_sent();
        });
        
        // Add custom action for failed send
        add_action('wp_mail_failed', function($error) use ($log_id) {
            $error_message = is_wp_error($error) ? $error->get_error_message() : 'Unknown error';
            Insurance_CRM_SMTP_Logger::log_email_failure($log_id, $error_message);
        });
    }
    
    public function setup_wizard_notice() {
        if (current_user_can('manage_options')) {
            echo '<div class="notice notice-info is-dismissible">';
            echo '<p>' . sprintf(
                __('Welcome to Insurance CRM SMTP! Please <a href="%s">run the setup wizard</a> to configure your email settings.', 'insurance-crm-smtp'),
                admin_url('admin.php?page=insurance-crm-smtp-wizard')
            ) . '</p>';
            echo '</div>';
        }
    }
    
    private function is_smtp_configured() {
        $host = get_option('icsm_smtp_host');
        $username = get_option('icsm_smtp_username');
        return !empty($host) && !empty($username);
    }
    
    private function is_smtp_active() {
        return get_option('icsm_smtp_active', false);
    }
    
    private function encrypt_password($password) {
        if (empty($password)) {
            return '';
        }
        
        $key = wp_salt('secure_auth');
        $encrypted = openssl_encrypt($password, 'AES-256-CBC', $key, 0, substr($key, 0, 16));
        return base64_encode($encrypted);
    }
    
    private function decrypt_password($encrypted_password) {
        if (empty($encrypted_password)) {
            return '';
        }
        
        $key = wp_salt('secure_auth');
        $encrypted = base64_decode($encrypted_password);
        return openssl_decrypt($encrypted, 'AES-256-CBC', $key, 0, substr($key, 0, 16));
    }
    
    public function encrypt_and_save_password($password) {
        $encrypted = $this->encrypt_password($password);
        update_option('icsm_smtp_password', $encrypted);
    }
    
    public static function deactivate() {
        // Clean up on deactivation
        update_option('icsm_smtp_active', false);
    }
    
    public static function uninstall() {
        // Clean up on uninstall
        global $wpdb;
        
        // Delete options
        delete_option('icsm_smtp_host');
        delete_option('icsm_smtp_port');
        delete_option('icsm_smtp_security');
        delete_option('icsm_smtp_username');
        delete_option('icsm_smtp_password');
        delete_option('icsm_from_email');
        delete_option('icsm_from_name');
        delete_option('icsm_smtp_active');
        delete_option('icsm_provider_preset');
        delete_option('icsm_show_setup_wizard');
        delete_option('icsm_db_version');
        delete_option('icsm_rate_limit_enabled');
        delete_option('icsm_rate_limit_emails');
        delete_option('icsm_rate_limit_window');
        
        // Drop tables
        $logs_table = $wpdb->prefix . 'insurance_crm_smtp_logs';
        $queue_table = $wpdb->prefix . 'insurance_crm_smtp_queue';
        $wpdb->query("DROP TABLE IF EXISTS {$logs_table}");
        $wpdb->query("DROP TABLE IF EXISTS {$queue_table}");
        
        // Clear scheduled events
        wp_clear_scheduled_hook('icsm_process_queue');
        
        // Clear rate limit transients
        Insurance_CRM_SMTP_Rate_Limiter::clear_rate_limits();
    }
}