<?php
/**
 * Modern Architect Theme
 *
 * @package           ModernArchitect
 * @author            Your Name
 * @copyright         2024 Your Company
 * @license           GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       Modern Architect Theme
 * Plugin URI:        https://example.com/modern-architect
 * Description:       A modern, performant WordPress theme built with OOP, SOLID principles, and CSS Variables.
 * Version:           1.0.0
 * Requires at least: 6.4
 * Requires PHP:      8.2
 * Author:            Your Name
 * Author URI:        https://example.com
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       modern-architect
 * Domain Path:       /languages
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

define('MODERN_ARCHITECT_VERSION', '1.0.0');
define('MODERN_ARCHITECT_DIR', __DIR__);
define('MODERN_ARCHITECT_URI', get_template_directory_uri());

/**
 * Autoloader - PSR-4 compliant
 * 
 * Loads classes following PSR-4 standard.
 * Namespace prefix: ModernArchitect\
 * Base directory: /inc/
 */
spl_autoload_register(function (string $class): void {
    $prefix = 'ModernArchitect\\';
    $base_dir = MODERN_ARCHITECT_DIR . '/inc/';
    
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    
    if (file_exists($file)) {
        require $file;
    }
});

/**
 * Initialize Service Container
 * 
 * Central hub for dependency injection.
 * All services are registered here and can be accessed globally.
 */
$container = new \ModernArchitect\Core\ServiceContainer();

// Register Core Services
$container->register('asset_service', function ($container) {
    return new \ModernArchitect\Providers\AssetServiceProvider();
});

$container->register('post_type_service', function ($container) {
    return new \ModernArchitect\Providers\PostTypeServiceProvider();
});

$container->register('taxonomy_service', function ($container) {
    return new \ModernArchitect\Providers\TaxonomyServiceProvider();
});

$container->register('menu_service', function ($container) {
    return new \ModernArchitect\Providers\MenuServiceProvider();
});

$container->register('security_service', function ($container) {
    return new \ModernArchitect\Providers\SecurityServiceProvider();
});

/**
 * Bootstrap Theme
 * 
 * Initialize all services after theme setup.
 * Priority 10 ensures default WP features are loaded first.
 */
add_action('after_setup_theme', function() use ($container): void {
    
    // Theme Support
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('automatic-feed-links');
    add_theme_support('html5', [
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
        'style',
        'script',
    ]);
    add_theme_support('customize-selective-refresh-widgets');
    add_theme_support('responsive-embeds');
    add_theme_support('align-wide');
    add_theme_support('wp-block-styles');
    add_theme_support('editor-styles');
    
    // Custom Logo
    add_theme_support('custom-logo', [
        'height'      => 60,
        'width'       => 200,
        'flex-height' => true,
        'flex-width'  => true,
    ]);
    
    // Custom Background
    add_theme_support('custom-background', [
        'default-color' => 'ffffff',
    ]);
    
    // Editor Color Palette (matches CSS variables)
    add_theme_support('editor-color-palette', [
        [
            'name'  => __('Primary', 'modern-architect'),
            'slug'  => 'primary',
            'color' => '#3b82f6',
        ],
        [
            'name'  => __('Secondary', 'modern-architect'),
            'slug'  => 'secondary',
            'color' => '#64748b',
        ],
        [
            'name'  => __('Accent', 'modern-architect'),
            'slug'  => 'accent',
            'color' => '#f59e0b',
        ],
        [
            'name'  => __('Background', 'modern-architect'),
            'slug'  => 'background',
            'color' => '#ffffff',
        ],
        [
            'name'  => __('Text', 'modern-architect'),
            'slug'  => 'text',
            'color' => '#0f172a',
        ],
    ]);
    
    // Boot all registered services
    $container->get('asset_service')->register();
    $container->get('post_type_service')->register();
    $container->get('taxonomy_service')->register();
    $container->get('menu_service')->register();
    $container->get('security_service')->register();
    
}, 10);

/**
 * Load Text Domain for i18n
 */
add_action('init', function(): void {
    load_theme_textdomain('modern-architect', MODERN_ARCHITECT_DIR . '/languages');
});

/**
 * Helper function to access service container
 * 
 * @return \ModernArchitect\Core\ServiceContainer
 */
function modern_architect_container(): \ModernArchitect\Core\ServiceContainer {
    global $container;
    return $container;
}

/**
 * Helper function to get template part with security
 * 
 * @param string $slug Template slug
 * @param string|null $name Optional template name
 * @param array $args Arguments to pass to template
 * @return void
 */
function modern_architect_get_template_part(string $slug, ?string $name = null, array $args = []): void {
    $safe_slug = sanitize_key($slug);
    $safe_name = $name ? sanitize_key($name) : null;
    
    $template = $safe_name ? "{$safe_slug}-{$safe_name}.php" : "{$safe_slug}.php";
    $template_path = MODERN_ARCHITECT_DIR . "/templates/components/{$template}";
    
    if (file_exists($template_path)) {
        extract($args, EXTR_SKIP);
        include $template_path;
    }
}
