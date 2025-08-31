<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap">
    <h1><?php _e('Send Test Email', 'insurance-crm-smtp'); ?></h1>
    
    <?php settings_errors('icsm_test'); ?>
    
    <div class="notice notice-info">
        <p><?php _e('Use this form to send a test email to verify your SMTP configuration is working correctly.', 'insurance-crm-smtp'); ?></p>
    </div>
    
    <form method="post" action="">
        <?php wp_nonce_field('icsm_send_test', 'icsm_test_nonce'); ?>
        
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="test_email"><?php _e('To Email Address', 'insurance-crm-smtp'); ?></label>
                </th>
                <td>
                    <input type="email" id="test_email" name="test_email" 
                           value="<?php echo esc_attr(get_option('admin_email')); ?>" class="regular-text" required />
                    <p class="description"><?php _e('Email address to send test email to.', 'insurance-crm-smtp'); ?></p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="test_subject"><?php _e('Subject', 'insurance-crm-smtp'); ?></label>
                </th>
                <td>
                    <input type="text" id="test_subject" name="test_subject" 
                           value="<?php _e('Test Email from Insurance CRM SMTP', 'insurance-crm-smtp'); ?>" class="regular-text" />
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="test_message"><?php _e('Message', 'insurance-crm-smtp'); ?></label>
                </th>
                <td>
                    <textarea id="test_message" name="test_message" rows="5" class="large-text"><?php 
                        echo esc_textarea(sprintf(
                            __("This is a test email sent from your WordPress site using Insurance CRM SMTP plugin.\n\nSent at: %s\nSite: %s", 'insurance-crm-smtp'),
                            current_time('mysql'),
                            get_site_url()
                        )); 
                    ?></textarea>
                </td>
            </tr>
        </table>
        
        <?php submit_button(__('Send Test Email', 'insurance-crm-smtp'), 'primary', 'send_test'); ?>
    </form>
    
    <div class="icsm-test-info">
        <h3><?php _e('Current SMTP Configuration', 'insurance-crm-smtp'); ?></h3>
        <table class="widefat">
            <tr>
                <td><strong><?php _e('SMTP Host:', 'insurance-crm-smtp'); ?></strong></td>
                <td><?php echo esc_html(get_option('icsm_smtp_host', __('Not configured', 'insurance-crm-smtp'))); ?></td>
            </tr>
            <tr>
                <td><strong><?php _e('SMTP Port:', 'insurance-crm-smtp'); ?></strong></td>
                <td><?php echo esc_html(get_option('icsm_smtp_port', __('Not configured', 'insurance-crm-smtp'))); ?></td>
            </tr>
            <tr>
                <td><strong><?php _e('Security:', 'insurance-crm-smtp'); ?></strong></td>
                <td><?php echo esc_html(strtoupper(get_option('icsm_smtp_security', __('Not configured', 'insurance-crm-smtp')))); ?></td>
            </tr>
            <tr>
                <td><strong><?php _e('Username:', 'insurance-crm-smtp'); ?></strong></td>
                <td><?php echo esc_html(get_option('icsm_smtp_username', __('Not configured', 'insurance-crm-smtp'))); ?></td>
            </tr>
            <tr>
                <td><strong><?php _e('From Email:', 'insurance-crm-smtp'); ?></strong></td>
                <td><?php echo esc_html(get_option('icsm_from_email', __('Not configured', 'insurance-crm-smtp'))); ?></td>
            </tr>
            <tr>
                <td><strong><?php _e('From Name:', 'insurance-crm-smtp'); ?></strong></td>
                <td><?php echo esc_html(get_option('icsm_from_name', __('Not configured', 'insurance-crm-smtp'))); ?></td>
            </tr>
            <tr>
                <td><strong><?php _e('SMTP Active:', 'insurance-crm-smtp'); ?></strong></td>
                <td>
                    <span class="status-badge status-<?php echo get_option('icsm_smtp_active') ? 'active' : 'inactive'; ?>">
                        <?php echo get_option('icsm_smtp_active') ? __('Active', 'insurance-crm-smtp') : __('Inactive', 'insurance-crm-smtp'); ?>
                    </span>
                </td>
            </tr>
        </table>
        
        <?php if (!get_option('icsm_smtp_active')): ?>
            <div class="notice notice-warning">
                <p><?php printf(__('SMTP is currently disabled. <a href="%s">Enable it in settings</a> to use SMTP for sending emails.', 'insurance-crm-smtp'), admin_url('admin.php?page=insurance-crm-smtp')); ?></p>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.icsm-test-info {
    margin-top: 30px;
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    padding: 20px;
}

.icsm-test-info h3 {
    margin-top: 0;
}

.icsm-test-info table {
    margin-top: 15px;
}

.icsm-test-info td {
    padding: 8px 12px;
    border-bottom: 1px solid #f0f0f1;
}

.status-badge {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
}

.status-active {
    background: #d1e7dd;
    color: #0f5132;
}

.status-inactive {
    background: #f8d7da;
    color: #721c24;
}
</style>