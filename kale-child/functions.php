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

    // Google Fonts — Playfair Display + Poppins for a sporty-elegant mix
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

// ==========================================================================
// CUSTOM POST TYPE: Sponsors
// ==========================================================================

function bsg_register_sponsor_post_type() {
    $labels = array(
        'name'               => 'Sponsors',
        'singular_name'      => 'Sponsor',
        'menu_name'          => 'Sponsors',
        'add_new'            => 'Add New Sponsor',
        'add_new_item'       => 'Add New Sponsor',
        'edit_item'          => 'Edit Sponsor',
        'new_item'           => 'New Sponsor',
        'view_item'          => 'View Sponsor',
        'search_items'       => 'Search Sponsors',
        'not_found'          => 'No sponsors found',
        'not_found_in_trash' => 'No sponsors found in Trash',
        'all_items'          => 'All Sponsors',
    );

    $args = array(
        'labels'             => $labels,
        'public'             => false,
        'publicly_queryable' => false,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'menu_position'      => 25,
        'menu_icon'          => 'dashicons-heart',
        'supports'           => array( 'title', 'thumbnail' ),
        'has_archive'        => false,
        'rewrite'            => false,
    );

    register_post_type( 'bsg_sponsor', $args );
}
add_action( 'init', 'bsg_register_sponsor_post_type' );

// Add custom meta box for sponsor website URL
function bsg_sponsor_meta_boxes() {
    add_meta_box(
        'bsg_sponsor_url',
        'Sponsor Website URL',
        'bsg_sponsor_url_callback',
        'bsg_sponsor',
        'normal',
        'high'
    );
}
add_action( 'add_meta_boxes', 'bsg_sponsor_meta_boxes' );

function bsg_sponsor_url_callback( $post ) {
    wp_nonce_field( 'bsg_sponsor_url_nonce', 'bsg_sponsor_url_nonce_field' );
    $url = get_post_meta( $post->ID, '_bsg_sponsor_url', true );
    echo '<label for="bsg_sponsor_url">Website URL:</label><br />';
    echo '<input type="url" id="bsg_sponsor_url" name="bsg_sponsor_url" value="' . esc_attr( $url ) . '" style="width:100%; margin-top:5px;" placeholder="https://example.com" />';
    echo '<p class="description">Enter the sponsor\'s website URL. Their logo and name will link here.</p>';
}

function bsg_save_sponsor_url( $post_id ) {
    if ( ! isset( $_POST['bsg_sponsor_url_nonce_field'] ) ||
         ! wp_verify_nonce( $_POST['bsg_sponsor_url_nonce_field'], 'bsg_sponsor_url_nonce' ) ) {
        return;
    }
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;

    if ( isset( $_POST['bsg_sponsor_url'] ) ) {
        update_post_meta( $post_id, '_bsg_sponsor_url', esc_url_raw( $_POST['bsg_sponsor_url'] ) );
    }
}
add_action( 'save_post_bsg_sponsor', 'bsg_save_sponsor_url' );

// Enable featured image support + register custom sponsor logo size
function bsg_theme_support() {
    add_theme_support( 'post-thumbnails' );
    add_image_size( 'bsg-sponsor-logo', 300, 150, false ); // 300x150, soft crop (keeps aspect ratio)
}
add_action( 'after_setup_theme', 'bsg_theme_support' );

// Show recommended size note below the featured image box for Sponsors
function bsg_sponsor_featured_image_note( $content, $post_id, $thumbnail_id ) {
    $post = get_post( $post_id );
    if ( $post && $post->post_type === 'bsg_sponsor' ) {
        $content .= '<p class="description" style="margin-top:10px; padding:10px; background:#fff8e1; border-left:4px solid #FFD700; font-size:13px;">';
        $content .= '<strong>Recommended size:</strong> 300 &times; 150 pixels (PNG or JPG).<br />';
        $content .= 'Logos will be displayed at this size in the sponsors sidebar. Use a transparent PNG for best results.';
        $content .= '</p>';
    }
    return $content;
}
add_filter( 'admin_post_thumbnail_html', 'bsg_sponsor_featured_image_note', 10, 3 );

// ==========================================================================
// CUSTOM POST TYPE: Events (Tournaments)
// ==========================================================================

function bsg_register_event_post_type() {
    $labels = array(
        'name'               => 'Events',
        'singular_name'      => 'Event',
        'menu_name'          => 'Events',
        'add_new'            => 'Add New Event',
        'add_new_item'       => 'Add New Event',
        'edit_item'          => 'Edit Event',
        'new_item'           => 'New Event',
        'view_item'          => 'View Event',
        'search_items'       => 'Search Events',
        'not_found'          => 'No events found',
        'not_found_in_trash' => 'No events found in Trash',
        'all_items'          => 'All Events',
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'menu_position'      => 20,
        'menu_icon'          => 'dashicons-calendar-alt',
        'supports'           => array( 'title' ),
        'has_archive'        => false,
        'rewrite'            => array( 'slug' => 'event' ),
    );

    register_post_type( 'bsg_event', $args );
}
add_action( 'init', 'bsg_register_event_post_type' );

// US States array
function bsg_get_us_states() {
    return array(
        '' => '— Select State —',
        'AL' => 'Alabama', 'AK' => 'Alaska', 'AZ' => 'Arizona', 'AR' => 'Arkansas',
        'CA' => 'California', 'CO' => 'Colorado', 'CT' => 'Connecticut', 'DE' => 'Delaware',
        'FL' => 'Florida', 'GA' => 'Georgia', 'HI' => 'Hawaii', 'ID' => 'Idaho',
        'IL' => 'Illinois', 'IN' => 'Indiana', 'IA' => 'Iowa', 'KS' => 'Kansas',
        'KY' => 'Kentucky', 'LA' => 'Louisiana', 'ME' => 'Maine', 'MD' => 'Maryland',
        'MA' => 'Massachusetts', 'MI' => 'Michigan', 'MN' => 'Minnesota', 'MS' => 'Mississippi',
        'MO' => 'Missouri', 'MT' => 'Montana', 'NE' => 'Nebraska', 'NV' => 'Nevada',
        'NH' => 'New Hampshire', 'NJ' => 'New Jersey', 'NM' => 'New Mexico', 'NY' => 'New York',
        'NC' => 'North Carolina', 'ND' => 'North Dakota', 'OH' => 'Ohio', 'OK' => 'Oklahoma',
        'OR' => 'Oregon', 'PA' => 'Pennsylvania', 'RI' => 'Rhode Island', 'SC' => 'South Carolina',
        'SD' => 'South Dakota', 'TN' => 'Tennessee', 'TX' => 'Texas', 'UT' => 'Utah',
        'VT' => 'Vermont', 'VA' => 'Virginia', 'WA' => 'Washington', 'WV' => 'West Virginia',
        'WI' => 'Wisconsin', 'WY' => 'Wyoming', 'DC' => 'Washington D.C.',
    );
}

// Event meta boxes
function bsg_event_meta_boxes() {
    add_meta_box( 'bsg_event_details', 'Event Details', 'bsg_event_details_callback', 'bsg_event', 'normal', 'high' );
}
add_action( 'add_meta_boxes', 'bsg_event_meta_boxes' );

function bsg_event_details_callback( $post ) {
    wp_nonce_field( 'bsg_event_details_nonce', 'bsg_event_details_nonce_field' );

    $start_date = get_post_meta( $post->ID, '_bsg_event_start_date', true );
    $end_date   = get_post_meta( $post->ID, '_bsg_event_end_date', true );
    $city       = get_post_meta( $post->ID, '_bsg_event_city', true );
    $state      = get_post_meta( $post->ID, '_bsg_event_state', true );

    $states = bsg_get_us_states();
    ?>
    <style>
        .bsg-event-fields { display: flex; flex-wrap: wrap; gap: 20px; }
        .bsg-event-fields .bsg-field-group { flex: 1; min-width: 200px; }
        .bsg-event-fields label { display: block; font-weight: 600; margin-bottom: 5px; }
        .bsg-event-fields input, .bsg-event-fields select { width: 100%; padding: 6px 8px; }
        .bsg-field-row { margin-bottom: 15px; }
    </style>

    <div class="bsg-field-row">
        <h4 style="margin:0 0 10px; border-bottom:1px solid #ddd; padding-bottom:8px;">Dates</h4>
        <div class="bsg-event-fields">
            <div class="bsg-field-group">
                <label for="bsg_event_start_date">Start Date</label>
                <input type="date" id="bsg_event_start_date" name="bsg_event_start_date" value="<?php echo esc_attr( $start_date ); ?>" />
            </div>
            <div class="bsg-field-group">
                <label for="bsg_event_end_date">End Date</label>
                <input type="date" id="bsg_event_end_date" name="bsg_event_end_date" value="<?php echo esc_attr( $end_date ); ?>" />
            </div>
        </div>
    </div>

    <div class="bsg-field-row">
        <h4 style="margin:0 0 10px; border-bottom:1px solid #ddd; padding-bottom:8px;">Location</h4>
        <div class="bsg-event-fields">
            <div class="bsg-field-group">
                <label for="bsg_event_city">City</label>
                <input type="text" id="bsg_event_city" name="bsg_event_city" value="<?php echo esc_attr( $city ); ?>" placeholder="e.g. Scottsdale" />
            </div>
            <div class="bsg-field-group">
                <label for="bsg_event_state">State</label>
                <select id="bsg_event_state" name="bsg_event_state">
                    <?php foreach ( $states as $abbr => $name ) : ?>
                        <option value="<?php echo esc_attr( $abbr ); ?>" <?php selected( $state, $abbr ); ?>><?php echo esc_html( $name ); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>
    <?php
}

function bsg_save_event_details( $post_id ) {
    if ( ! isset( $_POST['bsg_event_details_nonce_field'] ) ||
         ! wp_verify_nonce( $_POST['bsg_event_details_nonce_field'], 'bsg_event_details_nonce' ) ) {
        return;
    }
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;

    $fields = array( '_bsg_event_start_date', '_bsg_event_end_date', '_bsg_event_city', '_bsg_event_state' );
    $keys   = array( 'bsg_event_start_date', 'bsg_event_end_date', 'bsg_event_city', 'bsg_event_state' );

    foreach ( $fields as $i => $meta_key ) {
        if ( isset( $_POST[ $keys[$i] ] ) ) {
            update_post_meta( $post_id, $meta_key, sanitize_text_field( $_POST[ $keys[$i] ] ) );
        }
    }
}
add_action( 'save_post_bsg_event', 'bsg_save_event_details' );

// ==========================================================================
// WIDGET: Upcoming Events
// ==========================================================================

class BSG_Upcoming_Events_Widget extends WP_Widget {

    public function __construct() {
        parent::__construct(
            'bsg_upcoming_events',
            'BSG: Upcoming Events',
            array( 'description' => 'Displays upcoming tournament events.' )
        );
    }

    public function widget( $args, $instance ) {
        $title = ! empty( $instance['title'] ) ? $instance['title'] : 'Upcoming Events';
        $count = ! empty( $instance['count'] ) ? absint( $instance['count'] ) : 5;

        $events = new WP_Query( array(
            'post_type'      => 'bsg_event',
            'posts_per_page' => $count,
            'post_status'    => 'publish',
            'meta_key'       => '_bsg_event_start_date',
            'orderby'        => 'meta_value',
            'order'          => 'ASC',
            'meta_query'     => array(
                array(
                    'key'     => '_bsg_event_start_date',
                    'value'   => date( 'Y-m-d' ),
                    'compare' => '>=',
                    'type'    => 'DATE',
                ),
            ),
        ) );

        if ( ! $events->have_posts() ) {
            wp_reset_postdata();
            return;
        }

        echo $args['before_widget'];
        ?>
        <div class="bsg-upcoming-events">
            <h2 class="bsg-events-title"><?php echo esc_html( $title ); ?></h2>
            <div class="bsg-events-list">
                <?php while ( $events->have_posts() ) : $events->the_post();
                    $start = get_post_meta( get_the_ID(), '_bsg_event_start_date', true );
                    $end   = get_post_meta( get_the_ID(), '_bsg_event_end_date', true );
                    $city  = get_post_meta( get_the_ID(), '_bsg_event_city', true );
                    $state = get_post_meta( get_the_ID(), '_bsg_event_state', true );

                    $start_ts  = strtotime( $start );
                    $end_ts    = strtotime( $end );

                    // Format dates
                    if ( $start && $end && date( 'M Y', $start_ts ) === date( 'M Y', $end_ts ) ) {
                        $date_display = date( 'M j', $start_ts ) . '–' . date( 'j, Y', $end_ts );
                    } elseif ( $start && $end ) {
                        $date_display = date( 'M j', $start_ts ) . ' – ' . date( 'M j, Y', $end_ts );
                    } elseif ( $start ) {
                        $date_display = date( 'M j, Y', $start_ts );
                    } else {
                        $date_display = 'TBD';
                    }

                    $location = '';
                    if ( $city && $state ) $location = $city . ', ' . $state;
                    elseif ( $city ) $location = $city;
                    elseif ( $state ) $location = $state;
                ?>
                <div class="bsg-event-card">
                    <div class="bsg-event-date-badge">
                        <span class="bsg-event-month"><?php echo $start ? date( 'M', $start_ts ) : 'TBD'; ?></span>
                        <span class="bsg-event-day"><?php echo $start ? date( 'j', $start_ts ) : '—'; ?></span>
                    </div>
                    <div class="bsg-event-info">
                        <h3 class="bsg-event-name"><?php the_title(); ?></h3>
                        <p class="bsg-event-meta">
                            <span class="bsg-event-dates"><?php echo esc_html( $date_display ); ?></span>
                            <?php if ( $location ) : ?>
                                <span class="bsg-event-location"><?php echo esc_html( $location ); ?></span>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
        <?php
        echo $args['after_widget'];
        wp_reset_postdata();
    }

    public function form( $instance ) {
        $title = ! empty( $instance['title'] ) ? $instance['title'] : 'Upcoming Events';
        $count = ! empty( $instance['count'] ) ? absint( $instance['count'] ) : 5;
        ?>
        <p>
            <label for="<?php echo $this->get_field_id( 'title' ); ?>">Title:</label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
        </p>
        <p>
            <label for="<?php echo $this->get_field_id( 'count' ); ?>">Number of events to show:</label>
            <input class="tiny-text" id="<?php echo $this->get_field_id( 'count' ); ?>" name="<?php echo $this->get_field_name( 'count' ); ?>" type="number" value="<?php echo esc_attr( $count ); ?>" min="1" max="20" />
        </p>
        <?php
    }

    public function update( $new_instance, $old_instance ) {
        $instance = array();
        $instance['title'] = sanitize_text_field( $new_instance['title'] );
        $instance['count'] = absint( $new_instance['count'] );
        return $instance;
    }
}

function bsg_register_widgets() {
    register_widget( 'BSG_Upcoming_Events_Widget' );
}
add_action( 'widgets_init', 'bsg_register_widgets' );

// Register a widget area for the front page events section
function bsg_register_events_widget_area() {
    register_sidebar( array(
        'name'          => 'Front Page: Events Section',
        'id'            => 'frontpage-events',
        'description'   => 'Appears above the blog posts on the front page. Add the "BSG: Upcoming Events" widget here.',
        'before_widget' => '<div id="%1$s" class="bsg-events-widget-area %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '',
        'after_title'   => '',
    ) );
}
add_action( 'widgets_init', 'bsg_register_events_widget_area' );

// ==========================================================================
// SCHEDULE PAGE TEMPLATE: Calendar View
// ==========================================================================

// Register the page template from child theme
function bsg_register_page_templates( $templates ) {
    $templates['template-schedule.php'] = 'Schedule (Calendar View)';
    return $templates;
}
add_filter( 'theme_page_templates', 'bsg_register_page_templates' );

// Load the template file
function bsg_load_page_template( $template ) {
    if ( is_page() ) {
        $page_template = get_page_template_slug();
        if ( $page_template === 'template-schedule.php' ) {
            $file = get_stylesheet_directory() . '/template-schedule.php';
            if ( file_exists( $file ) ) {
                return $file;
            }
        }
    }
    return $template;
}
add_filter( 'template_include', 'bsg_load_page_template' );

// AJAX handler for calendar navigation
function bsg_calendar_ajax() {
    $month = isset( $_GET['month'] ) ? absint( $_GET['month'] ) : date( 'n' );
    $year  = isset( $_GET['year'] ) ? absint( $_GET['year'] ) : date( 'Y' );

    echo bsg_render_calendar( $month, $year );
    wp_die();
}
add_action( 'wp_ajax_bsg_calendar', 'bsg_calendar_ajax' );
add_action( 'wp_ajax_nopriv_bsg_calendar', 'bsg_calendar_ajax' );

// Calendar rendering function
function bsg_render_calendar( $month, $year ) {
    $first_day   = mktime( 0, 0, 0, $month, 1, $year );
    $days_in_month = date( 't', $first_day );
    $start_dow   = date( 'w', $first_day ); // 0=Sun
    $month_name  = date( 'F', $first_day );

    // Prev/Next
    $prev_month = $month - 1;
    $prev_year  = $year;
    if ( $prev_month < 1 ) { $prev_month = 12; $prev_year--; }
    $next_month = $month + 1;
    $next_year  = $year;
    if ( $next_month > 12 ) { $next_month = 1; $next_year++; }

    // Fetch events for this month
    $month_start = sprintf( '%04d-%02d-01', $year, $month );
    $month_end   = sprintf( '%04d-%02d-%02d', $year, $month, $days_in_month );

    $events = new WP_Query( array(
        'post_type'      => 'bsg_event',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
        'meta_query'     => array(
            'relation' => 'AND',
            array(
                'key'     => '_bsg_event_start_date',
                'value'   => $month_end,
                'compare' => '<=',
                'type'    => 'DATE',
            ),
            array(
                'key'     => '_bsg_event_end_date',
                'value'   => $month_start,
                'compare' => '>=',
                'type'    => 'DATE',
            ),
        ),
    ) );

    // Build a map of day => events
    $day_events = array();
    if ( $events->have_posts() ) {
        while ( $events->have_posts() ) {
            $events->the_post();
            $start = get_post_meta( get_the_ID(), '_bsg_event_start_date', true );
            $end   = get_post_meta( get_the_ID(), '_bsg_event_end_date', true );
            $city  = get_post_meta( get_the_ID(), '_bsg_event_city', true );
            $state = get_post_meta( get_the_ID(), '_bsg_event_state', true );
            $location = '';
            if ( $city && $state ) $location = $city . ', ' . $state;
            elseif ( $city ) $location = $city;

            $s = max( strtotime( $start ), strtotime( $month_start ) );
            $e = min( strtotime( $end ), strtotime( $month_end ) );

            for ( $d = $s; $d <= $e; $d += 86400 ) {
                $day_num = (int) date( 'j', $d );
                if ( ! isset( $day_events[ $day_num ] ) ) $day_events[ $day_num ] = array();
                $day_events[ $day_num ][] = array(
                    'title'    => get_the_title(),
                    'location' => $location,
                    'start'    => $start,
                    'end'      => $end,
                );
            }
        }
    }
    wp_reset_postdata();

    $today     = date( 'j' );
    $today_m   = date( 'n' );
    $today_y   = date( 'Y' );

    ob_start();
    ?>
    <div class="bsg-calendar-header">
        <button class="bsg-cal-nav bsg-cal-prev" data-month="<?php echo $prev_month; ?>" data-year="<?php echo $prev_year; ?>">&larr;</button>
        <h2 class="bsg-cal-month-title"><?php echo esc_html( $month_name . ' ' . $year ); ?></h2>
        <button class="bsg-cal-nav bsg-cal-next" data-month="<?php echo $next_month; ?>" data-year="<?php echo $next_year; ?>">&rarr;</button>
    </div>
    <table class="bsg-calendar-table">
        <thead>
            <tr>
                <th>Sun</th><th>Mon</th><th>Tue</th><th>Wed</th><th>Thu</th><th>Fri</th><th>Sat</th>
            </tr>
        </thead>
        <tbody>
            <tr>
            <?php
            // Empty cells before first day
            for ( $i = 0; $i < $start_dow; $i++ ) {
                echo '<td class="bsg-cal-empty"></td>';
            }

            $current_dow = $start_dow;
            for ( $day = 1; $day <= $days_in_month; $day++ ) {
                $is_today = ( $day == $today && $month == $today_m && $year == $today_y );
                $has_events = isset( $day_events[ $day ] );
                $classes = 'bsg-cal-day';
                if ( $is_today ) $classes .= ' bsg-cal-today';
                if ( $has_events ) $classes .= ' bsg-cal-has-event';

                echo '<td class="' . $classes . '">';
                echo '<span class="bsg-cal-day-num">' . $day . '</span>';

                if ( $has_events ) {
                    // Deduplicate events (multi-day events repeat)
                    $seen = array();
                    foreach ( $day_events[ $day ] as $ev ) {
                        if ( in_array( $ev['title'], $seen ) ) continue;
                        $seen[] = $ev['title'];
                        echo '<div class="bsg-cal-event-dot" title="' . esc_attr( $ev['title'] ) . '">';
                        echo '<span class="bsg-cal-event-name">' . esc_html( $ev['title'] ) . '</span>';
                        echo '</div>';
                    }
                }

                echo '</td>';
                $current_dow++;
                if ( $current_dow == 7 && $day < $days_in_month ) {
                    echo '</tr><tr>';
                    $current_dow = 0;
                }
            }

            // Fill remaining cells
            if ( $current_dow > 0 && $current_dow < 7 ) {
                for ( $i = $current_dow; $i < 7; $i++ ) {
                    echo '<td class="bsg-cal-empty"></td>';
                }
            }
            ?>
            </tr>
        </tbody>
    </table>
    <?php
    return ob_get_clean();
}

// Add custom body class
function kale_child_body_class($classes) {
    $classes[] = 'bri-steger-golf';
    $classes[] = 'bsg-vibrant';
    return $classes;
}
add_filter('body_class', 'kale_child_body_class');
