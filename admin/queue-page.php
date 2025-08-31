<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap">
    <h1><?php _e('Email Queue Management', 'insurance-crm-smtp'); ?></h1>
    
    <?php settings_errors('icsm_queue'); ?>
    
    <div class="icsm-stats-row">
        <div class="icsm-stat-card">
            <h3><?php _e('Total Queued', 'insurance-crm-smtp'); ?></h3>
            <span class="stat-number"><?php echo esc_html($stats['total']); ?></span>
        </div>
        <div class="icsm-stat-card stat-pending">
            <h3><?php _e('Pending', 'insurance-crm-smtp'); ?></h3>
            <span class="stat-number"><?php echo esc_html($stats['pending']); ?></span>
        </div>
        <div class="icsm-stat-card stat-success">
            <h3><?php _e('Sent', 'insurance-crm-smtp'); ?></h3>
            <span class="stat-number"><?php echo esc_html($stats['sent']); ?></span>
        </div>
        <div class="icsm-stat-card stat-error">
            <h3><?php _e('Failed', 'insurance-crm-smtp'); ?></h3>
            <span class="stat-number"><?php echo esc_html($stats['failed']); ?></span>
        </div>
    </div>
    
    <div class="icsm-rate-limit-card">
        <h3><?php _e('Rate Limiting Status', 'insurance-crm-smtp'); ?></h3>
        <?php if ($rate_limit_status['enabled']): ?>
            <p>
                <strong><?php _e('Status:', 'insurance-crm-smtp'); ?></strong>
                <span class="status-badge status-<?php echo $rate_limit_status['is_limited'] ? 'error' : 'success'; ?>">
                    <?php echo $rate_limit_status['is_limited'] ? __('Limited', 'insurance-crm-smtp') : __('Active', 'insurance-crm-smtp'); ?>
                </span>
            </p>
            <p>
                <strong><?php _e('Limit:', 'insurance-crm-smtp'); ?></strong>
                <?php printf(__('%d emails per %s', 'insurance-crm-smtp'), $rate_limit_status['limit'], human_time_diff(0, $rate_limit_status['window'])); ?>
            </p>
            <p>
                <strong><?php _e('Remaining:', 'insurance-crm-smtp'); ?></strong>
                <?php echo esc_html($rate_limit_status['remaining']); ?> emails
            </p>
            <?php if ($rate_limit_status['reset_in'] > 0): ?>
                <p>
                    <strong><?php _e('Resets in:', 'insurance-crm-smtp'); ?></strong>
                    <?php echo Insurance_CRM_SMTP_Rate_Limiter::format_time_remaining($rate_limit_status['reset_in']); ?>
                </p>
            <?php endif; ?>
        <?php else: ?>
            <p class="status-badge status-inactive"><?php _e('Disabled', 'insurance-crm-smtp'); ?></p>
        <?php endif; ?>
    </div>
    
    <div class="icsm-queue-actions">
        <h3><?php _e('Queue Actions', 'insurance-crm-smtp'); ?></h3>
        
        <form method="post" action="" style="display: inline;">
            <?php wp_nonce_field('icsm_queue_action', 'queue_nonce'); ?>
            <input type="hidden" name="action" value="process_queue">
            <?php submit_button(__('Process Queue Now', 'insurance-crm-smtp'), 'primary', 'submit', false); ?>
        </form>
        
        <form method="post" action="" style="display: inline; margin-left: 10px;">
            <?php wp_nonce_field('icsm_queue_action', 'queue_nonce'); ?>
            <input type="hidden" name="action" value="retry_failed">
            <?php submit_button(__('Retry Failed Emails', 'insurance-crm-smtp'), 'secondary', 'submit', false); ?>
        </form>
        
        <form method="post" action="" style="display: inline; margin-left: 10px;" 
              onsubmit="return confirm('<?php _e('Are you sure you want to clear the entire queue? This cannot be undone.', 'insurance-crm-smtp'); ?>');">
            <?php wp_nonce_field('icsm_queue_action', 'queue_nonce'); ?>
            <input type="hidden" name="action" value="clear_queue">
            <?php submit_button(__('Clear Queue', 'insurance-crm-smtp'), 'delete', 'submit', false); ?>
        </form>
    </div>
    
    <div class="icsm-queue-info">
        <h3><?php _e('How Email Queue Works', 'insurance-crm-smtp'); ?></h3>
        <div class="info-grid">
            <div class="info-item">
                <h4><?php _e('Automatic Processing', 'insurance-crm-smtp'); ?></h4>
                <p><?php _e('The queue is automatically processed every hour. Emails are sent in order of priority and creation time.', 'insurance-crm-smtp'); ?></p>
            </div>
            
            <div class="info-item">
                <h4><?php _e('Retry Logic', 'insurance-crm-smtp'); ?></h4>
                <p><?php _e('Failed emails are automatically retried up to 3 times with increasing delays between attempts.', 'insurance-crm-smtp'); ?></p>
            </div>
            
            <div class="info-item">
                <h4><?php _e('Priority System', 'insurance-crm-smtp'); ?></h4>
                <p><?php _e('Emails with lower priority numbers (1-10) are sent first. Default priority is 5.', 'insurance-crm-smtp'); ?></p>
            </div>
            
            <div class="info-item">
                <h4><?php _e('Rate Limiting', 'insurance-crm-smtp'); ?></h4>
                <p><?php _e('Rate limiting prevents too many emails from being sent too quickly, helping avoid spam filters.', 'insurance-crm-smtp'); ?></p>
            </div>
        </div>
    </div>
    
    <div class="icsm-queue-settings">
        <h3><?php _e('Queue Settings', 'insurance-crm-smtp'); ?></h3>
        <p><?php printf(__('Queue settings can be configured in the <a href="%s">main settings page</a>.', 'insurance-crm-smtp'), admin_url('admin.php?page=insurance-crm-smtp')); ?></p>
        
        <table class="widefat">
            <tr>
                <td><strong><?php _e('Queue Processing:', 'insurance-crm-smtp'); ?></strong></td>
                <td><?php _e('Every hour via WordPress cron', 'insurance-crm-smtp'); ?></td>
            </tr>
            <tr>
                <td><strong><?php _e('Max Retries:', 'insurance-crm-smtp'); ?></strong></td>
                <td>3</td>
            </tr>
            <tr>
                <td><strong><?php _e('Cleanup:', 'insurance-crm-smtp'); ?></strong></td>
                <td><?php _e('Failed emails older than 7 days, sent emails older than 30 days', 'insurance-crm-smtp'); ?></td>
            </tr>
            <tr>
                <td><strong><?php _e('Next Scheduled Run:', 'insurance-crm-smtp'); ?></strong></td>
                <td>
                    <?php
                    $next_run = wp_next_scheduled('icsm_process_queue');
                    if ($next_run) {
                        echo date('Y-m-d H:i:s', $next_run);
                    } else {
                        _e('Not scheduled', 'insurance-crm-smtp');
                    }
                    ?>
                </td>
            </tr>
        </table>
    </div>
</div>

<style>
.icsm-rate-limit-card {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    padding: 20px;
    margin: 20px 0;
}

.icsm-rate-limit-card h3 {
    margin-top: 0;
}

.icsm-queue-actions {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    padding: 20px;
    margin: 20px 0;
}

.icsm-queue-actions h3 {
    margin-top: 0;
    margin-bottom: 15px;
}

.icsm-queue-info {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    padding: 20px;
    margin: 20px 0;
}

.icsm-queue-info h3 {
    margin-top: 0;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-top: 15px;
}

.info-item h4 {
    margin-top: 0;
    color: #0073aa;
}

.info-item p {
    margin-bottom: 0;
    color: #666;
}

.icsm-queue-settings {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    padding: 20px;
    margin: 20px 0;
}

.icsm-queue-settings h3 {
    margin-top: 0;
}

.icsm-queue-settings table {
    margin-top: 15px;
}

.icsm-queue-settings td {
    padding: 8px 12px;
    border-bottom: 1px solid #f0f0f1;
}
</style>