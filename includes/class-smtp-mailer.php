<?php
/**
 * SMTP Mailer Class
 */

if (!defined('ABSPATH')) {
    exit;
}

class Insurance_CRM_SMTP_Mailer {
    
    private static $email_providers = array(
        'gmail' => array(
            'name' => 'Gmail',
            'host' => 'smtp.gmail.com',
            'port' => 587,
            'security' => 'tls',
            'auth' => true
        ),
        'outlook' => array(
            'name' => 'Outlook/Hotmail',
            'host' => 'smtp.live.com',
            'port' => 587,
            'security' => 'starttls',
            'auth' => true
        ),
        'yahoo' => array(
            'name' => 'Yahoo',
            'host' => 'smtp.mail.yahoo.com',
            'port' => 587,
            'security' => 'tls',
            'auth' => true
        ),
        'yandex' => array(
            'name' => 'Yandex',
            'host' => 'smtp.yandex.com',
            'port' => 465,
            'security' => 'ssl',
            'auth' => true
        )
    );
    
    public static function get_email_providers() {
        return self::$email_providers;
    }
    
    public static function get_provider_settings($provider) {
        return isset(self::$email_providers[$provider]) ? self::$email_providers[$provider] : null;
    }
    
    public static function test_connection($host, $port, $security, $username, $password) {
        require_once(ABSPATH . WPINC . '/PHPMailer/PHPMailer.php');
        require_once(ABSPATH . WPINC . '/PHPMailer/SMTP.php');
        require_once(ABSPATH . WPINC . '/PHPMailer/Exception.php');
        
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        
        try {
            $mail->isSMTP();
            $mail->Host = $host;
            $mail->Port = $port;
            $mail->SMTPAuth = !empty($username);
            $mail->Username = $username;
            $mail->Password = $password;
            
            if ($security === 'ssl') {
                $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
            } elseif ($security === 'tls' || $security === 'starttls') {
                $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            }
            
            // Test connection
            $mail->SMTPDebug = 0; // Disable debug output
            $result = $mail->smtpConnect();
            $mail->smtpClose();
            
            return array(
                'success' => $result,
                'message' => $result ? __('Connection successful!', 'insurance-crm-smtp') : __('Connection failed!', 'insurance-crm-smtp')
            );
            
        } catch (Exception $e) {
            return array(
                'success' => false,
                'message' => $e->getMessage()
            );
        }
    }
    
    public static function send_test_email($to_email, $subject = null, $message = null) {
        if (empty($subject)) {
            $subject = __('Test Email from Insurance CRM SMTP', 'insurance-crm-smtp');
        }
        
        if (empty($message)) {
            $message = __('This is a test email sent from your WordPress site using Insurance CRM SMTP plugin.', 'insurance-crm-smtp');
            $message .= "\n\n" . sprintf(__('Sent at: %s', 'insurance-crm-smtp'), current_time('mysql'));
            $message .= "\n" . sprintf(__('Site: %s', 'insurance-crm-smtp'), get_site_url());
        }
        
        $result = wp_mail($to_email, $subject, $message);
        
        if ($result) {
            Insurance_CRM_SMTP_Logger::log('info', sprintf(__('Test email sent successfully to %s', 'insurance-crm-smtp'), $to_email));
        } else {
            Insurance_CRM_SMTP_Logger::log('error', sprintf(__('Failed to send test email to %s', 'insurance-crm-smtp'), $to_email));
        }
        
        return $result;
    }
    
    public static function get_smtp_status() {
        $host = get_option('icsm_smtp_host');
        $username = get_option('icsm_smtp_username');
        $active = get_option('icsm_smtp_active', false);
        
        if (!$active) {
            return array(
                'status' => 'inactive',
                'message' => __('SMTP is disabled', 'insurance-crm-smtp'),
                'color' => 'red'
            );
        }
        
        if (empty($host) || empty($username)) {
            return array(
                'status' => 'not_configured',
                'message' => __('SMTP not configured', 'insurance-crm-smtp'),
                'color' => 'orange'
            );
        }
        
        return array(
            'status' => 'active',
            'message' => __('SMTP is active and configured', 'insurance-crm-smtp'),
            'color' => 'green'
        );
    }
    
    public static function validate_settings($settings) {
        $errors = array();
        
        if (empty($settings['host'])) {
            $errors[] = __('SMTP Host is required', 'insurance-crm-smtp');
        }
        
        if (empty($settings['port']) || !is_numeric($settings['port'])) {
            $errors[] = __('Valid SMTP Port is required', 'insurance-crm-smtp');
        }
        
        if (empty($settings['username'])) {
            $errors[] = __('SMTP Username is required', 'insurance-crm-smtp');
        }
        
        if (empty($settings['password'])) {
            $errors[] = __('SMTP Password is required', 'insurance-crm-smtp');
        }
        
        if (!empty($settings['from_email']) && !is_email($settings['from_email'])) {
            $errors[] = __('From Email must be a valid email address', 'insurance-crm-smtp');
        }
        
        return $errors;
    }
}