/**
 * Insurance CRM SMTP Admin JavaScript
 */

jQuery(document).ready(function($) {
    'use strict';
    
    // Provider preset handler
    $('#icsm_provider_preset').change(function() {
        const provider = $(this).val();
        
        if (provider !== 'manual') {
            // Provider presets are loaded via PHP, this handles the UI updates
            loadProviderSettings(provider);
        } else {
            // Clear fields for manual configuration
            $('#icsm_smtp_host').val('');
            $('#icsm_smtp_port').val('587');
            $('#icsm_smtp_security').val('tls');
        }
    });
    
    // Connection test handler
    $(document).on('click', 'input[name="test_connection"]', function(e) {
        e.preventDefault();
        
        const $button = $(this);
        const $form = $button.closest('form');
        const originalText = $button.val();
        
        // Validate required fields
        const host = $('#icsm_smtp_host').val();
        const port = $('#icsm_smtp_port').val();
        const username = $('#icsm_smtp_username').val();
        const password = $('#icsm_smtp_password').val();
        
        if (!host || !port || !username || !password) {
            alert(icsm_ajax.strings.connection_failed + ': Please fill in all required fields.');
            return;
        }
        
        // Update button state
        $button.val(icsm_ajax.strings.testing_connection);
        $button.prop('disabled', true);
        $form.addClass('icsm-loading');
        
        // Perform AJAX test (if implemented) or submit form
        // For now, we'll submit the form as it handles the test
        $form.append('<input type="hidden" name="ajax_test" value="1">');
        
        // Reset button after delay
        setTimeout(function() {
            $button.val(originalText);
            $button.prop('disabled', false);
            $form.removeClass('icsm-loading');
        }, 3000);
    });
    
    // Auto-save settings on change (optional)
    let saveTimeout;
    $('.form-table input, .form-table select, .form-table textarea').on('change', function() {
        // Clear previous timeout
        if (saveTimeout) {
            clearTimeout(saveTimeout);
        }
        
        // Set timeout for auto-save (optional feature)
        saveTimeout = setTimeout(function() {
            // Auto-save could be implemented here
            // showNotice('Settings auto-saved', 'success');
        }, 2000);
    });
    
    // Log table row click handler for details
    $('.icsm-logs-table tbody tr').on('click', function() {
        $(this).toggleClass('selected');
        // Could implement detailed view modal here
    });
    
    // Clear logs confirmation
    $(document).on('click', '.clear-logs', function(e) {
        if (!confirm('Are you sure you want to clear all logs? This action cannot be undone.')) {
            e.preventDefault();
        }
    });
    
    // Wizard navigation enhancement
    $('.wizard-navigation .button-primary').on('click', function() {
        const $button = $(this);
        const $form = $button.closest('form');
        
        // Add loading spinner
        $button.prop('disabled', true);
        $button.html('<span class="icsm-spinner"></span>' + $button.text());
        
        // Allow form to submit naturally
        setTimeout(function() {
            $form.submit();
        }, 100);
    });
    
    // Email validation helper
    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }
    
    // Test email form validation
    $('#test_email').on('blur', function() {
        const email = $(this).val();
        if (email && !isValidEmail(email)) {
            $(this).addClass('error');
            showNotice('Please enter a valid email address', 'error');
        } else {
            $(this).removeClass('error');
        }
    });
    
    // Provider settings loader
    function loadProviderSettings(provider) {
        // This would be populated from PHP localized data
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
            $('#icsm_smtp_host').val(settings.host);
            $('#icsm_smtp_port').val(settings.port);
            $('#icsm_smtp_security').val(settings.security);
            
            // Highlight changed fields
            $('#icsm_smtp_host, #icsm_smtp_port, #icsm_smtp_security').addClass('updated-field');
            setTimeout(function() {
                $('.updated-field').removeClass('updated-field');
            }, 2000);
        }
    }
    
    // Show admin notice
    function showNotice(message, type = 'info') {
        const noticeClass = 'notice notice-' + type + ' is-dismissible';
        const $notice = $('<div class="' + noticeClass + '"><p>' + message + '</p></div>');
        
        $('.wrap h1').after($notice);
        
        // Auto-dismiss after 5 seconds
        setTimeout(function() {
            $notice.fadeOut(function() {
                $(this).remove();
            });
        }, 5000);
    }
    
    // Enhanced form validation
    function validateSMTPSettings() {
        let isValid = true;
        const requiredFields = [
            { field: '#icsm_smtp_host', name: 'SMTP Host' },
            { field: '#icsm_smtp_port', name: 'SMTP Port' },
            { field: '#icsm_smtp_username', name: 'Username' },
            { field: '#icsm_smtp_password', name: 'Password' }
        ];
        
        requiredFields.forEach(function(item) {
            const $field = $(item.field);
            const value = $field.val();
            
            if (!value || value.trim() === '') {
                $field.addClass('error');
                isValid = false;
                showNotice(item.name + ' is required', 'error');
            } else {
                $field.removeClass('error');
            }
        });
        
        // Validate email
        const fromEmail = $('#icsm_from_email').val();
        if (fromEmail && !isValidEmail(fromEmail)) {
            $('#icsm_from_email').addClass('error');
            isValid = false;
            showNotice('From Email must be a valid email address', 'error');
        }
        
        // Validate port number
        const port = $('#icsm_smtp_port').val();
        if (port && (isNaN(port) || port < 1 || port > 65535)) {
            $('#icsm_smtp_port').addClass('error');
            isValid = false;
            showNotice('SMTP Port must be a valid port number (1-65535)', 'error');
        }
        
        return isValid;
    }
    
    // Form submission validation
    $('form').on('submit', function(e) {
        if ($(this).find('#icsm_smtp_host').length > 0) {
            // This is the settings form
            if (!validateSMTPSettings()) {
                e.preventDefault();
                return false;
            }
        }
        
        // Wizard step 2 provider selection validation
        if ($(this).find('input[name="provider"]').length > 0) {
            const selectedProvider = $(this).find('input[name="provider"]:checked').val();
            if (!selectedProvider) {
                e.preventDefault();
                showNotice('Please select an email provider to continue.', 'error');
                return false;
            }
        }
    });
    
    // Initialize tooltips if available
    if (typeof tippy !== 'undefined') {
        tippy('[data-tippy-content]', {
            theme: 'light-border',
            placement: 'top'
        });
    }
    
    // Keyboard shortcuts
    $(document).on('keydown', function(e) {
        // Ctrl/Cmd + S to save settings
        if ((e.ctrlKey || e.metaKey) && e.key === 's') {
            e.preventDefault();
            $('input[name="submit"]').click();
        }
    });
    
    // Add CSS for error states
    $('<style>')
        .prop('type', 'text/css')
        .html(`
            .error { 
                border-color: #d63638 !important; 
                box-shadow: 0 0 0 1px #d63638 !important;
            }
            .updated-field {
                border-color: #00a32a !important;
                box-shadow: 0 0 0 1px #00a32a !important;
                transition: border-color 0.3s ease, box-shadow 0.3s ease;
            }
            .selected {
                background-color: #f0f6fc !important;
            }
        `)
        .appendTo('head');
});