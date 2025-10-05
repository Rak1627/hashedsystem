<?php
/**
 * Archive Template for Events
 */

get_header();
?>

<div class="events-container">
    <h1><?php post_type_archive_title(); ?></h1>

    <?php if (have_posts()) : ?>

    <div class="events-grid">
        <?php while (have_posts()) : the_post();
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
                    <?php _e('View Details', 'hashedsystem'); ?>
                </a>
            </div>
        </div>

        <?php endwhile; ?>
    </div>

    <?php
    // Pagination
    the_posts_pagination(array(
        'mid_size' => 2,
        'prev_text' => __('&laquo; Previous', 'hashedsystem'),
        'next_text' => __('Next &raquo;', 'hashedsystem'),
    ));
    ?>

    <?php else : ?>

    <div class="no-events">
        <p><?php _e('No events found.', 'hashedsystem'); ?></p>
    </div>

    <?php endif; ?>

</div>

<?php
get_footer();
