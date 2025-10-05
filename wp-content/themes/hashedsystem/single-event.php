<?php
/**
 * Single Event Template
 */

get_header();
?>

<div class="single-event-container">
    <?php while (have_posts()) : the_post();
        $event_id = get_the_ID();
        $event_date = get_post_meta($event_id, '_event_date', true);
        $event_location = get_post_meta($event_id, '_event_location', true);
        $event_capacity = get_post_meta($event_id, '_event_capacity', true);
        $registered_attendees = ERS_Database::get_total_attendees($event_id);
        $remaining_seats = ERS_Registration::get_remaining_seats($event_id);
        $is_full = ERS_Registration::is_event_full($event_id);
    ?>

    <article id="event-<?php the_ID(); ?>" <?php post_class(); ?>>
        <div class="event-header">
            <h1><?php the_title(); ?></h1>

            <?php if (has_post_thumbnail()) : ?>
                <div class="event-featured-image">
                    <?php the_post_thumbnail('full', array('class' => 'event-featured-image')); ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="event-details">
            <div class="event-detail-item">
                <strong>📅 Event Date:</strong>
                <span>
                    <?php
                    if (!empty($event_date)) {
                        echo date('F j, Y, g:i a', strtotime($event_date));
                    } else {
                        echo 'Date not set';
                    }
                    ?>
                </span>
            </div>

            <div class="event-detail-item">
                <strong>📍 Location:</strong>
                <span><?php echo esc_html($event_location); ?></span>
            </div>

            <div class="event-detail-item">
                <strong>👥 Capacity:</strong>
                <span>
                    <?php echo esc_html($event_capacity); ?> attendees
                    <span class="remaining-seats <?php echo $is_full ? 'full' : ($remaining_seats < 10 ? 'low' : ''); ?>">
                        <?php
                        if ($is_full) {
                            echo 'FULL';
                        } else {
                            echo $remaining_seats . ' seats remaining';
                        }
                        ?>
                    </span>
                </span>
            </div>

            <div class="event-detail-item">
                <strong>✅ Registered:</strong>
                <span><?php echo esc_html($registered_attendees); ?> attendees</span>
            </div>
        </div>

        <div class="event-content">
            <h2><?php _e('Event Description', 'hashedsystem'); ?></h2>
            <?php the_content(); ?>
        </div>

        <?php
        // Google Maps Integration (Optional Feature)
        if ($event_location) :
        ?>
        <div class="event-location-map">
            <h2><?php _e('Event Location', 'hashedsystem'); ?></h2>
            <iframe
                class="event-map"
                frameborder="0"
                scrolling="no"
                marginheight="0"
                marginwidth="0"
                src="https://maps.google.com/maps?q=<?php echo urlencode($event_location); ?>&output=embed"
                allowfullscreen>
            </iframe>
        </div>
        <?php endif; ?>

        <?php if (!$is_full) : ?>
        <!-- Registration Form -->
        <div class="event-registration-form">
            <h3><?php _e('Register for this Event', 'hashedsystem'); ?></h3>

            <div class="form-message" style="display: none;"></div>

            <form id="event-registration-form" method="post">
                <input type="hidden" name="event_id" value="<?php echo esc_attr($event_id); ?>">

                <div class="form-group">
                    <label for="name"><?php _e('Full Name *', 'hashedsystem'); ?></label>
                    <input type="text" id="name" name="name" required placeholder="Enter your full name">
                </div>

                <div class="form-group">
                    <label for="email"><?php _e('Email Address *', 'hashedsystem'); ?></label>
                    <input type="email" id="email" name="email" required placeholder="Enter your email address">
                </div>

                <div class="form-group">
                    <label for="attendees"><?php _e('Number of Attendees *', 'hashedsystem'); ?></label>
                    <input type="number" id="attendees" name="attendees" value="1" min="1" max="<?php echo esc_attr($remaining_seats); ?>" required>
                    <small><?php printf(__('Maximum %d attendees', 'hashedsystem'), $remaining_seats); ?></small>
                </div>

                <button type="submit" class="submit-btn"><?php _e('Register Now', 'hashedsystem'); ?></button>
            </form>
        </div>
        <?php else : ?>
        <!-- Event Full Message -->
        <div class="event-full-message">
            <h3><?php _e('Event is Full', 'hashedsystem'); ?></h3>
            <p><?php _e('Sorry, this event has reached its maximum capacity. Registration is now closed.', 'hashedsystem'); ?></p>
        </div>
        <?php endif; ?>

    </article>

    <?php endwhile; ?>
</div>

<?php
get_footer();
