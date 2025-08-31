# Insurance CRM SMTP - Installation Guide

## Quick Start

1. **Upload & Activate**
   - Upload the plugin files to `/wp-content/plugins/insurance-crm-smtp/`
   - Activate the plugin in WordPress admin
   - You'll see a setup wizard notification

2. **Run Setup Wizard**
   - Click "run the setup wizard" from the admin notice
   - Or go to WordPress Admin → InsuranceCRM SMTP
   - Follow the 4-step wizard

3. **Choose Provider (Step 2)**
   - Select your email provider (Gmail, Outlook, Yahoo, Yandex)
   - Or choose "Manual Configuration" for custom SMTP

4. **Enter Credentials (Step 3)**
   - SMTP settings will be pre-filled for popular providers
   - Enter your email and password/app password
   - Configure "From" name and email

5. **Test & Complete (Step 4)**
   - Send a test email to verify everything works
   - Complete setup to activate SMTP

## Provider-Specific Setup

### Gmail
1. Enable 2-factor authentication in your Google account
2. Generate an App Password at: https://myaccount.google.com/apppasswords
3. Use your Gmail address as username
4. Use the App Password (not your regular password)

### Outlook/Hotmail
1. Use your full email address as username
2. Use your regular password or generate an App Password
3. Ensure SMTP is enabled in Outlook settings

### Yahoo
1. Enable 2-factor authentication
2. Generate an App Password in Yahoo Account Security
3. Use your Yahoo email as username
4. Use the App Password

### Yandex
1. Use your Yandex email as username
2. Use your regular password or App Password
3. Ensure SMTP access is enabled

## After Setup

### Verify Configuration
- Go to **Test Email** page
- Send a test email to yourself
- Check **Logs** page for delivery status

### Monitor Performance
- View **Logs** for email delivery statistics
- Check **Queue** page for bulk email processing
- Monitor rate limiting status

### Troubleshooting
- Check logs for error messages
- Use connection test feature
- Verify SMTP settings match your provider
- Ensure SMTP is enabled in plugin settings

## Security Notes

- All passwords are encrypted before storage
- Rate limiting prevents spam/abuse
- All forms use WordPress security nonces
- Logs don't contain sensitive information

## Performance Features

- Automatic email queue processing
- Failed email retry with exponential backoff
- Rate limiting to avoid provider limits
- Database optimization and cleanup
- Caching of SMTP settings

## Need Help?

1. Check the **Logs** page for error details
2. Test your configuration on the **Test Email** page
3. Review your email provider's SMTP requirements
4. Verify your credentials are correct