# Insurance CRM SMTP WordPress Plugin

A comprehensive SMTP plugin for WordPress with advanced email delivery features, designed specifically for insurance CRM systems but suitable for any WordPress site requiring reliable email delivery.

## Features

### 🚀 Quick Setup
- **Setup Wizard**: User-friendly step-by-step configuration process
- **Popular Provider Presets**: One-click setup for Gmail, Outlook, Yahoo, and Yandex
- **Test Email**: Verify configuration with test email functionality

### 📧 SMTP Configuration
- Support for all SMTP servers with manual configuration
- Multiple security protocols (None, SSL, TLS, STARTTLS)
- Encrypted password storage for security
- Custom "From" email and name settings

### 📊 Comprehensive Logging
- Detailed logging of all email attempts
- Success/failure tracking with error messages
- Retry count monitoring
- Admin interface for log viewing and management

### 🔒 Security Features
- Encrypted password storage using WordPress salt
- Rate limiting to prevent spam
- Secure nonce verification for all forms
- Input sanitization and validation

### ⚡ Performance Optimizations
- Retry mechanism for failed emails
- Queue system support for high volume
- Database optimization for log storage
- Automatic log cleanup

### 🎛️ Admin Interface
- Dedicated admin menu with subpages
- Real-time SMTP status indicator
- Connection testing tools
- Email log viewer with statistics

## Installation

1. Upload the plugin files to `/wp-content/plugins/insurance-crm-smtp/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Run the setup wizard from the admin notice or navigate to "InsuranceCRM SMTP" in the admin menu
4. Configure your SMTP settings
5. Send a test email to verify everything works

## Quick Configuration

### Gmail Setup
1. Select "Gmail" in the provider dropdown
2. Enter your Gmail address as username
3. Use an App Password (not your regular password)
4. Enable 2-factor authentication in your Google account
5. Generate an App Password at https://myaccount.google.com/apppasswords

### Outlook/Hotmail Setup
1. Select "Outlook/Hotmail" in the provider dropdown  
2. Enter your full email address as username
3. Use your regular password or App Password
4. Ensure SMTP is enabled in your Outlook settings

### Yahoo Setup
1. Select "Yahoo" in the provider dropdown
2. Enter your Yahoo email as username
3. Generate and use an App Password
4. Enable 2-factor authentication if not already enabled

### Yandex Setup
1. Select "Yandex" in the provider dropdown
2. Enter your Yandex email as username
3. Use your regular password or App Password
4. Ensure SMTP access is enabled in Yandex settings

## Database Tables

The plugin creates the following database table:

### wp_insurance_crm_smtp_logs
- `id` - Primary key
- `timestamp` - When the email was attempted
- `to_email` - Recipient email address(es)
- `from_email` - Sender email address
- `subject` - Email subject
- `message` - Email content (truncated)
- `status` - sent, failed, pending
- `smtp_host` - SMTP server used
- `smtp_port` - SMTP port used
- `error_message` - Error details if failed
- `retry_count` - Number of retry attempts
- `sent_at` - When successfully sent

## Hooks and Filters

The plugin provides several WordPress hooks:

### Actions
- `icsm_email_sent` - Fired when email is successfully sent
- `icsm_email_failed` - Fired when email fails to send
- `icsm_settings_updated` - Fired when SMTP settings are updated

### Filters
- `icsm_smtp_settings` - Modify SMTP settings before use
- `icsm_log_retention_days` - Change log retention period (default: 30 days)
- `icsm_max_retries` - Maximum retry attempts (default: 3)

## Troubleshooting

### Common Issues

**Connection Failed**
- Verify SMTP host and port are correct
- Check that security protocol matches your provider
- Ensure username and password are correct
- Try disabling antivirus/firewall temporarily

**Authentication Failed**
- Use App Passwords for Gmail/Yahoo (not regular passwords)
- Enable 2-factor authentication if required
- Check if SMTP access is enabled in your email account

**Emails Not Sending**
- Verify SMTP is enabled in plugin settings
- Check the logs for error messages
- Test with a different email provider
- Ensure WordPress can make outbound connections

**SSL/TLS Errors**
- Try different security protocols (TLS vs SSL)
- Update PHP to latest version
- Check if OpenSSL is enabled on server

### Debug Mode

To enable debug mode:
1. Add `define('WP_DEBUG', true);` to wp-config.php
2. Add `define('WP_DEBUG_LOG', true);` to wp-config.php
3. Check `/wp-content/debug.log` for detailed error messages

## Requirements

- WordPress 5.0 or higher
- PHP 7.4 or higher
- OpenSSL extension for encrypted passwords
- cURL extension for SMTP connections

## Support

For support and feature requests:
1. Check the plugin logs for error details
2. Verify your SMTP provider settings
3. Test with the built-in connection tester
4. Review the troubleshooting section above

## Security

- All passwords are encrypted using WordPress salts
- Input sanitization on all forms
- Nonce verification for security
- No sensitive data logged in plain text
- Regular security updates and patches

## Performance

- Optimized database queries with indexes
- Automatic log cleanup to prevent bloat
- Minimal resource usage
- Caching of SMTP settings
- Queue support for bulk emails

## License

This plugin is licensed under GPL v2 or later.

## Changelog

### Version 1.0.0
- Initial release
- Setup wizard implementation
- SMTP configuration with popular providers
- Comprehensive logging system
- Admin interface with statistics
- Security features and password encryption
- Performance optimizations
- Test email functionality