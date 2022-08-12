<?php
// Manage custom time interval
add_filter('cron_schedules', 'sgma_add_cron_interval');
function sgma_add_cron_interval($schedules)
{
    $schedules['int_seconds'] = array(
        'interval' => 60 * 60,
        'display' => esc_html__('Every Day'));
    return $schedules;
}

// Manage hook
add_action('sgma_cron_hooks', 'sgma_cron_exec');
function sgma_cron_exec()
{
    //Insert category term
    $cat_term = term_exists('vergangene-veranstaltungen', 'event_kategorien');
    $cat_term_id = $cat_term['term_id']; // get numeric term id
    if (!$cat_term_id) {
        wp_insert_term(
            'Vergangene Veranstaltungen', // the term
            'event_kategorien', // the taxonomy
            array(
                'description' => 'Vergangene Veranstaltungen',
                'slug' => 'vergangene-veranstaltungen',
                // 'parent' => $cat_term_id,
            )
        );
    }

    // Post query
    $q_args = array(
        'post_type' => 'events',
    );

    $q_query = new WP_Query($q_args);

    if ($q_query->have_posts()):
        while ($q_query->have_posts()): $q_query->the_post();

            $event_date = get_field('workshop_or_event_date', get_the_ID());
            $expl = explode('/', $event_date);
            $new_date = $expl[0] . '-' . $expl[1] . '-' . $expl[2];
            $exp_time = strtotime($new_date) + (24*60*60);
            if (strtotime($exp_time) +  < time()) {
                // Set post term to post item
                // set false to reset all terms
                wp_set_post_terms(get_the_ID(), array(37), 'event_kategorien', true);
            }

        endwhile;
        wp_reset_postdata();
    endif;
}

// Cron event schedule
if (!wp_next_scheduled('sgma_cron_hooks')) {
    wp_schedule_event(time(), 'int_seconds', 'sgma_cron_hooks');
}
