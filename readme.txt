=== Insurance CRM SMTP ===
Contributors: benzersizkod
Tags: smtp, email, mail, phpmailer, gmail, outlook, yahoo, yandex
Requires at least: 5.0
Tested up to: 6.4
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Professional SMTP plugin for WordPress with advanced email delivery features, setup wizard, and comprehensive logging.

== Description ==

Insurance CRM SMTP is a powerful WordPress plugin designed to improve your website's email delivery reliability through SMTP configuration. Whether you're running an insurance CRM system or any WordPress site that requires dependable email delivery, this plugin provides all the tools you need.

= Key Features =

* **Setup Wizard**: User-friendly step-by-step configuration
* **Popular Provider Presets**: One-click setup for Gmail, Outlook, Yahoo, and Yandex  
* **Comprehensive Logging**: Track all email attempts with detailed success/failure logs
* **Security**: Encrypted password storage and secure authentication
* **Test Email**: Verify your configuration works before going live
* **Admin Interface**: Beautiful, intuitive admin panels with statistics
* **Performance**: Retry mechanisms and queue support for reliable delivery

= Supported Email Providers =

* Gmail (smtp.gmail.com)
* Outlook/Hotmail (smtp.live.com) 
* Yahoo Mail (smtp.mail.yahoo.com)
* Yandex (smtp.yandex.com)
* Any custom SMTP server

= Premium Features =

* Rate limiting and spam protection
* Queue system for bulk emails
* Advanced retry mechanisms
* Detailed error reporting
* Performance optimization
* Security enhancements

== Installation ==

= Automatic Installation =

1. Login to your WordPress admin panel
2. Navigate to Plugins > Add New
3. Search for "Insurance CRM SMTP"
4. Click "Install Now" and then "Activate"
5. Follow the setup wizard to configure your SMTP settings

= Manual Installation =

1. Download the plugin zip file
2. Upload it to `/wp-content/plugins/insurance-crm-smtp/`
3. Activate the plugin through the 'Plugins' menu
4. Run the setup wizard from the admin notice

= Configuration =

After activation, you'll see an admin notice to run the setup wizard. The wizard will guide you through:

1. **Welcome**: Overview of features
2. **Provider Selection**: Choose your email provider or manual config
3. **SMTP Configuration**: Enter your server details
4. **Test & Complete**: Send a test email and activate

== Frequently Asked Questions ==

= What SMTP providers are supported? =

The plugin supports any SMTP server, with built-in presets for Gmail, Outlook, Yahoo, and Yandex. You can also configure any custom SMTP server manually.

= Do I need special passwords for Gmail? =

Yes, Gmail requires App Passwords when using 2-factor authentication. Regular passwords won't work with SMTP. Generate an App Password in your Google Account settings.

= How do I know if emails are being sent? =

The plugin includes comprehensive logging. Check the "Logs" page in the admin menu to see all email attempts, their status, and any error messages.

= Can I test my configuration? =

Yes! The plugin includes a test email feature. You can send test emails from the "Test Email" page or during the setup wizard.

= Is my password stored securely? =

Absolutely. All passwords are encrypted using WordPress security salts before being stored in the database.

= What if my emails aren't sending? =

1. Check the logs for error messages
2. Use the connection test feature
3. Verify your SMTP settings are correct
4. Ensure SMTP is enabled in the plugin settings

== Screenshots ==

1. Setup wizard welcome screen
2. Email provider selection
3. SMTP configuration page  
4. Admin settings interface
5. Email logs with statistics
6. Test email functionality

== Changelog ==

= 1.0.0 =
* Initial release
* Setup wizard implementation
* Support for Gmail, Outlook, Yahoo, Yandex
* Comprehensive email logging system
* Admin interface with statistics  
* Security features and password encryption
* Test email functionality
* Performance optimizations
* Rate limiting and retry mechanisms

== Upgrade Notice ==

= 1.0.0 =
Initial release of Insurance CRM SMTP with full SMTP functionality and setup wizard.

== Technical Details ==

= Requirements =
* WordPress 5.0+
* PHP 7.4+
* OpenSSL extension
* cURL extension

= Database =
The plugin creates one table: `wp_insurance_crm_smtp_logs` for email logging.

= Security =
* All passwords encrypted with WordPress salts
* Nonce verification on all forms
* Input sanitization and validation
* No sensitive data in logs

= Performance =
* Optimized database queries
* Automatic log cleanup
* Minimal resource usage
* Caching support

== Support ==

For support questions:
1. Check the plugin logs for error details
2. Review the FAQ section
3. Test your SMTP configuration
4. Check your email provider's SMTP requirements

== Privacy ==

This plugin:
* Stores SMTP configuration in your WordPress database
* Logs email attempts locally (no external services)
* Does not send data to third-party services
* Encrypts sensitive information like passwords