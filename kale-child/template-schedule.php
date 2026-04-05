<?php
/**
 * Template Name: Schedule (Calendar View)
 *
 * Displays a monthly calendar grid of tournament events.
 *
 * @package kale-child
 */

get_header();

$current_month = isset( $_GET['cal_month'] ) ? absint( $_GET['cal_month'] ) : date( 'n' );
$current_year  = isset( $_GET['cal_year'] ) ? absint( $_GET['cal_year'] ) : date( 'Y' );
?>

<div class="bsg-schedule-page">
    <div class="row">
        <div class="col-md-12">

            <h1 class="bsg-schedule-heading"><?php the_title(); ?></h1>

            <div class="bsg-calendar-wrap" id="bsg-calendar">
                <?php echo bsg_render_calendar( $current_month, $current_year ); ?>
            </div>

            <?php
            // Also show a list of upcoming events below the calendar
            $upcoming = new WP_Query( array(
                'post_type'      => 'bsg_event',
                'posts_per_page' => 10,
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

            if ( $upcoming->have_posts() ) : ?>
            <div class="bsg-schedule-list">
                <h2 class="bsg-schedule-list-title">Upcoming Events</h2>
                <?php while ( $upcoming->have_posts() ) : $upcoming->the_post();
                    $start = get_post_meta( get_the_ID(), '_bsg_event_start_date', true );
                    $end   = get_post_meta( get_the_ID(), '_bsg_event_end_date', true );
                    $city  = get_post_meta( get_the_ID(), '_bsg_event_city', true );
                    $state = get_post_meta( get_the_ID(), '_bsg_event_state', true );

                    $start_ts = strtotime( $start );
                    $end_ts   = strtotime( $end );

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
                ?>
                <div class="bsg-schedule-event-row">
                    <div class="bsg-schedule-event-date">
                        <span class="bsg-sched-month"><?php echo $start ? date( 'M', $start_ts ) : 'TBD'; ?></span>
                        <span class="bsg-sched-day"><?php echo $start ? date( 'j', $start_ts ) : '—'; ?></span>
                    </div>
                    <div class="bsg-schedule-event-details">
                        <h3><?php the_title(); ?></h3>
                        <p>
                            <?php echo esc_html( $date_display ); ?>
                            <?php if ( $location ) : ?>
                                &nbsp;&bull;&nbsp; <?php echo esc_html( $location ); ?>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
            <?php endif; wp_reset_postdata(); ?>

        </div>
    </div>
</div>

<script>
(function() {
    var calWrap = document.getElementById('bsg-calendar');
    if (!calWrap) return;

    calWrap.addEventListener('click', function(e) {
        var btn = e.target.closest('.bsg-cal-nav');
        if (!btn) return;
        e.preventDefault();

        var month = btn.getAttribute('data-month');
        var year  = btn.getAttribute('data-year');
        var ajaxUrl = '<?php echo admin_url( "admin-ajax.php" ); ?>';

        fetch(ajaxUrl + '?action=bsg_calendar&month=' + month + '&year=' + year)
            .then(function(r) { return r.text(); })
            .then(function(html) { calWrap.innerHTML = html; });
    });
})();
</script>

<?php get_footer(); ?>
