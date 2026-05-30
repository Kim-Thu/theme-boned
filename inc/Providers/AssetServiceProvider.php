<?php
declare(strict_types=1);

namespace ModernArchitect\Providers;

/**
 * Asset Service Provider
 * 
 * Handles registration and enqueueing of CSS/JS assets.
 * Implements conditional loading for performance optimization.
 * 
 * @package ModernArchitect\Providers
 * @author Your Name
 * @license GPL-2.0-or-later
 */
class AssetServiceProvider
{
    /**
     * Theme version
     */
    private const VERSION = MODERN_ARCHITECT_VERSION;

    /**
     * Register all asset-related hooks
     * 
     * @return void
     */
    public function register(): void
    {
        add_action('wp_enqueue_scripts', [$this, 'enqueueAssets']);
        add_filter('script_loader_tag', [$this, 'addScriptStrategy'], 10, 3);
    }

    /**
     * Enqueue all theme assets
     * 
     * Uses conditional loading to only load scripts when needed.
     * 
     * @return void
     */
    public function enqueueAssets(): void
    {
        $suffix = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';
        
        // Main stylesheet with CSS Variables
        wp_register_style(
            'modern-architect-variables',
            get_template_directory_uri() . "/assets/css/01-settings/variables{$suffix}.css",
            [],
            self::VERSION,
            'all'
        );
        
        wp_register_style(
            'modern-architect-components',
            get_template_directory_uri() . "/assets/css/05-components/components{$suffix}.css",
            ['modern-architect-variables'],
            self::VERSION,
            'all'
        );
        
        wp_register_style(
            'modern-architect-style',
            get_stylesheet_uri(),
            ['modern-architect-components'],
            self::VERSION,
            'all'
        );
        
        wp_enqueue_style('modern-architect-style');
        
        // Main JavaScript
        wp_register_script(
            'modern-architect-main',
            get_template_directory_uri() . "/assets/js/main{$suffix}.js",
            [],
            self::VERSION,
            [
                'in_footer' => true,
                'strategy'  => 'defer',
            ]
        );
        
        // Conditional: Contact form script only on contact page
        if (is_page('contact') || is_page_template('template-contact.php')) {
            wp_enqueue_script(
                'modern-architect-contact',
                get_template_directory_uri() . "/assets/js/contact{$suffix}.js",
                ['modern-architect-main'],
                self::VERSION,
                [
                    'in_footer' => true,
                    'strategy'  => 'defer',
                ]
            );
            
            wp_localize_script('modern-architect-contact', 'contactConfig', [
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce'   => wp_create_nonce('modern_architect_contact_nonce'),
                'restUrl' => rest_url('modern-architect/v1/contact'),
            ]);
        }
        
        // Conditional: Comment reply script
        if (is_singular() && comments_open() && get_option('thread_comments')) {
            wp_enqueue_script('comment-reply');
        }
        
        wp_enqueue_script('modern-architect-main');
        
        // Global config for JavaScript
        wp_localize_script('modern-architect-main', 'themeConfig', [
            'ajaxUrl'      => admin_url('admin-ajax.php'),
            'nonce'        => wp_create_nonce('modern_architect_nonce'),
            'restUrl'      => rest_url('modern-architect/v1'),
            'homeUrl'      => home_url('/'),
            'siteName'     => get_bloginfo('name'),
            'breakpoints'  => [
                'sm' => 640,
                'md' => 768,
                'lg' => 1024,
                'xl' => 1280,
            ],
            'i18n'         => [
                'loading'    => __('Loading...', 'modern-architect'),
                'error'      => __('An error occurred', 'modern-architect'),
                'success'    => __('Success!', 'modern-architect'),
            ],
        ]);
    }

    /**
     * Add defer/async strategy to script tags
     * 
     * @param string $tag Script tag HTML
     * @param string $handle Script handle
     * @param string $src Script source URL
     * @return string Modified script tag
     */
    public function addScriptStrategy(string $tag, string $handle, string $src): string
    {
        $defer_scripts = ['modern-architect-main', 'modern-architect-contact'];
        
        if (in_array($handle, $defer_scripts, true)) {
            if (strpos($tag, 'defer') === false) {
                $tag = str_replace(' src', ' defer="defer" src', $tag);
            }
        }
        
        return $tag;
    }

    /**
     * Preload critical assets
     * 
     * @return void
     */
    public function preloadCriticalAssets(): void
    {
        // Preload main font
        echo '<link rel="preload" href="' . esc_url(get_template_directory_uri()) . '/assets/fonts/inter-var.woff2" as="font" type="font/woff2" crossorigin>';
    }
}
