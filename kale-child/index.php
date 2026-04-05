<?php
/**
 * The main template file — Bri Steger Golf
 *
 * Adds the events widget area above the blog feed.
 *
 * @package kale-child
 */
?>
<?php get_header(); ?>

<?php if ( is_front_page() && is_active_sidebar( 'frontpage-events' ) ) : ?>
<!-- Events Section -->
<div class="bsg-frontpage-events-section">
    <?php dynamic_sidebar( 'frontpage-events' ); ?>
</div>
<!-- /Events Section -->
<?php endif; ?>

<div class="blog-feed">
<!-- Two Columns -->
<div class="row two-columns">
    <?php get_template_part('parts/feed'); ?>
    <?php get_sidebar(); ?>
</div>
<!-- /Two Columns -->
<hr />
</div>

<?php get_footer(); ?>
