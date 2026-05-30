<?php
declare(strict_types=1);

namespace ModernArchitect\Providers;

/**
 * Menu Service Provider
 * 
 * Registers navigation menus and walker classes.
 * 
 * @package ModernArchitect\Providers
 * @author Your Name
 * @license GPL-2.0-or-later
 */
class MenuServiceProvider
{
    /**
     * Register all menu-related hooks
     * 
     * @return void
     */
    public function register(): void
    {
        add_action('init', [$this, 'registerMenus']);
        add_filter('nav_menu_css_class', [$this, 'addMenuClasses'], 10, 2);
    }

    /**
     * Register navigation menus
     * 
     * @return void
     */
    public function registerMenus(): void
    {
        $locations = [
            'primary'   => __('Primary Menu', 'modern-architect'),
            'footer'    => __('Footer Menu', 'modern-architect'),
            'mobile'    => __('Mobile Menu', 'modern-architect'),
            'social'    => __('Social Links', 'modern-architect'),
        ];

        foreach ($locations as $location => $description) {
            register_nav_menu($location, $description);
        }
    }

    /**
     * Add custom CSS classes to menu items
     * 
     * @param array<string> $classes Existing classes
     * @param \WP_Post $item Menu item object
     * @return array<string> Modified classes
     */
    public function addMenuClasses(array $classes, \WP_Post $item): array
    {
        // Add BEM-style class
        $classes[] = 'nav-menu__item';
        
        // Add current page indicator
        if (in_array('current-menu-item', $classes, true)) {
            $classes[] = 'nav-menu__item--active';
        }
        
        return $classes;
    }
}
