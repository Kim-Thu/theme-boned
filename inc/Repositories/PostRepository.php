<?php
declare(strict_types=1);

namespace ModernArchitect\Repositories;

use WP_Query;
use WP_Post;

/**
 * Post Repository
 * 
 * Handles database queries for posts with caching.
 * Implements Repository pattern for data access abstraction.
 * 
 * @package ModernArchitect\Repositories
 * @author Your Name
 * @license GPL-2.0-or-later
 */
class PostRepository
{
    /**
     * Cache group name
     */
    private const CACHE_GROUP = 'theme_posts';

    /**
     * Get recent posts with caching
     * 
     * @param int $count Number of posts to retrieve
     * @param string $post_type Post type to query
     * @return array<WP_Post> Array of post objects
     */
    public function getRecentPosts(int $count = 5, string $post_type = 'post'): array
    {
        $cache_key = sprintf('recent_posts_%s_%d', $post_type, $count);
        $cached = wp_cache_get($cache_key, self::CACHE_GROUP);

        if (false !== $cached) {
            return $cached;
        }

        $args = [
            'post_type'      => $post_type,
            'posts_per_page' => $count,
            'post_status'    => 'publish',
            'orderby'        => 'date',
            'order'          => 'DESC',
            'no_found_rows'  => true,
            'update_post_term_cache' => false,
            'update_post_meta_cache' => false,
        ];

        $query = new WP_Query($args);
        $posts = $query->posts ?? [];

        // Cache for 1 hour
        wp_cache_set($cache_key, $posts, self::CACHE_GROUP, HOUR_IN_SECONDS);

        return $posts;
    }

    /**
     * Get post by ID with caching
     * 
     * @param int $post_id Post ID
     * @return WP_Post|null Post object or null if not found
     */
    public function getPostById(int $post_id): ?WP_Post
    {
        $cache_key = sprintf('post_%d', $post_id);
        $cached = wp_cache_get($cache_key, self::CACHE_GROUP);

        if (false !== $cached && $cached instanceof WP_Post) {
            return $cached;
        }

        $post = get_post($post_id);
        
        if ($post instanceof WP_Post) {
            wp_cache_set($cache_key, $post, self::CACHE_GROUP, HOUR_IN_SECONDS);
            return $post;
        }

        return null;
    }

    /**
     * Get posts by meta key/value with prepared statement
     * 
     * @param string $meta_key Meta key
     * @param string $meta_value Meta value
     * @param string $post_type Post type
     * @param int $limit Limit results
     * @return array<int> Array of post IDs
     */
    public function getPostIdsByMeta(
        string $meta_key,
        string $meta_value,
        string $post_type = 'post',
        int $limit = 10
    ): array {
        global $wpdb;
        
        // Sanitize inputs
        $safe_key = sanitize_key($meta_key);
        $safe_value = sanitize_text_field($meta_value);
        $safe_post_type = sanitize_key($post_type);

        $cache_key = sprintf('posts_by_meta_%s_%s_%s', $safe_key, md5($safe_value), $safe_post_type);
        $cached = wp_cache_get($cache_key, self::CACHE_GROUP);

        if (false !== $cached) {
            return $cached;
        }

        $posts_table = $wpdb->posts;
        $meta_table = $wpdb->postmeta;
        
        // Using prepared statement for SQL injection prevention
        $sql = $wpdb->prepare(
            "SELECT DISTINCT p.ID 
             FROM {$posts_table} p
             INNER JOIN {$meta_table} pm ON p.ID = pm.post_id
             WHERE p.post_type = %s
               AND p.post_status = 'publish'
               AND pm.meta_key = %s
               AND pm.meta_value = %s
             ORDER BY p.post_date DESC
             LIMIT %d",
            $safe_post_type,
            $safe_key,
            $safe_value,
            $limit
        );

        $results = $wpdb->get_col($sql);
        $post_ids = array_map('intval', $results);

        wp_cache_set($cache_key, $post_ids, self::CACHE_GROUP, 30 * MINUTE_IN_SECONDS);

        return $post_ids;
    }

    /**
     * Search posts with sanitization
     * 
     * @param string $search_term Search term
     * @param int $limit Limit results
     * @return array<WP_Post> Array of post objects
     */
    public function searchPosts(string $search_term, int $limit = 10): array
    {
        $safe_term = sanitize_text_field($search_term);
        
        if (empty($safe_term)) {
            return [];
        }

        $cache_key = sprintf('search_posts_%s_%d', md5($safe_term), $limit);
        $cached = wp_cache_get($cache_key, self::CACHE_GROUP);

        if (false !== $cached) {
            return $cached;
        }

        $args = [
            'post_type'      => 'post',
            'posts_per_page' => $limit,
            'post_status'    => 'publish',
            's'              => $safe_term,
            'no_found_rows'  => true,
        ];

        $query = new WP_Query($args);
        $posts = $query->posts ?? [];

        wp_cache_set($cache_key, $posts, self::CACHE_GROUP, 15 * MINUTE_IN_SECONDS);

        return $posts;
    }

    /**
     * Clear post cache
     * 
     * @param int|null $post_id Specific post ID or null for all
     * @return void
     */
    public function clearCache(?int $post_id = null): void
    {
        if ($post_id !== null) {
            wp_cache_delete(sprintf('post_%d', $post_id), self::CACHE_GROUP);
        } else {
            wp_cache_flush_group(self::CACHE_GROUP);
        }
    }
}
