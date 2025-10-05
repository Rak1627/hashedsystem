<?php
/**
 * Template Name: Events Listing
 * Description: Display all upcoming events with pagination
 */

get_header();
?>

<div class="events-container">
    <h1><?php _e('Upcoming Events', 'hashedsystem'); ?></h1>

    <?php
    // Get current page
    $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;

    // Query upcoming events
    $args = array(
        'post_type' => 'event',
        'posts_per_page' => 6,
        'paged' => $paged,
        'meta_query' => array(
            array(
                'key' => '_event_date',
                'value' => current_time('mysql'),
                'compare' => '>=',
                'type' => 'DATETIME'
            )
        ),
        'meta_key' => '_event_date',
        'orderby' => 'meta_value',
        'order' => 'ASC'
    );

    $events_query = new WP_Query($args);

    if ($events_query->have_posts()) :
    ?>

    <div class="events-grid">
        <?php while ($events_query->have_posts()) : $events_query->the_post();
            $event_date = get_post_meta(get_the_ID(), '_event_date', true);
            $event_location = get_post_meta(get_the_ID(), '_event_location', true);
            $event_capacity = get_post_meta(get_the_ID(), '_event_capacity', true);
            $registered_attendees = ERS_Database::get_total_attendees(get_the_ID());
            $remaining_seats = $event_capacity - $registered_attendees;
            $is_full = $remaining_seats <= 0;
        ?>

        <div class="event-card">
            <?php if (has_post_thumbnail()) : ?>
                <div class="event-image">
                    <?php the_post_thumbnail('medium'); ?>
                </div>
            <?php endif; ?>

            <div class="event-content">
                <h2 class="event-title"><?php the_title(); ?></h2>

                <div class="event-meta">
                    <p><strong>📅 Date:</strong>
                        <?php
                        if (!empty($event_date)) {
                            echo date('F j, Y, g:i a', strtotime($event_date));
                        } else {
                            echo 'Date not set';
                        }
                        ?>
                    </p>
                    <p><strong>📍 Location:</strong> <?php echo esc_html($event_location); ?></p>
                </div>

                <div class="event-description">
                    <?php echo wp_trim_words(get_the_excerpt(), 20); ?>
                </div>

                <div class="event-capacity <?php echo $is_full ? 'full' : ''; ?>">
                    <strong>Capacity:</strong> <?php echo esc_html($registered_attendees . ' / ' . $event_capacity); ?>
                    <?php if ($is_full) : ?>
                        <span style="color: #c62828; font-weight: bold;"> - FULL</span>
                    <?php else : ?>
                        <span style="color: #4caf50; font-weight: bold;"> - <?php echo $remaining_seats; ?> seats left</span>
                    <?php endif; ?>
                </div>

                <a href="<?php the_permalink(); ?>" class="event-read-more">
                    <?php _e('Read More', 'hashedsystem'); ?>
                </a>
            </div>
        </div>

        <?php endwhile; ?>
    </div>

    <?php
    // Pagination
    if ($events_query->max_num_pages > 1) :
    ?>
    <div class="events-pagination">
        <?php
        echo paginate_links(array(
            'base' => str_replace(999999999, '%#%', esc_url(get_pagenum_link(999999999))),
            'total' => $events_query->max_num_pages,
            'current' => max(1, $paged),
            'format' => '?paged=%#%',
            'prev_text' => __('&laquo; Previous', 'hashedsystem'),
            'next_text' => __('Next &raquo;', 'hashedsystem'),
        ));
        ?>
    </div>
    <?php endif; ?>

    <?php
    wp_reset_postdata();

    else :
    ?>
    <div class="no-events">
        <p><?php _e('No upcoming events found.', 'hashedsystem'); ?></p>
    </div>
    <?php endif; ?>

</div>

<?php
get_footer();
