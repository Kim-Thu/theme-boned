<?php
declare(strict_types=1);

namespace ModernArchitect\Providers;

/**
 * Security Service Provider
 * 
 * Implements security measures following OWASP Top 10.
 * Handles nonce verification, input sanitization, and output escaping.
 * 
 * @package ModernArchitect\Providers
 * @author Your Name
 * @license GPL-2.0-or-later
 */
class SecurityServiceProvider
{
    /**
     * Register all security-related hooks
     * 
     * @return void
     */
    public function register(): void
    {
        add_action('init', [$this, 'registerAjaxHandlers']);
        add_filter('wp_kses_allowed_html', [$this, 'allowedHtmlTags'], 10, 2);
        
        // Remove WordPress version for security
        remove_action('wp_head', 'wp_generator');
        remove_action('wp_head', 'wlwmanifest_link');
        remove_action('wp_head', 'wp_shortlink_wp_head');
        
        // Security headers
        add_action('send_headers', [$this, 'addSecurityHeaders']);
    }

    /**
     * Register AJAX handlers with nonce verification
     * 
     * @return void
     */
    public function registerAjaxHandlers(): void
    {
        // Logged-in users
        add_action('wp_ajax_modern_architect_contact', [$this, 'handleContactForm']);
        
        // Non-logged-in users
        add_action('wp_ajax_nopriv_modern_architect_contact', [$this, 'handleContactForm']);
    }

    /**
     * Handle contact form submission with full security
     * 
     * @return void
     */
    public function handleContactForm(): void
    {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field($_POST['nonce']), 'modern_architect_contact_nonce')) {
            wp_send_json_error([
                'message' => __('Security check failed. Please refresh and try again.', 'modern-architect'),
            ], 403);
        }

        // Sanitize all inputs
        $name = isset($_POST['name']) ? sanitize_text_field($_POST['name']) : '';
        $email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
        $message = isset($_POST['message']) ? wp_kses_post($_POST['message']) : '';

        // Validate required fields
        if (empty($name) || empty($email) || empty($message)) {
            wp_send_json_error([
                'message' => __('All fields are required.', 'modern-architect'),
            ], 400);
        }

        // Validate email format
        if (!is_email($email)) {
            wp_send_json_error([
                'message' => __('Please enter a valid email address.', 'modern-architect'),
            ], 400);
        }

        // Rate limiting check (simple implementation)
        $transient_key = 'contact_form_' . md5($email);
        if (get_transient($transient_key)) {
            wp_send_json_error([
                'message' => __('Please wait before submitting another message.', 'modern-architect'),
            ], 429);
        }

        // Process the form (e.g., send email, save to database)
        $success = $this->processContactSubmission($name, $email, $message);

        if ($success) {
            // Set rate limit transient (5 minutes)
            set_transient($transient_key, true, 5 * MINUTE_IN_SECONDS);
            
            wp_send_json_success([
                'message' => __('Thank you! Your message has been sent.', 'modern-architect'),
            ]);
        } else {
            wp_send_json_error([
                'message' => __('An error occurred. Please try again later.', 'modern-architect'),
            ], 500);
        }
    }

    /**
     * Process contact form submission
     * 
     * @param string $name Sender name
     * @param string $email Sender email
     * @param string $message Message content
     * @return bool Success status
     */
    private function processContactSubmission(string $name, string $email, string $message): bool
    {
        $admin_email = get_option('admin_email');
        $site_name = get_bloginfo('name');
        
        $subject = sprintf(
            /* translators: %s: Site name */
            __('[%s] New Contact Form Submission', 'modern-architect'),
            $site_name
        );

        $body = sprintf(
            "Name: %s\nEmail: %s\n\nMessage:\n%s",
            wp_strip_all_tags($name),
            wp_strip_all_tags($email),
            wp_strip_all_tags($message)
        );

        $headers = [
            'Content-Type: text/plain; charset=UTF-8',
            'Reply-To: ' . sanitize_email($email),
        ];

        return wp_mail($admin_email, $subject, $body, $headers);
    }

    /**
     * Add custom allowed HTML tags for wp_kses
     * 
     * @param array<string, array<string, bool|string>> $tags Allowed tags
     * @param string $context Context name
     * @return array<string, array<string, bool|string>> Modified allowed tags
     */
    public function allowedHtmlTags(array $tags, string $context): array
    {
        if ($context === 'modern_architect_content') {
            $tags['button'] = [
                'class'   => true,
                'type'    => true,
                'onclick' => false, // Prevent inline JS
                'data-*'  => true,
                'aria-*'  => true,
            ];
            
            $tags['form'] = [
                'class'  => true,
                'action' => true,
                'method' => true,
                'id'     => true,
            ];
            
            $tags['input'] = [
                'type'        => true,
                'name'        => true,
                'value'       => true,
                'placeholder' => true,
                'class'       => true,
                'required'    => true,
                'disabled'    => true,
                'readonly'    => true,
                'autocomplete'=> true,
            ];
            
            $tags['textarea'] = [
                'name'        => true,
                'class'       => true,
                'rows'        => true,
                'cols'        => true,
                'placeholder' => true,
                'required'    => true,
            ];
        }

        return $tags;
    }

    /**
     * Add security headers to HTTP response
     * 
     * @return void
     */
    public function addSecurityHeaders(): void
    {
        // Prevent clickjacking
        header('X-Frame-Options: SAMEORIGIN');
        
        // XSS Protection
        header('X-XSS-Protection: 1; mode=block');
        
        // Prevent MIME type sniffing
        header('X-Content-Type-Options: nosniff');
        
        // Referrer Policy
        header('Referrer-Policy: strict-origin-when-cross-origin');
        
        // Content Security Policy (adjust as needed)
        header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://www.google-analytics.com; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com; img-src 'self' data: https:;");
    }

    /**
     * Sanitize and validate file upload
     * 
     * @param array<string, mixed> $file File array from $_FILES
     * @param array<string> $allowed_mimes Allowed MIME types
     * @return array{success: bool, message: string, file_path?: string}
     */
    public function handleFileUpload(array $file, array $allowed_mimes = ['image/jpeg', 'image/png', 'image/gif']): array
    {
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            return [
                'success' => false,
                'message' => __('No file uploaded or invalid upload.', 'modern-architect'),
            ];
        }

        // Check file size (max 2MB)
        $max_size = 2 * MB_IN_BYTES;
        if ($file['size'] > $max_size) {
            return [
                'success' => false,
                'message' => __('File size exceeds maximum allowed size.', 'modern-architect'),
            ];
        }

        // Validate MIME type
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime_type = $finfo->file($file['tmp_name']);
        
        if (!in_array($mime_type, $allowed_mimes, true)) {
            return [
                'success' => false,
                'message' => __('File type not allowed.', 'modern-architect'),
            ];
        }

        // Generate safe filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $safe_filename = wp_unique_filename(wp_upload_dir()['path'], 'file-' . time() . '.' . $extension);
        
        // Move uploaded file
        $upload_path = wp_upload_dir()['path'] . '/' . $safe_filename;
        
        if (!move_uploaded_file($file['tmp_name'], $upload_path)) {
            return [
                'success' => false,
                'message' => __('Failed to save uploaded file.', 'modern-architect'),
            ];
        }

        return [
            'success'   => true,
            'message'   => __('File uploaded successfully.', 'modern-architect'),
            'file_path' => $upload_path,
        ];
    }
}
