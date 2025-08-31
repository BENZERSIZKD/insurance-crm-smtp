<?php
/**
 * Setup Wizard Class
 */

if (!defined('ABSPATH')) {
    exit;
}

class Insurance_CRM_SMTP_Setup_Wizard {
    
    public static function init() {
        add_action('admin_post_icsm_wizard_step', array(__CLASS__, 'handle_wizard_step'));
        add_action('admin_post_icsm_wizard_complete', array(__CLASS__, 'handle_wizard_complete'));
    }
    
    public static function display_wizard() {
        // Use single-page wizard instead of multi-step
        include ICSM_PLUGIN_DIR . 'admin/setup-wizard-single.php';
    }
    
    public static function handle_wizard_complete() {
        if (!wp_verify_nonce($_POST['wizard_nonce'], 'icsm_wizard_complete')) {
            wp_die(__('Security check failed', 'insurance-crm-smtp'));
        }
        
        // Validate and save all settings at once
        $provider = isset($_POST['provider']) && !empty($_POST['provider']) 
            ? sanitize_text_field($_POST['provider']) 
            : 'manual';
        
        $smtp_settings = array(
            'host' => sanitize_text_field($_POST['smtp_host']),
            'port' => intval($_POST['smtp_port']),
            'security' => sanitize_text_field($_POST['smtp_security']),
            'username' => sanitize_text_field($_POST['smtp_username']),
            'password' => $_POST['smtp_password'],
            'from_email' => sanitize_email($_POST['from_email']),
            'from_name' => sanitize_text_field($_POST['from_name'])
        );
        
        // Validate settings
        $errors = Insurance_CRM_SMTP_Mailer::validate_settings($smtp_settings);
        
        if (!empty($errors)) {
            $redirect_url = admin_url('admin.php?page=insurance-crm-smtp-wizard&error=' . urlencode(implode(', ', $errors)));
        } else {
            // Save provider preset
            update_option('icsm_provider_preset', $provider);
            
            // Save SMTP settings
            update_option('icsm_smtp_host', $smtp_settings['host']);
            update_option('icsm_smtp_port', $smtp_settings['port']);
            update_option('icsm_smtp_security', $smtp_settings['security']);
            update_option('icsm_smtp_username', $smtp_settings['username']);
            update_option('icsm_from_email', $smtp_settings['from_email']);
            update_option('icsm_from_name', $smtp_settings['from_name']);
            
            // Encrypt and save password
            $plugin = Insurance_CRM_SMTP::get_instance();
            $plugin->encrypt_and_save_password($smtp_settings['password']);
            
            // Activate SMTP
            update_option('icsm_smtp_active', true);
            
            // Send test email if provided
            $test_email = sanitize_email($_POST['test_email']);
            if (!empty($test_email) && is_email($test_email)) {
                Insurance_CRM_SMTP_Mailer::send_test_email($test_email);
            }
            
            $redirect_url = admin_url('admin.php?page=insurance-crm-smtp-wizard&success=1');
        }
        
        wp_redirect($redirect_url);
        exit;
    }
    
    public static function handle_wizard_step() {
        if (!wp_verify_nonce($_POST['wizard_nonce'], 'icsm_wizard_step')) {
            wp_die(__('Security check failed', 'insurance-crm-smtp'));
        }
        
        $step = intval($_POST['step']);
        $redirect_url = admin_url('admin.php?page=insurance-crm-smtp-wizard&step=' . ($step + 1));
        
        switch ($step) {
            case 1:
                // Step 1: Welcome - no processing needed
                break;
                
            case 2:
                // Step 2: Provider selection - with fallback to manual
                $provider = isset($_POST['provider']) && !empty($_POST['provider']) 
                    ? sanitize_text_field($_POST['provider']) 
                    : 'manual'; // Default to manual if nothing selected
                
                update_option('icsm_provider_preset', $provider);
                
                if ($provider !== 'manual') {
                    $provider_settings = Insurance_CRM_SMTP_Mailer::get_provider_settings($provider);
                    if ($provider_settings) {
                        update_option('icsm_smtp_host', $provider_settings['host']);
                        update_option('icsm_smtp_port', $provider_settings['port']);
                        update_option('icsm_smtp_security', $provider_settings['security']);
                    }
                }
                break;
                
            case 3:
                // Step 3: SMTP Configuration
                $smtp_settings = array(
                    'host' => sanitize_text_field($_POST['smtp_host']),
                    'port' => intval($_POST['smtp_port']),
                    'security' => sanitize_text_field($_POST['smtp_security']),
                    'username' => sanitize_text_field($_POST['smtp_username']),
                    'password' => $_POST['smtp_password'],
                    'from_email' => sanitize_email($_POST['from_email']),
                    'from_name' => sanitize_text_field($_POST['from_name'])
                );
                
                // Validate settings
                $errors = Insurance_CRM_SMTP_Mailer::validate_settings($smtp_settings);
                
                if (!empty($errors)) {
                    $redirect_url = admin_url('admin.php?page=insurance-crm-smtp-wizard&step=3&error=' . urlencode(implode(', ', $errors)));
                } else {
                    // Save settings
                    update_option('icsm_smtp_host', $smtp_settings['host']);
                    update_option('icsm_smtp_port', $smtp_settings['port']);
                    update_option('icsm_smtp_security', $smtp_settings['security']);
                    update_option('icsm_smtp_username', $smtp_settings['username']);
                    update_option('icsm_from_email', $smtp_settings['from_email']);
                    update_option('icsm_from_name', $smtp_settings['from_name']);
                    
                    // Encrypt and save password
                    $plugin = Insurance_CRM_SMTP::get_instance();
                    $plugin->encrypt_and_save_password($smtp_settings['password']);
                }
                break;
                
            case 4:
                // Step 4: Test and Complete
                $test_email = sanitize_email($_POST['test_email']);
                
                if (!empty($test_email) && is_email($test_email)) {
                    Insurance_CRM_SMTP_Mailer::send_test_email($test_email);
                }
                
                // Activate SMTP
                update_option('icsm_smtp_active', true);
                
                $redirect_url = admin_url('admin.php?page=insurance-crm-smtp&wizard_complete=1');
                break;
        }
        
        wp_redirect($redirect_url);
        exit;
    }
    
    public static function get_wizard_progress($current_step) {
        $steps = array(
            1 => __('Welcome', 'insurance-crm-smtp'),
            2 => __('Provider', 'insurance-crm-smtp'),
            3 => __('Configuration', 'insurance-crm-smtp'),
            4 => __('Test & Complete', 'insurance-crm-smtp')
        );
        
        return array(
            'current' => $current_step,
            'total' => count($steps),
            'steps' => $steps
        );
    }
}