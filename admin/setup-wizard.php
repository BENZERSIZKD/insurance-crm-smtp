<?php
if (!defined('ABSPATH')) {
    exit;
}

$step = isset($_GET['step']) ? intval($_GET['step']) : 1;
$progress = Insurance_CRM_SMTP_Setup_Wizard::get_wizard_progress($step);
$error = isset($_GET['error']) ? sanitize_text_field($_GET['error']) : '';
?>
<div class="wrap icsm-wizard">
    <h1><?php _e('Insurance CRM SMTP Setup Wizard', 'insurance-crm-smtp'); ?></h1>
    
    <?php if (!empty($error)): ?>
        <div class="notice notice-error">
            <p><?php echo esc_html($error); ?></p>
        </div>
    <?php endif; ?>
    
    <div class="icsm-wizard-progress">
        <?php foreach ($progress['steps'] as $step_num => $step_name): ?>
            <div class="step-item <?php echo $step_num === $progress['current'] ? 'active' : ($step_num < $progress['current'] ? 'completed' : 'pending'); ?>">
                <span class="step-number"><?php echo $step_num; ?></span>
                <span class="step-name"><?php echo esc_html($step_name); ?></span>
            </div>
        <?php endforeach; ?>
    </div>
    
    <div class="icsm-wizard-content">
        <?php if ($step === 1): ?>
            <!-- Step 1: Welcome -->
            <div class="wizard-step">
                <h2><?php _e('Welcome to Insurance CRM SMTP', 'insurance-crm-smtp'); ?></h2>
                <p><?php _e('This wizard will help you configure SMTP email delivery for your WordPress site in just a few simple steps.', 'insurance-crm-smtp'); ?></p>
                
                <div class="feature-list">
                    <h3><?php _e('Features included:', 'insurance-crm-smtp'); ?></h3>
                    <ul>
                        <li><?php _e('Support for popular email providers (Gmail, Outlook, Yahoo, Yandex)', 'insurance-crm-smtp'); ?></li>
                        <li><?php _e('Secure password encryption', 'insurance-crm-smtp'); ?></li>
                        <li><?php _e('Comprehensive email logging', 'insurance-crm-smtp'); ?></li>
                        <li><?php _e('Connection testing and troubleshooting', 'insurance-crm-smtp'); ?></li>
                        <li><?php _e('Rate limiting and retry mechanisms', 'insurance-crm-smtp'); ?></li>
                    </ul>
                </div>
                
                <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
                    <input type="hidden" name="action" value="icsm_wizard_step">
                    <input type="hidden" name="step" value="1">
                    <?php wp_nonce_field('icsm_wizard_step', 'wizard_nonce'); ?>
                    <?php submit_button(__('Get Started', 'insurance-crm-smtp'), 'primary large'); ?>
                </form>
            </div>
            
        <?php elseif ($step === 2): ?>
            <!-- Step 2: Provider Selection -->
            <div class="wizard-step">
                <h2><?php _e('Choose Your Email Provider', 'insurance-crm-smtp'); ?></h2>
                <p><?php _e('Select your email provider to automatically configure SMTP settings, or choose manual configuration.', 'insurance-crm-smtp'); ?></p>
                
                <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
                    <input type="hidden" name="action" value="icsm_wizard_step">
                    <input type="hidden" name="step" value="2">
                    <?php wp_nonce_field('icsm_wizard_step', 'wizard_nonce'); ?>
                    
                    <div class="provider-options">
                        <?php 
                        $providers = Insurance_CRM_SMTP_Mailer::get_email_providers();
                        $current_provider = get_option('icsm_provider_preset', '');
                        ?>
                        
                        <label class="provider-option">
                            <input type="radio" name="provider" value="gmail" <?php checked($current_provider, 'gmail'); ?>>
                            <div class="provider-card">
                                <h3>Gmail</h3>
                                <p>smtp.gmail.com:587 (TLS)</p>
                            </div>
                        </label>
                        
                        <label class="provider-option">
                            <input type="radio" name="provider" value="outlook" <?php checked($current_provider, 'outlook'); ?>>
                            <div class="provider-card">
                                <h3>Outlook/Hotmail</h3>
                                <p>smtp.live.com:587 (STARTTLS)</p>
                            </div>
                        </label>
                        
                        <label class="provider-option">
                            <input type="radio" name="provider" value="yahoo" <?php checked($current_provider, 'yahoo'); ?>>
                            <div class="provider-card">
                                <h3>Yahoo</h3>
                                <p>smtp.mail.yahoo.com:587 (TLS)</p>
                            </div>
                        </label>
                        
                        <label class="provider-option">
                            <input type="radio" name="provider" value="yandex" <?php checked($current_provider, 'yandex'); ?>>
                            <div class="provider-card">
                                <h3>Yandex</h3>
                                <p>smtp.yandex.com:465 (SSL)</p>
                            </div>
                        </label>
                        
                        <label class="provider-option">
                            <input type="radio" name="provider" value="manual" <?php checked($current_provider, 'manual'); ?>>
                            <div class="provider-card">
                                <h3><?php _e('Manual Configuration', 'insurance-crm-smtp'); ?></h3>
                                <p><?php _e('Configure SMTP settings manually', 'insurance-crm-smtp'); ?></p>
                            </div>
                        </label>
                    </div>
                    
                    <div class="wizard-navigation">
                        <a href="<?php echo admin_url('admin.php?page=insurance-crm-smtp-wizard&step=1'); ?>" class="button button-secondary"><?php _e('Previous', 'insurance-crm-smtp'); ?></a>
                        <?php submit_button(__('Next', 'insurance-crm-smtp'), 'primary', 'submit', false); ?>
                    </div>
                </form>
            </div>
            
        <?php elseif ($step === 3): ?>
            <!-- Step 3: SMTP Configuration -->
            <div class="wizard-step">
                <h2><?php _e('SMTP Configuration', 'insurance-crm-smtp'); ?></h2>
                <p><?php _e('Enter your SMTP server details and authentication information.', 'insurance-crm-smtp'); ?></p>
                
                <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
                    <input type="hidden" name="action" value="icsm_wizard_step">
                    <input type="hidden" name="step" value="3">
                    <?php wp_nonce_field('icsm_wizard_step', 'wizard_nonce'); ?>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="smtp_host"><?php _e('SMTP Host', 'insurance-crm-smtp'); ?></label>
                            </th>
                            <td>
                                <input type="text" id="smtp_host" name="smtp_host" 
                                       value="<?php echo esc_attr(get_option('icsm_smtp_host')); ?>" class="regular-text" required />
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="smtp_port"><?php _e('SMTP Port', 'insurance-crm-smtp'); ?></label>
                            </th>
                            <td>
                                <input type="number" id="smtp_port" name="smtp_port" 
                                       value="<?php echo esc_attr(get_option('icsm_smtp_port', 587)); ?>" class="small-text" required />
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="smtp_security"><?php _e('Security', 'insurance-crm-smtp'); ?></label>
                            </th>
                            <td>
                                <select id="smtp_security" name="smtp_security" class="regular-text" required>
                                    <option value="none" <?php selected(get_option('icsm_smtp_security'), 'none'); ?>><?php _e('None', 'insurance-crm-smtp'); ?></option>
                                    <option value="ssl" <?php selected(get_option('icsm_smtp_security'), 'ssl'); ?>><?php _e('SSL', 'insurance-crm-smtp'); ?></option>
                                    <option value="tls" <?php selected(get_option('icsm_smtp_security'), 'tls'); ?>><?php _e('TLS', 'insurance-crm-smtp'); ?></option>
                                    <option value="starttls" <?php selected(get_option('icsm_smtp_security'), 'starttls'); ?>><?php _e('STARTTLS', 'insurance-crm-smtp'); ?></option>
                                </select>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="smtp_username"><?php _e('Username', 'insurance-crm-smtp'); ?></label>
                            </th>
                            <td>
                                <input type="text" id="smtp_username" name="smtp_username" 
                                       value="<?php echo esc_attr(get_option('icsm_smtp_username')); ?>" class="regular-text" required />
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="smtp_password"><?php _e('Password', 'insurance-crm-smtp'); ?></label>
                            </th>
                            <td>
                                <input type="password" id="smtp_password" name="smtp_password" 
                                       value="" class="regular-text" required />
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="from_email"><?php _e('From Email', 'insurance-crm-smtp'); ?></label>
                            </th>
                            <td>
                                <input type="email" id="from_email" name="from_email" 
                                       value="<?php echo esc_attr(get_option('icsm_from_email', get_option('admin_email'))); ?>" class="regular-text" required />
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="from_name"><?php _e('From Name', 'insurance-crm-smtp'); ?></label>
                            </th>
                            <td>
                                <input type="text" id="from_name" name="from_name" 
                                       value="<?php echo esc_attr(get_option('icsm_from_name', get_bloginfo('name'))); ?>" class="regular-text" />
                            </td>
                        </tr>
                    </table>
                    
                    <div class="wizard-navigation">
                        <a href="<?php echo admin_url('admin.php?page=insurance-crm-smtp-wizard&step=2'); ?>" class="button button-secondary"><?php _e('Previous', 'insurance-crm-smtp'); ?></a>
                        <?php submit_button(__('Next', 'insurance-crm-smtp'), 'primary', 'submit', false); ?>
                    </div>
                </form>
            </div>
            
        <?php elseif ($step === 4): ?>
            <!-- Step 4: Test and Complete -->
            <div class="wizard-step">
                <h2><?php _e('Test & Complete Setup', 'insurance-crm-smtp'); ?></h2>
                <p><?php _e('Send a test email to verify your configuration and complete the setup.', 'insurance-crm-smtp'); ?></p>
                
                <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
                    <input type="hidden" name="action" value="icsm_wizard_step">
                    <input type="hidden" name="step" value="4">
                    <?php wp_nonce_field('icsm_wizard_step', 'wizard_nonce'); ?>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="test_email"><?php _e('Test Email Address', 'insurance-crm-smtp'); ?></label>
                            </th>
                            <td>
                                <input type="email" id="test_email" name="test_email" 
                                       value="<?php echo esc_attr(get_option('admin_email')); ?>" class="regular-text" />
                                <p class="description"><?php _e('Leave empty to skip test email.', 'insurance-crm-smtp'); ?></p>
                            </td>
                        </tr>
                    </table>
                    
                    <div class="wizard-complete-info">
                        <h3><?php _e('Setup Complete!', 'insurance-crm-smtp'); ?></h3>
                        <p><?php _e('Your SMTP configuration will be activated and the setup wizard will be hidden after clicking "Complete Setup".', 'insurance-crm-smtp'); ?></p>
                    </div>
                    
                    <div class="wizard-navigation">
                        <a href="<?php echo admin_url('admin.php?page=insurance-crm-smtp-wizard&step=3'); ?>" class="button button-secondary"><?php _e('Previous', 'insurance-crm-smtp'); ?></a>
                        <?php submit_button(__('Complete Setup', 'insurance-crm-smtp'), 'primary', 'submit', false); ?>
                    </div>
                </form>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.icsm-wizard {
    max-width: 800px;
    margin: 20px 0;
}

.icsm-wizard-progress {
    display: flex;
    justify-content: space-between;
    margin: 30px 0;
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    padding: 20px;
}

.step-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    flex: 1;
    position: relative;
}

.step-item:not(:last-child):after {
    content: '';
    position: absolute;
    top: 15px;
    right: -50%;
    width: 100%;
    height: 2px;
    background: #ddd;
    z-index: 1;
}

.step-item.completed:not(:last-child):after {
    background: #00a32a;
}

.step-number {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background: #ddd;
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    margin-bottom: 8px;
    position: relative;
    z-index: 2;
}

.step-item.active .step-number {
    background: #0073aa;
}

.step-item.completed .step-number {
    background: #00a32a;
}

.step-name {
    font-size: 14px;
    color: #50575e;
}

.icsm-wizard-content {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    padding: 30px;
}

.wizard-step h2 {
    margin-top: 0;
}

.feature-list ul {
    list-style-type: none;
    padding: 0;
}

.feature-list li {
    padding: 5px 0;
    padding-left: 20px;
    position: relative;
}

.feature-list li:before {
    content: '✓';
    position: absolute;
    left: 0;
    color: #00a32a;
    font-weight: 600;
}

.provider-options {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    margin: 20px 0;
}

.provider-option {
    cursor: pointer;
}

.provider-card {
    border: 2px solid #ddd;
    border-radius: 4px;
    padding: 20px;
    text-align: center;
    transition: border-color 0.2s;
}

.provider-option input[type="radio"]:checked + .provider-card {
    border-color: #0073aa;
    background: #f0f6fc;
}

.provider-card h3 {
    margin: 0 0 10px 0;
    font-size: 16px;
}

.provider-card p {
    margin: 0;
    color: #666;
    font-size: 14px;
}

.wizard-navigation {
    margin-top: 30px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.wizard-complete-info {
    background: #d1e7dd;
    border: 1px solid #badbcc;
    border-radius: 4px;
    padding: 15px;
    margin: 20px 0;
}

.wizard-complete-info h3 {
    margin-top: 0;
    color: #0f5132;
}

.wizard-complete-info p {
    margin-bottom: 0;
    color: #0f5132;
}
</style>