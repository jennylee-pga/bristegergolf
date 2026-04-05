<?php
/**
 * Sidebar — Sponsors
 *
 * Replaces the default Kale sidebar with a sponsors listing.
 * Sponsors are managed via the custom "Sponsors" post type in WP Admin.
 *
 * @package kale-child
 */

$kale_sidebar_size = kale_get_option('kale_sidebar_size');
?>
<!-- Sidebar: Sponsors -->
<aside class="sidebar sidebar-column <?php echo ( $kale_sidebar_size == 0 ) ? 'col-md-4' : 'col-md-3'; ?>" role="complementary" aria-label="<?php _ex( 'Sponsors', 'aria label', 'kale' ); ?>">

    <div class="widget bsg-sponsors-widget">
        <h3 class="widget-title">Affiliations</h3>

        <?php
        $sponsors = new WP_Query( array(
            'post_type'      => 'bsg_sponsor',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'orderby'        => 'menu_order',
            'order'          => 'ASC',
        ) );

        if ( $sponsors->have_posts() ) : ?>
            <ul class="bsg-sponsors-list">
            <?php while ( $sponsors->have_posts() ) : $sponsors->the_post();
                $sponsor_url = get_post_meta( get_the_ID(), '_bsg_sponsor_url', true );
                $has_link    = ! empty( $sponsor_url );
            ?>
                <li class="bsg-sponsor-item">
                    <?php if ( $has_link ) : ?>
                        <a href="<?php echo esc_url( $sponsor_url ); ?>" target="_blank" rel="noopener noreferrer">
                    <?php endif; ?>

                    <?php if ( has_post_thumbnail() ) : ?>
                        <div class="bsg-sponsor-logo">
                            <?php the_post_thumbnail( 'bsg-sponsor-logo' ); ?>
                        </div>
                    <?php endif; ?>

                    <span class="bsg-sponsor-name"><?php the_title(); ?></span>

                    <?php if ( $has_link ) : ?>
                        </a>
                    <?php endif; ?>
                </li>
            <?php endwhile; ?>
            </ul>
        <?php else : ?>
            <p class="bsg-sponsors-empty">Sponsors coming soon.</p>
        <?php endif;
        wp_reset_postdata();
        ?>
    </div>

</aside>
<!-- /Sidebar: Sponsors -->
