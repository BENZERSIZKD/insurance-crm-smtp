<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap">
    <h1><?php _e('Insurance CRM SMTP Settings', 'insurance-crm-smtp'); ?></h1>
    
    <?php settings_errors(); ?>
    
    <div class="icsm-status-card">
        <h3><?php _e('SMTP Status', 'insurance-crm-smtp'); ?></h3>
        <p class="status-indicator status-<?php echo esc_attr($smtp_status['color']); ?>">
            <span class="dashicons dashicons-<?php echo $smtp_status['status'] === 'active' ? 'yes' : 'warning'; ?>"></span>
            <?php echo esc_html($smtp_status['message']); ?>
        </p>
    </div>
    
    <form method="post" action="">
        <?php wp_nonce_field('icsm_save_settings', 'icsm_nonce'); ?>
        
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="icsm_smtp_active"><?php _e('Enable SMTP', 'insurance-crm-smtp'); ?></label>
                </th>
                <td>
                    <input type="checkbox" id="icsm_smtp_active" name="icsm_smtp_active" value="1" 
                           <?php checked(get_option('icsm_smtp_active', false)); ?> />
                    <label for="icsm_smtp_active"><?php _e('Use SMTP for sending emails', 'insurance-crm-smtp'); ?></label>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="icsm_provider_preset"><?php _e('Email Provider', 'insurance-crm-smtp'); ?></label>
                </th>
                <td>
                    <select id="icsm_provider_preset" name="icsm_provider_preset" class="regular-text">
                        <option value="manual" <?php selected($current_provider, 'manual'); ?>><?php _e('Manual Configuration', 'insurance-crm-smtp'); ?></option>
                        <?php foreach ($providers as $key => $provider): ?>
                            <option value="<?php echo esc_attr($key); ?>" <?php selected($current_provider, $key); ?>>
                                <?php echo esc_html($provider['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <p class="description"><?php _e('Select your email provider or choose manual configuration.', 'insurance-crm-smtp'); ?></p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="icsm_smtp_host"><?php _e('SMTP Host', 'insurance-crm-smtp'); ?></label>
                </th>
                <td>
                    <input type="text" id="icsm_smtp_host" name="icsm_smtp_host" 
                           value="<?php echo esc_attr(get_option('icsm_smtp_host')); ?>" class="regular-text" />
                    <p class="description"><?php _e('Your SMTP server hostname.', 'insurance-crm-smtp'); ?></p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="icsm_smtp_port"><?php _e('SMTP Port', 'insurance-crm-smtp'); ?></label>
                </th>
                <td>
                    <input type="number" id="icsm_smtp_port" name="icsm_smtp_port" 
                           value="<?php echo esc_attr(get_option('icsm_smtp_port', 587)); ?>" class="small-text" />
                    <p class="description"><?php _e('SMTP port (usually 25, 465, or 587).', 'insurance-crm-smtp'); ?></p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="icsm_smtp_security"><?php _e('Security', 'insurance-crm-smtp'); ?></label>
                </th>
                <td>
                    <select id="icsm_smtp_security" name="icsm_smtp_security" class="regular-text">
                        <option value="none" <?php selected(get_option('icsm_smtp_security'), 'none'); ?>><?php _e('None', 'insurance-crm-smtp'); ?></option>
                        <option value="ssl" <?php selected(get_option('icsm_smtp_security'), 'ssl'); ?>><?php _e('SSL', 'insurance-crm-smtp'); ?></option>
                        <option value="tls" <?php selected(get_option('icsm_smtp_security'), 'tls'); ?>><?php _e('TLS', 'insurance-crm-smtp'); ?></option>
                        <option value="starttls" <?php selected(get_option('icsm_smtp_security'), 'starttls'); ?>><?php _e('STARTTLS', 'insurance-crm-smtp'); ?></option>
                    </select>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="icsm_smtp_username"><?php _e('Username', 'insurance-crm-smtp'); ?></label>
                </th>
                <td>
                    <input type="text" id="icsm_smtp_username" name="icsm_smtp_username" 
                           value="<?php echo esc_attr(get_option('icsm_smtp_username')); ?>" class="regular-text" />
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="icsm_smtp_password"><?php _e('Password', 'insurance-crm-smtp'); ?></label>
                </th>
                <td>
                    <?php 
                    $has_password = !empty(get_option('icsm_smtp_password'));
                    $password_placeholder = $has_password 
                        ? __('Password saved - enter new password to change', 'insurance-crm-smtp')
                        : __('Enter your SMTP password', 'insurance-crm-smtp');
                    ?>
                    <input type="password" id="icsm_smtp_password" name="icsm_smtp_password" 
                           value="" class="regular-text" placeholder="<?php echo esc_attr($password_placeholder); ?>" />
                    <?php if ($has_password): ?>
                        <p class="description">
                            <span class="dashicons dashicons-yes-alt" style="color: #00a32a;"></span>
                            <?php _e('Password is saved and encrypted. Leave empty to keep current password.', 'insurance-crm-smtp'); ?>
                        </p>
                    <?php else: ?>
                        <p class="description"><?php _e('Password will be stored encrypted for security.', 'insurance-crm-smtp'); ?></p>
                    <?php endif; ?>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="icsm_from_email"><?php _e('From Email', 'insurance-crm-smtp'); ?></label>
                </th>
                <td>
                    <input type="email" id="icsm_from_email" name="icsm_from_email" 
                           value="<?php echo esc_attr(get_option('icsm_from_email')); ?>" class="regular-text" />
                    <p class="description"><?php _e('Email address to send from.', 'insurance-crm-smtp'); ?></p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="icsm_from_name"><?php _e('From Name', 'insurance-crm-smtp'); ?></label>
                </th>
                <td>
                    <input type="text" id="icsm_from_name" name="icsm_from_name" 
                           value="<?php echo esc_attr(get_option('icsm_from_name')); ?>" class="regular-text" />
                    <p class="description"><?php _e('Name to display as sender.', 'insurance-crm-smtp'); ?></p>
                </td>
            </tr>
        </table>
        
        <div class="icsm-button-row">
            <?php submit_button(__('Save Settings', 'insurance-crm-smtp'), 'primary', 'submit', false); ?>
            <input type="submit" name="test_connection" value="<?php _e('Test Connection', 'insurance-crm-smtp'); ?>" class="button button-secondary" />
        </div>
    </form>
</div>

<style>
.icsm-status-card {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    padding: 15px;
    margin-bottom: 20px;
}

.status-indicator {
    font-weight: 600;
    padding: 8px 12px;
    border-radius: 4px;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.status-green {
    background: #d1e7dd;
    color: #0f5132;
}

.status-red {
    background: #f8d7da;
    color: #721c24;
}

.status-orange {
    background: #fff3cd;
    color: #856404;
}

.icsm-button-row {
    margin-top: 20px;
    display: flex;
    gap: 10px;
}
</style>

<script>
jQuery(document).ready(function($) {
    // Provider preset handler
    $('#icsm_provider_preset').change(function() {
        const provider = $(this).val();
        
        if (provider !== 'manual') {
            const providers = <?php echo json_encode($providers); ?>;
            const settings = providers[provider];
            
            if (settings) {
                $('#icsm_smtp_host').val(settings.host);
                $('#icsm_smtp_port').val(settings.port);
                $('#icsm_smtp_security').val(settings.security);
            }
        }
    });
});
</script>