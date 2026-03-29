<?php
/**
* Custom footer â Bri Steger Golf
* Dark footer with gradient accent
*
* @package kale-child
*/
?>

        <?php if(is_front_page() && !is_paged() ) {
            get_template_part('parts/frontpage', 'large');
        } ?>

        <?php get_sidebar('footer'); ?>

        <!-- Footer -->
        <div class="footer" role="contentinfo">

            <?php if ( is_active_sidebar( 'footer-row-3-center' ) ) { ?>
            <div class="footer-row-3-center"><?php dynamic_sidebar( 'footer-row-3-center' ); ?>
            <?php } ?>

            <div class="footer-copyright">
                &copy; <?php echo date('Y'); ?> <strong style="color:#FFD700;">Bri Steger Golf</strong>. All rights reserved.
            </div>

            <div class="footer-copyright">
                <ul class="credit">
                    <li>Powered by <a href="https://wordpress.org/">WordPress</a></li>
                </ul>
            </div>

        </div>
        <!-- /Footer -->

    </div><!-- /Container -->
</div><!-- /Main Wrapper -->

<?php wp_footer(); ?>
</body>
</html>
