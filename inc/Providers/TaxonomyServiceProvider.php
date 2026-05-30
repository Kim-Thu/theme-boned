<?php
declare(strict_types=1);

namespace ModernArchitect\Providers;

/**
 * Taxonomy Service Provider
 * 
 * Registers custom taxonomies.
 * 
 * @package ModernArchitect\Providers
 * @author Your Name
 * @license GPL-2.0-or-later
 */
class TaxonomyServiceProvider
{
    /**
     * Register all taxonomy-related hooks
     * 
     * @return void
     */
    public function register(): void
    {
        add_action('init', [$this, 'registerTaxonomies']);
    }

    /**
     * Register custom taxonomies
     * 
     * @return void
     */
    public function registerTaxonomies(): void
    {
        $taxonomies = $this->getTaxonomies();
        
        foreach ($taxonomies as $taxonomy => $args) {
            if (!taxonomy_exists($taxonomy)) {
                register_taxonomy($taxonomy, $args['object_type'], $args['args']);
            }
        }
    }

    /**
     * Get array of taxonomies to register
     * 
     * @return array<string, array{object_type: string[], args: array<string, mixed>}>
     */
    private function getTaxonomies(): array
    {
        return [
            'project_category' => [
                'object_type' => ['portfolio'],
                'args'        => [
                    'labels' => [
                        'name'              => __('Project Categories', 'modern-architect'),
                        'singular_name'     => __('Project Category', 'modern-architect'),
                        'search_items'      => __('Search Categories', 'modern-architect'),
                        'all_items'         => __('All Categories', 'modern-architect'),
                        'edit_item'         => __('Edit Category', 'modern-architect'),
                        'update_item'       => __('Update Category', 'modern-architect'),
                        'add_new_item'      => __('Add New Category', 'modern-architect'),
                        'new_item_name'     => __('New Category Name', 'modern-architect'),
                        'parent_item'       => __('Parent Category', 'modern-architect'),
                        'parent_item_colon' => __('Parent Category:', 'modern-architect'),
                    ],
                    'hierarchical'      => true,
                    'public'            => true,
                    'show_in_rest'      => true,
                    'show_admin_column' => true,
                    'rewrite'           => ['slug' => 'project-category'],
                ],
            ],
            'testimonial_category' => [
                'object_type' => ['testimonial'],
                'args'        => [
                    'labels' => [
                        'name'          => __('Testimonial Categories', 'modern-architect'),
                        'singular_name' => __('Testimonial Category', 'modern-architect'),
                    ],
                    'hierarchical'      => false,
                    'public'            => true,
                    'show_in_rest'      => true,
                    'show_admin_column' => true,
                ],
            ],
        ];
    }
}
