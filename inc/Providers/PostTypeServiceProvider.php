<?php
declare(strict_types=1);

namespace ModernArchitect\Providers;

/**
 * Post Type Service Provider
 * 
 * Registers custom post types using OOP approach.
 * Easily extensible through filters.
 * 
 * @package ModernArchitect\Providers
 * @author Your Name
 * @license GPL-2.0-or-later
 */
class PostTypeServiceProvider
{
    /**
     * Register all post type hooks
     * 
     * @return void
     */
    public function register(): void
    {
        add_action('init', [$this, 'registerPostTypes']);
        add_filter('post_type_link', [$this, 'customPostTypeLink'], 10, 3);
    }

    /**
     * Register custom post types
     * 
     * Uses filter to allow child themes/plugins to modify args.
     * 
     * @return void
     */
    public function registerPostTypes(): void
    {
        $post_types = $this->getPostTypes();
        
        foreach ($post_types as $post_type => $args) {
            // Allow filtering of post type args
            $filtered_args = apply_filters(
                "modern_architect_{$post_type}_args",
                $args['args'],
                $post_type
            );
            
            if (!post_type_exists($post_type)) {
                register_post_type($post_type, $filtered_args);
            }
        }
    }

    /**
     * Get array of post types to register
     * 
     * @return array<string, array{labels: array<string, string>, args: array<string, mixed>}>
     */
    private function getPostTypes(): array
    {
        return [
            'portfolio' => [
                'labels' => [
                    'name'               => __('Portfolios', 'modern-architect'),
                    'singular_name'      => __('Portfolio', 'modern-architect'),
                    'menu_name'          => __('Portfolio', 'modern-architect'),
                    'add_new'            => __('Add New', 'modern-architect'),
                    'add_new_item'       => __('Add New Portfolio Item', 'modern-architect'),
                    'edit_item'          => __('Edit Portfolio Item', 'modern-architect'),
                    'new_item'           => __('New Portfolio Item', 'modern-architect'),
                    'view_item'          => __('View Portfolio Item', 'modern-architect'),
                    'search_items'       => __('Search Portfolio', 'modern-architect'),
                    'not_found'          => __('No portfolio items found', 'modern-architect'),
                    'not_found_in_trash' => __('No portfolio items found in trash', 'modern-architect'),
                ],
                'args'   => [
                    'public'              => true,
                    'has_archive'         => true,
                    'show_in_menu'        => true,
                    'show_in_rest'        => true, // Enable Gutenberg
                    'menu_icon'           => 'dashicons-portfolio',
                    'supports'            => ['title', 'editor', 'thumbnail', 'excerpt'],
                    'rewrite'             => ['slug' => 'portfolio', 'with_front' => false],
                    'menu_position'       => 5,
                    'show_ui'             => true,
                    'capability_type'     => 'post',
                    'map_meta_cap'        => true,
                    'hierarchical'        => false,
                    'query_var'           => true,
                ],
            ],
            'testimonial' => [
                'labels' => [
                    'name'               => __('Testimonials', 'modern-architect'),
                    'singular_name'      => __('Testimonial', 'modern-architect'),
                    'menu_name'          => __('Testimonials', 'modern-architect'),
                    'add_new'            => __('Add New', 'modern-architect'),
                    'add_new_item'       => __('Add New Testimonial', 'modern-architect'),
                    'edit_item'          => __('Edit Testimonial', 'modern-architect'),
                    'new_item'           => __('New Testimonial', 'modern-architect'),
                    'view_item'          => __('View Testimonial', 'modern-architect'),
                    'search_items'       => __('Search Testimonials', 'modern-architect'),
                    'not_found'          => __('No testimonials found', 'modern-architect'),
                    'not_found_in_trash' => __('No testimonials found in trash', 'modern-architect'),
                ],
                'args'   => [
                    'public'              => false,
                    'has_archive'         => false,
                    'show_in_menu'        => true,
                    'show_in_rest'        => true,
                    'menu_icon'           => 'dashicons-format-quote',
                    'supports'            => ['title', 'editor', 'thumbnail'],
                    'show_ui'             => true,
                    'capability_type'     => 'post',
                    'map_meta_cap'        => true,
                    'hierarchical'        => false,
                    'query_var'           => true,
                ],
            ],
        ];
    }

    /**
     * Custom post type permalink filter
     * 
     * @param string $permalink Post permalink
     * @param \WP_Post $post Post object
     * @param bool $leavename Whether to keep post name
     * @return string Modified permalink
     */
    public function customPostTypeLink(string $permalink, \WP_Post $post, bool $leavename): string
    {
        // Add custom logic for post type URLs if needed
        return $permalink;
    }
}
