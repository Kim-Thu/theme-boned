<?php
/**
 * Card Component Template
 * 
 * A reusable card component with BEM naming and CSS Variables.
 * 
 * @package ModernArchitect
 * @param array $args {
 *     @type string $title Card title
 *     @type string $image_url Image URL
 *     @type string $content Card content
 *     @type string $link_url Link URL
 *     @type string $modifier BEM modifier class
 *     @type string $image_alt Image alt text
 * }
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

$defaults = [
    'title'       => '',
    'image_url'   => '',
    'content'     => '',
    'link_url'    => '#',
    'modifier'    => '',
    'image_alt'   => '',
    'show_button' => true,
    'button_text' => __('Read More', 'modern-architect'),
];

$args = wp_parse_args($args, $defaults);

// Sanitize outputs
$safe_title = esc_html($args['title']);
$safe_image_url = esc_url($args['image_url']);
$safe_link_url = esc_url($args['link_url']);
$safe_modifier = $args['modifier'] ? 'card--' . sanitize_key($args['modifier']) : '';
$safe_image_alt = $args['image_alt'] ? esc_attr($args['image_alt']) : $safe_title;

// Allowed HTML for content
$allowed_html = [
    'p'      => ['class' => []],
    'span'   => ['class' => []],
    'strong' => [],
    'em'     => [],
    'br'     => [],
    'a'      => [
        'href'  => [],
        'title' => [],
        'class' => [],
    ],
];
?>

<article class="card <?php echo esc_attr($safe_modifier); ?>" itemscope itemtype="http://schema.org/Article">
    <?php if (!empty($args['image_url'])): ?>
        <div class="card__media">
            <img 
                src="<?php echo $safe_image_url; ?>" 
                alt="<?php echo esc_attr($safe_image_alt); ?>" 
                loading="lazy" 
                decoding="async"
                width="400" 
                height="250"
                class="card__image"
                itemprop="image"
            >
        </div>
    <?php endif; ?>

    <div class="card__body">
        <?php if (!empty($args['title'])): ?>
            <h3 class="card__title">
                <a href="<?php echo $safe_link_url; ?>" class="card__link" itemprop="url">
                    <span itemprop="name"><?php echo $safe_title; ?></span>
                </a>
            </h3>
        <?php endif; ?>
        
        <?php if (!empty($args['content'])): ?>
            <div class="card__content" itemprop="description">
                <?php echo wp_kses($args['content'], $allowed_html); ?>
            </div>
        <?php endif; ?>

        <?php if ($args['show_button']): ?>
            <div class="card__footer">
                <a 
                    href="<?php echo $safe_link_url; ?>" 
                    class="btn btn--primary btn--sm card__button"
                    aria-label="<?php printf esc_attr__('Read more about %s', 'modern-architect', $safe_title); ?>"
                >
                    <?php echo esc_html($args['button_text']); ?>
                </a>
            </div>
        <?php endif; ?>
    </div>
</article>
