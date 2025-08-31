<?php
/**
 * Uninstall script for Insurance CRM SMTP
 * Runs when plugin is deleted (not just deactivated)
 */

// If uninstall not called from WordPress, exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Include plugin files to access uninstall method
require_once plugin_dir_path(__FILE__) . 'includes/class-insurance-crm-smtp.php';

// Call the uninstall method
Insurance_CRM_SMTP::uninstall();