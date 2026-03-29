<?php
/**
 * Kale Child Theme - Bri Steger Golf
 * Bold, vibrant, sporty design
 *
 * @package kale-child
 */

// Enqueue parent and child theme styles
function kale_child_enqueue_styles() {
    // Parent theme style
    wp_enqueue_style(
        'kale-parent-style',
        get_template_directory_uri() . '/style.css',
        array(),
        wp_get_theme('kale')->get('Version')
    );

    // Child theme style (loads after parent)
    wp_enqueue_style(
        'kale-child-style',
        get_stylesheet_directory_uri() . '/style.css',
        array('kale-parent-style'),
        wp_get_theme()->get('Version')
    );

    // Google Fonts â Playfair Display + Poppins for a sporty-elegant mix
    wp_enqueue_style(
        'kale-child-google-fonts',
        'https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;1,400&family=Poppins:wght@300;400;500;600;700&family=Lora:ital,wght@0,400;1,400&display=swap',
        array(),
        null
    );

    // Font overrides via inline CSS
    $custom_fonts = '
        body {
            font-family: "Poppins", "Helvetica Neue", sans-serif;
        }
        .logo .header-logo-text {
            font-family: "Playfair Display", Georgia, serif;
        }
        .tagline,
        .tagline p {
            font-family: "Lora", Georgia, serif;
            font-style: italic;
        }
        h1, h2, h3, h4, h5, h6 {
            font-family: "Poppins", "Helvetica Neue", sans-serif;
        }
        .navbar-nav > li > a {
            font-family: "Poppins", "Helvetica Neue", sans-serif;
            font-weight: 500;
        }
        .frontpage-banner .caption h2 {
            font-family: "Playfair Display", Georgia, serif;
            text-transform: none;
        }
        .frontpage-banner .caption .read-more {
            font-family: "Poppins", "Helvetica Neue", sans-serif;
        }
        .entry-title,
        .entry-title a {
            font-family: "Playfair Display", Georgia, serif;
            text-transform: none;
        }
        .widget-title {
            font-family: "Poppins", "Helvetica Neue", sans-serif;
        }
        .blog-feed > h2 {
            font-family: "Poppins", "Helvetica Neue", sans-serif;
        }
        .pagination-blog-feed a {
            font-family: "Poppins", "Helvetica Neue", sans-serif;
        }
        .footer,
        .footer .footer-copyright {
            font-family: "Poppins", "Helvetica Neue", sans-serif;
        }
    ';
    wp_add_inline_style('kale-child-style', $custom_fonts);
}
add_action('wp_enqueue_scripts', 'kale_child_enqueue_styles');

// Add custom body class
function kale_child_body_class($classes) {
    $classes[] = 'bri-steger-golf';
    $classes[] = 'bsg-vibrant';
    return $classes;
}
add_filter('body_class', 'kale_child_body_class');
