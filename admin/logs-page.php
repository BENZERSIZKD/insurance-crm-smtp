<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap">
    <h1><?php _e('SMTP Email Logs', 'insurance-crm-smtp'); ?></h1>
    
    <div class="icsm-stats-row">
        <div class="icsm-stat-card">
            <h3><?php _e('Total', 'insurance-crm-smtp'); ?></h3>
            <span class="stat-number"><?php echo esc_html($stats['total']); ?></span>
        </div>
        <div class="icsm-stat-card stat-success">
            <h3><?php _e('Sent', 'insurance-crm-smtp'); ?></h3>
            <span class="stat-number"><?php echo esc_html($stats['sent']); ?></span>
        </div>
        <div class="icsm-stat-card stat-error">
            <h3><?php _e('Failed', 'insurance-crm-smtp'); ?></h3>
            <span class="stat-number"><?php echo esc_html($stats['failed']); ?></span>
        </div>
        <div class="icsm-stat-card stat-pending">
            <h3><?php _e('Pending', 'insurance-crm-smtp'); ?></h3>
            <span class="stat-number"><?php echo esc_html($stats['pending']); ?></span>
        </div>
    </div>
    
    <?php if (empty($logs)): ?>
        <div class="notice notice-info">
            <p><?php _e('No email logs found.', 'insurance-crm-smtp'); ?></p>
        </div>
    <?php else: ?>
        <div class="icsm-logs-table">
            <table class="widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php _e('Date/Time', 'insurance-crm-smtp'); ?></th>
                        <th><?php _e('To', 'insurance-crm-smtp'); ?></th>
                        <th><?php _e('Subject', 'insurance-crm-smtp'); ?></th>
                        <th><?php _e('Status', 'insurance-crm-smtp'); ?></th>
                        <th><?php _e('SMTP Server', 'insurance-crm-smtp'); ?></th>
                        <th><?php _e('Error', 'insurance-crm-smtp'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logs as $log): ?>
                        <tr>
                            <td><?php echo esc_html(mysql2date('Y-m-d H:i:s', $log->timestamp)); ?></td>
                            <td>
                                <div class="email-cell" title="<?php echo esc_attr($log->to_email); ?>">
                                    <?php echo esc_html(strlen($log->to_email) > 30 ? substr($log->to_email, 0, 30) . '...' : $log->to_email); ?>
                                </div>
                            </td>
                            <td>
                                <div class="subject-cell" title="<?php echo esc_attr($log->subject); ?>">
                                    <?php echo esc_html(strlen($log->subject) > 40 ? substr($log->subject, 0, 40) . '...' : $log->subject); ?>
                                </div>
                            </td>
                            <td>
                                <span class="status-badge status-<?php echo esc_attr($log->status); ?>">
                                    <?php echo esc_html(ucfirst($log->status)); ?>
                                </span>
                                <?php if ($log->retry_count > 0): ?>
                                    <small>(<?php printf(__('Retries: %d', 'insurance-crm-smtp'), $log->retry_count); ?>)</small>
                                <?php endif; ?>
                            </td>
                            <td><?php echo esc_html($log->smtp_host . ':' . $log->smtp_port); ?></td>
                            <td>
                                <?php if (!empty($log->error_message)): ?>
                                    <div class="error-message" title="<?php echo esc_attr($log->error_message); ?>">
                                        <?php echo esc_html(strlen($log->error_message) > 50 ? substr($log->error_message, 0, 50) . '...' : $log->error_message); ?>
                                    </div>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <?php if ($total_pages > 1): ?>
            <div class="tablenav bottom">
                <div class="tablenav-pages">
                    <?php
                    $page_links = paginate_links(array(
                        'base' => add_query_arg('paged', '%#%'),
                        'format' => '',
                        'prev_text' => __('&laquo;'),
                        'next_text' => __('&raquo;'),
                        'total' => $total_pages,
                        'current' => $page
                    ));
                    echo $page_links;
                    ?>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<style>
.icsm-stats-row {
    display: flex;
    gap: 20px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}

.icsm-stat-card {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    padding: 20px;
    text-align: center;
    min-width: 120px;
    flex: 1;
}

.icsm-stat-card h3 {
    margin: 0 0 10px 0;
    color: #50575e;
}

.stat-number {
    font-size: 24px;
    font-weight: 600;
    color: #1d2327;
}

.stat-success .stat-number {
    color: #00a32a;
}

.stat-error .stat-number {
    color: #d63638;
}

.stat-pending .stat-number {
    color: #dba617;
}

.icsm-logs-table {
    margin-top: 20px;
}

.status-badge {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
}

.status-sent {
    background: #d1e7dd;
    color: #0f5132;
}

.status-failed {
    background: #f8d7da;
    color: #721c24;
}

.status-pending {
    background: #fff3cd;
    color: #856404;
}

.email-cell, .subject-cell, .error-message {
    max-width: 200px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
</style>