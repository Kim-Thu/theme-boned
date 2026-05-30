<?php
/**
 * Main template file
 * 
 * @package ModernArchitect
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>

<main class="site-main" id="primary">
    <div class="container">
        <?php if (have_posts()) : ?>
            
            <div class="posts-grid">
                <?php while (have_posts()) : the_post(); ?>
                    
                    <?php
                    // Use our card component
                    modern_architect_get_template_part('card', null, [
                        'title'     => get_the_title(),
                        'link_url'  => get_permalink(),
                        'image_url' => get_the_post_thumbnail_url(get_the_ID(), 'medium'),
                        'content'   => get_the_excerpt(),
                        'modifier'  => 'post',
                    ]);
                    ?>
                    
                <?php endwhile; ?>
            </div>
            
            <?php
            // Pagination
            the_posts_pagination([
                'mid_size'  => 2,
                'prev_text' => __('Previous', 'modern-architect'),
                'next_text' => __('Next', 'modern-architect'),
            ]);
            ?>
            
        <?php else : ?>
            
            <section class="no-results">
                <h1 class="page-title"><?php esc_html_e('Nothing Found', 'modern-architect'); ?></h1>
                <p><?php esc_html_e('It seems we can\'t find what you\'re looking for.', 'modern-architect'); ?></p>
            </section>
            
        <?php endif; ?>
    </div>
</main>

<?php
get_footer();
