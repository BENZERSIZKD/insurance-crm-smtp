<?php
if (!defined('ABSPATH')) {
    exit;
}

$error = isset($_GET['error']) ? sanitize_text_field($_GET['error']) : '';
$success = isset($_GET['success']) ? true : false;
$test_email_sent = isset($_GET['test_email_sent']) ? true : false;
$test_email_failed = isset($_GET['test_email_failed']) ? true : false;

// Get current settings for form population
$providers = Insurance_CRM_SMTP_Mailer::get_email_providers();
$current_provider = get_option('icsm_provider_preset', 'manual');
?>

<div class="wrap icsm-single-wizard">
    <h1><?php _e('Insurance CRM SMTP Setup Wizard', 'insurance-crm-smtp'); ?></h1>
    
    <?php if (!empty($error)): ?>
        <div class="notice notice-error">
            <p><?php echo esc_html($error); ?></p>
        </div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="notice notice-success">
            <p><?php _e('SMTP configuration completed successfully! Your email settings are now active.', 'insurance-crm-smtp'); ?></p>
            <?php if ($test_email_sent): ?>
                <p><?php _e('✓ Test email was sent successfully.', 'insurance-crm-smtp'); ?></p>
            <?php elseif ($test_email_failed): ?>
                <p><?php _e('⚠ SMTP configuration saved, but test email failed. This may be due to server restrictions or incorrect settings.', 'insurance-crm-smtp'); ?></p>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    
    <div class="icsm-wizard-container">
        <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" id="icsm-single-wizard-form">
            <input type="hidden" name="action" value="icsm_wizard_complete">
            <?php wp_nonce_field('icsm_wizard_complete', 'wizard_nonce'); ?>
            
            <!-- Provider Selection Section -->
            <div class="wizard-section">
                <h2><?php _e('1. Choose Your Email Provider', 'insurance-crm-smtp'); ?></h2>
                <p><?php _e('Select your email provider for automatic configuration, or choose manual setup:', 'insurance-crm-smtp'); ?></p>
                
                <div class="provider-grid">
                    <label class="provider-card" data-provider="gmail">
                        <input type="radio" name="provider" value="gmail" <?php checked($current_provider, 'gmail'); ?>>
                        <div class="provider-info">
                            <h3>Gmail</h3>
                            <p>smtp.gmail.com:587 (TLS)</p>
                            <small>Google's email service</small>
                        </div>
                    </label>
                    
                    <label class="provider-card" data-provider="outlook">
                        <input type="radio" name="provider" value="outlook" <?php checked($current_provider, 'outlook'); ?>>
                        <div class="provider-info">
                            <h3>Outlook/Hotmail</h3>
                            <p>smtp.live.com:587 (STARTTLS)</p>
                            <small>Microsoft's email service</small>
                        </div>
                    </label>
                    
                    <label class="provider-card" data-provider="yahoo">
                        <input type="radio" name="provider" value="yahoo" <?php checked($current_provider, 'yahoo'); ?>>
                        <div class="provider-info">
                            <h3>Yahoo</h3>
                            <p>smtp.mail.yahoo.com:587 (TLS)</p>
                            <small>Yahoo's email service</small>
                        </div>
                    </label>
                    
                    <label class="provider-card" data-provider="yandex">
                        <input type="radio" name="provider" value="yandex" <?php checked($current_provider, 'yandex'); ?>>
                        <div class="provider-info">
                            <h3>Yandex</h3>
                            <p>smtp.yandex.com:465 (SSL)</p>
                            <small>Yandex's email service</small>
                        </div>
                    </label>
                    
                    <label class="provider-card" data-provider="manual">
                        <input type="radio" name="provider" value="manual" <?php checked($current_provider, 'manual'); ?> checked>
                        <div class="provider-info">
                            <h3><?php _e('Manual Configuration', 'insurance-crm-smtp'); ?></h3>
                            <p><?php _e('Custom SMTP settings', 'insurance-crm-smtp'); ?></p>
                            <small><?php _e('Configure manually', 'insurance-crm-smtp'); ?></small>
                        </div>
                    </label>
                </div>
            </div>
            
            <!-- SMTP Configuration Section -->
            <div class="wizard-section">
                <h2><?php _e('2. SMTP Configuration', 'insurance-crm-smtp'); ?></h2>
                <p><?php _e('Configure your SMTP server settings:', 'insurance-crm-smtp'); ?></p>
                
                <div class="form-grid">
                    <div class="form-row">
                        <label for="smtp_host"><?php _e('SMTP Host', 'insurance-crm-smtp'); ?></label>
                        <input type="text" id="smtp_host" name="smtp_host" 
                               value="<?php echo esc_attr(get_option('icsm_smtp_host')); ?>" 
                               class="regular-text" required />
                    </div>
                    
                    <div class="form-row">
                        <label for="smtp_port"><?php _e('SMTP Port', 'insurance-crm-smtp'); ?></label>
                        <input type="number" id="smtp_port" name="smtp_port" 
                               value="<?php echo esc_attr(get_option('icsm_smtp_port', 587)); ?>" 
                               class="small-text" required />
                    </div>
                    
                    <div class="form-row">
                        <label for="smtp_security"><?php _e('Security', 'insurance-crm-smtp'); ?></label>
                        <select id="smtp_security" name="smtp_security" class="regular-text" required>
                            <option value="none" <?php selected(get_option('icsm_smtp_security'), 'none'); ?>><?php _e('None', 'insurance-crm-smtp'); ?></option>
                            <option value="ssl" <?php selected(get_option('icsm_smtp_security'), 'ssl'); ?>><?php _e('SSL', 'insurance-crm-smtp'); ?></option>
                            <option value="tls" <?php selected(get_option('icsm_smtp_security'), 'tls'); ?>><?php _e('TLS', 'insurance-crm-smtp'); ?></option>
                            <option value="starttls" <?php selected(get_option('icsm_smtp_security'), 'starttls'); ?>><?php _e('STARTTLS', 'insurance-crm-smtp'); ?></option>
                        </select>
                    </div>
                    
                    <div class="form-row">
                        <label for="smtp_username"><?php _e('Username', 'insurance-crm-smtp'); ?></label>
                        <input type="text" id="smtp_username" name="smtp_username" 
                               value="<?php echo esc_attr(get_option('icsm_smtp_username')); ?>" 
                               class="regular-text" required />
                    </div>
                    
                    <div class="form-row">
                        <label for="smtp_password"><?php _e('Password', 'insurance-crm-smtp'); ?></label>
                        <input type="password" id="smtp_password" name="smtp_password" 
                               value="" class="regular-text" required 
                               placeholder="<?php _e('Enter your email password', 'insurance-crm-smtp'); ?>" />
                    </div>
                    
                    <div class="form-row">
                        <label for="from_email"><?php _e('From Email', 'insurance-crm-smtp'); ?></label>
                        <input type="email" id="from_email" name="from_email" 
                               value="<?php echo esc_attr(get_option('icsm_from_email', get_option('admin_email'))); ?>" 
                               class="regular-text" required />
                    </div>
                    
                    <div class="form-row">
                        <label for="from_name"><?php _e('From Name', 'insurance-crm-smtp'); ?></label>
                        <input type="text" id="from_name" name="from_name" 
                               value="<?php echo esc_attr(get_option('icsm_from_name', get_bloginfo('name'))); ?>" 
                               class="regular-text" />
                    </div>
                </div>
            </div>
            
            <!-- Test Email Section -->
            <div class="wizard-section">
                <h2><?php _e('3. Test Configuration (Optional)', 'insurance-crm-smtp'); ?></h2>
                <p><?php _e('Send a test email to verify your configuration:', 'insurance-crm-smtp'); ?></p>
                
                <div class="form-row">
                    <label for="test_email"><?php _e('Test Email Address', 'insurance-crm-smtp'); ?></label>
                    <input type="email" id="test_email" name="test_email" 
                           value="<?php echo esc_attr(get_option('admin_email')); ?>" 
                           class="regular-text" />
                    <p class="description"><?php _e('Leave empty to skip test email.', 'insurance-crm-smtp'); ?></p>
                </div>
            </div>
            
            <!-- Submit Section -->
            <div class="wizard-actions">
                <div class="action-buttons">
                    <?php submit_button(__('Complete Setup & Activate SMTP', 'insurance-crm-smtp'), 'primary large', 'submit', false, array('id' => 'complete-setup-btn')); ?>
                    <a href="<?php echo admin_url('admin.php?page=insurance-crm-smtp'); ?>" class="button button-secondary"><?php _e('Skip Wizard', 'insurance-crm-smtp'); ?></a>
                </div>
                
                <div class="wizard-info">
                    <h3><?php _e('What happens next?', 'insurance-crm-smtp'); ?></h3>
                    <ul>
                        <li><?php _e('✓ SMTP settings will be saved and activated', 'insurance-crm-smtp'); ?></li>
                        <li><?php _e('✓ Password will be encrypted for security', 'insurance-crm-smtp'); ?></li>
                        <li><?php _e('✓ Test email will be sent (if provided)', 'insurance-crm-smtp'); ?></li>
                        <li><?php _e('✓ You can modify settings anytime in the main settings page', 'insurance-crm-smtp'); ?></li>
                    </ul>
                </div>
            </div>
        </form>
    </div>
</div>

<style>
.icsm-single-wizard {
    max-width: 1000px;
}

.icsm-wizard-container {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 6px;
    overflow: hidden;
}

.wizard-section {
    padding: 30px;
    border-bottom: 1px solid #f0f0f0;
}

.wizard-section:last-child {
    border-bottom: none;
}

.wizard-section h2 {
    color: #1d2327;
    font-size: 22px;
    margin-bottom: 8px;
}

.wizard-section p {
    color: #646970;
    margin-bottom: 20px;
}

/* Provider Grid */
.provider-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    margin-bottom: 20px;
}

.provider-card {
    border: 2px solid #ddd;
    border-radius: 6px;
    padding: 20px;
    cursor: pointer;
    transition: all 0.3s ease;
    display: block;
    background: #fff;
}

.provider-card:hover {
    border-color: #0073aa;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

.provider-card input[type="radio"] {
    display: none;
}

.provider-card input[type="radio"]:checked + .provider-info {
    color: #0073aa;
}

.provider-card input[type="radio"]:checked {
    display: inline-block;
}

.provider-card.selected {
    border-color: #0073aa;
    background: #f0f6fc;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

.provider-info h3 {
    margin: 0 0 8px 0;
    font-size: 16px;
    font-weight: 600;
}

.provider-info p {
    margin: 0 0 5px 0;
    font-size: 13px;
    color: #666;
}

.provider-info small {
    color: #888;
    font-size: 12px;
}

/* Form Grid */
.form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
}

.form-row {
    display: flex;
    flex-direction: column;
}

.form-row label {
    font-weight: 600;
    margin-bottom: 5px;
    color: #1d2327;
}

.form-row input,
.form-row select {
    padding: 8px 12px;
    border: 1px solid #8c8f94;
    border-radius: 4px;
    font-size: 14px;
}

.form-row input:focus,
.form-row select:focus {
    border-color: #0073aa;
    box-shadow: 0 0 0 1px #0073aa;
    outline: none;
}

.form-row .description {
    margin-top: 5px;
    font-size: 12px;
    color: #646970;
}

/* Actions */
.wizard-actions {
    padding: 30px;
    background: #f9f9f9;
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 30px;
    flex-wrap: wrap;
}

.action-buttons {
    display: flex;
    gap: 10px;
    align-items: center;
}

.wizard-info {
    flex: 1;
    min-width: 300px;
}

.wizard-info h3 {
    margin: 0 0 10px 0;
    font-size: 16px;
    color: #1d2327;
}

.wizard-info ul {
    margin: 0;
    padding: 0;
    list-style: none;
}

.wizard-info li {
    padding: 4px 0;
    color: #646970;
    font-size: 14px;
}

/* Loading state */
.icsm-loading #complete-setup-btn {
    opacity: 0.7;
    pointer-events: none;
}

.icsm-loading #complete-setup-btn::after {
    content: "...";
    animation: loading-dots 1.4s infinite;
}

@keyframes loading-dots {
    0%, 20% { opacity: 0; }
    50% { opacity: 1; }
    100% { opacity: 0; }
}

/* Responsive */
@media (max-width: 782px) {
    .wizard-actions {
        flex-direction: column;
        text-align: center;
    }
    
    .action-buttons {
        width: 100%;
        justify-content: center;
    }
    
    .form-grid {
        grid-template-columns: 1fr;
    }
    
    .provider-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
jQuery(document).ready(function($) {
    'use strict';
    
    // Provider selection handlers
    $('.provider-card').on('click', function() {
        $('.provider-card').removeClass('selected');
        $(this).addClass('selected');
        $(this).find('input[type="radio"]').prop('checked', true);
        
        // Auto-fill SMTP settings based on provider
        const provider = $(this).data('provider');
        updateSMTPSettings(provider);
    });
    
    // Initialize selected provider
    $('input[name="provider"]:checked').closest('.provider-card').addClass('selected');
    
    // Form submission handler
    $('#icsm-single-wizard-form').on('submit', function(e) {
        const $form = $(this);
        const $button = $('#complete-setup-btn');
        
        // Basic validation
        if (!$('input[name="provider"]:checked').length) {
            alert('<?php _e('Please select an email provider.', 'insurance-crm-smtp'); ?>');
            e.preventDefault();
            return;
        }
        
        // Visual feedback
        $form.addClass('icsm-loading');
        $button.val('<?php _e('Setting up SMTP...', 'insurance-crm-smtp'); ?>');
    });
    
    // Auto-fill SMTP settings based on provider selection
    function updateSMTPSettings(provider) {
        const providers = {
            'gmail': {
                host: 'smtp.gmail.com',
                port: 587,
                security: 'tls'
            },
            'outlook': {
                host: 'smtp.live.com',
                port: 587,
                security: 'starttls'
            },
            'yahoo': {
                host: 'smtp.mail.yahoo.com',
                port: 587,
                security: 'tls'
            },
            'yandex': {
                host: 'smtp.yandex.com',
                port: 465,
                security: 'ssl'
            }
        };
        
        if (providers[provider]) {
            const settings = providers[provider];
            $('#smtp_host').val(settings.host);
            $('#smtp_port').val(settings.port);
            $('#smtp_security').val(settings.security);
            
            // Visual feedback
            $('#smtp_host, #smtp_port, #smtp_security').css({
                'border-color': '#00a32a',
                'box-shadow': '0 0 0 1px #00a32a'
            });
            
            setTimeout(function() {
                $('#smtp_host, #smtp_port, #smtp_security').css({
                    'border-color': '',
                    'box-shadow': ''
                });
            }, 2000);
        } else if (provider === 'manual') {
            // Clear for manual configuration
            $('#smtp_host').val('');
            $('#smtp_port').val('587');
            $('#smtp_security').val('tls');
        }
    }
    
    // Initialize with current provider
    const currentProvider = $('input[name="provider"]:checked').val();
    if (currentProvider) {
        updateSMTPSettings(currentProvider);
    }
});
</script>