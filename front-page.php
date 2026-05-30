<?php
/**
 * Front page template - Beautiful homepage
 */

get_header();
?>

<!-- Hero Section -->
<section class="hero-section">
  <div class="container">
    <div class="hero-content">
      <div class="hero-badge">🚀 Welcome to Modern Architect</div>
      <h1 class="hero-title">Build Stunning Websites with Modern Design</h1>
      <p class="hero-subtitle">A powerful WordPress theme with SOLID architecture, CSS Variables design system, and pixel-perfect aesthetics.</p>
      <div class="hero-buttons">
        <a href="#features" class="btn btn-primary">Explore Features →</a>
        <a href="#posts" class="btn btn-secondary">View Our Work</a>
      </div>
    </div>
  </div>
</section>

<!-- Features Section -->
<section class="features-section" id="features">
  <div class="container">
    <div class="section-header">
      <span class="section-label">✨ Features</span>
      <h2 class="section-title">Why Choose Modern Architect?</h2>
      <p class="section-subtitle">Built with modern technologies and best practices for optimal performance</p>
    </div>
    
    <div class="features-grid">
      <div class="feature-card">
        <div class="feature-icon">⚡</div>
        <h3 class="feature-title">Lightning Fast</h3>
        <p class="feature-description">Optimized code, lazy loading, and smart caching ensure your site loads in milliseconds.</p>
      </div>
      
      <div class="feature-card">
        <div class="feature-icon">🎨</div>
        <h3 class="feature-title">Beautiful Design</h3>
        <p class="feature-description">Modern aesthetics with CSS Variables, smooth animations, and responsive layouts.</p>
      </div>
      
      <div class="feature-card">
        <div class="feature-icon">🔒</div>
        <h3 class="feature-title">Secure & Reliable</h3>
        <p class="feature-description">Built with security best practices, sanitized inputs, and escaped outputs.</p>
      </div>
      
      <div class="feature-card">
        <div class="feature-icon">📱</div>
        <h3 class="feature-title">Fully Responsive</h3>
        <p class="feature-description">Looks perfect on all devices - desktop, tablet, and mobile.</p>
      </div>
      
      <div class="feature-card">
        <div class="feature-icon">🛠️</div>
        <h3 class="feature-title">Easy to Customize</h3>
        <p class="feature-description">CSS Variables design system allows instant theme customization without recompiling.</p>
      </div>
      
      <div class="feature-card">
        <div class="feature-icon">🚀</div>
        <h3 class="feature-title">SEO Optimized</h3>
        <p class="feature-description">Clean code structure and semantic HTML for better search engine rankings.</p>
      </div>
    </div>
  </div>
</section>

<!-- Posts Section -->
<section class="posts-section" id="posts">
  <div class="container">
    <div class="section-header">
      <span class="section-label">📰 Latest Posts</span>
      <h2 class="section-title">From Our Blog</h2>
      <p class="section-subtitle">Stay updated with our latest news and articles</p>
    </div>
    
    <div class="posts-grid">
      <?php
      $args = array(
        'posts_per_page' => 3,
        'post_status'    => 'publish',
      );
      $query = new WP_Query($args);
      
      if ($query->have_posts()) :
        while ($query->have_posts()) : $query->the_post();
          modern_architect_get_template_part('card', null, [
            'title'     => get_the_title(),
            'link_url'  => get_permalink(),
            'image_url' => get_the_post_thumbnail_url(get_the_ID(), 'large'),
            'content'   => get_the_excerpt(),
            'modifier'  => 'post',
          ]);
        endwhile;
        wp_reset_postdata();
      endif;
      ?>
    </div>
  </div>
</section>

<!-- CTA Section -->
<section class="cta-section">
  <div class="container">
    <div class="cta-content">
      <h2 class="cta-title">Ready to Get Started?</h2>
      <p class="cta-description">Join thousands of satisfied users and build your dream website today.</p>
      <a href="#" class="btn btn-secondary">Contact Us →</a>
    </div>
  </div>
</section>

<?php get_footer(); ?>