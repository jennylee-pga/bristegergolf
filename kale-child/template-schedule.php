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

    // ── Month navigation ──────────────────────────────────────────────────
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

    // ── Event tooltips ────────────────────────────────────────────────────
    // Create one shared tooltip element appended to <body> so it is never
    // clipped by overflow:hidden on table cells or event pills.
    var tip = document.createElement('div');
    tip.id = 'bsg-cal-tooltip';
    tip.style.cssText = [
        'position:fixed',
        'z-index:9999',
        'background:#1a1a2e',
        'color:#fff',
        'font-family:Poppins,sans-serif',
        'font-size:12px',
        'line-height:1.5',
        'padding:8px 12px',
        'border-radius:4px',
        'border-left:3px solid #E91E90',
        'box-shadow:0 4px 16px rgba(0,0,0,.25)',
        'max-width:240px',
        'pointer-events:none',
        'opacity:0',
        'transition:opacity .15s ease',
        'white-space:normal',
    ].join(';');
    document.body.appendChild(tip);

    function showTip(text, x, y) {
        tip.textContent = text;
        // Position above the cursor; keep within viewport
        var vw = window.innerWidth;
        var left = Math.min(x + 12, vw - 260);
        tip.style.left  = left + 'px';
        tip.style.top   = (y - tip.offsetHeight - 12) + 'px';
        tip.style.opacity = '1';
    }

    function hideTip() {
        tip.style.opacity = '0';
    }

    // Use event delegation so tooltips still work after AJAX month changes
    document.addEventListener('mouseover', function(e) {
        var dot = e.target.closest('.bsg-cal-event-dot');
        if (!dot) return;
        var text = dot.getAttribute('data-tooltip') || dot.textContent.trim();
        if (!text) return;
        var rect = dot.getBoundingClientRect();
        showTip(text, rect.left, rect.top);
    });

    document.addEventListener('mouseout', function(e) {
        if (!e.target.closest('.bsg-cal-event-dot')) return;
        hideTip();
    });

    document.addEventListener('mousemove', function(e) {
        if (!e.target.closest('.bsg-cal-event-dot')) return;
        var vw = window.innerWidth;
        tip.style.left = Math.min(e.clientX + 14, vw - 260) + 'px';
        tip.style.top  = (e.clientY - tip.offsetHeight - 14) + 'px';
    });
})();
</script>

<?php get_footer(); ?>
