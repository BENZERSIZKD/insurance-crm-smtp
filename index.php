<?php
/**
 * Plugin Name: Insurance CRM SMTP
 * Plugin URI: https://github.com/benzersizkod/insurance-crm-smtp
 * Description: Professional SMTP plugin for WordPress with advanced features for email delivery management
 * Version: 1.0.0
 * Author: BenzerSizKod
 * License: GPL v2 or later
 * Text Domain: insurance-crm-smtp
 * Domain Path: /languages
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('ICSM_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('ICSM_PLUGIN_URL', plugin_dir_url(__FILE__));
define('ICSM_VERSION', '1.0.0');
define('ICSM_DB_VERSION', '1.0');

// Include required files
require_once ICSM_PLUGIN_DIR . 'includes/class-insurance-crm-smtp.php';
require_once ICSM_PLUGIN_DIR . 'includes/class-smtp-mailer.php';
require_once ICSM_PLUGIN_DIR . 'includes/class-admin.php';
require_once ICSM_PLUGIN_DIR . 'includes/class-setup-wizard.php';
require_once ICSM_PLUGIN_DIR . 'includes/class-logger.php';
require_once ICSM_PLUGIN_DIR . 'includes/class-database.php';

// Initialize the plugin
function insurance_crm_smtp_init() {
    $plugin = Insurance_CRM_SMTP::get_instance();
    $plugin->init();
    
    // Initialize setup wizard
    Insurance_CRM_SMTP_Setup_Wizard::init();
    
    // Enqueue admin scripts
    add_action('admin_enqueue_scripts', array('Insurance_CRM_SMTP_Admin', 'enqueue_admin_scripts'));
}

// Hook into WordPress
add_action('plugins_loaded', 'insurance_crm_smtp_init');

// Activation hook
register_activation_hook(__FILE__, array('Insurance_CRM_SMTP_Database', 'create_tables'));

// Deactivation hook
register_deactivation_hook(__FILE__, array('Insurance_CRM_SMTP', 'deactivate'));

// Uninstall hook
register_uninstall_hook(__FILE__, array('Insurance_CRM_SMTP', 'uninstall'));
