<?php
/*
Plugin Name: Simple Event Manager
Description: Adds a simple event manager with calendar view.
Version: 1.0
Author: Cryptoball cryptoball7@gmail.com
*/

// Register Event Custom Post Type
function sem_register_event_post_type() {
    register_post_type('sem_event', [
        'labels' => [
            'name' => 'Events',
            'singular_name' => 'Event'
        ],
        'public' => true,
        'has_archive' => true,
        'supports' => ['title', 'editor'],
        'menu_icon' => 'dashicons-calendar',
    ]);
}
add_action('init', 'sem_register_event_post_type');

// Add Meta Boxes
function sem_add_event_meta_boxes() {
    add_meta_box('sem_event_details', 'Event Details', 'sem_event_meta_box_callback', 'sem_event');
}
add_action('add_meta_boxes', 'sem_add_event_meta_boxes');

function sem_event_meta_box_callback($post) {
    $date = get_post_meta($post->ID, '_sem_event_date', true);
    $time = get_post_meta($post->ID, '_sem_event_time', true);
    $location = get_post_meta($post->ID, '_sem_event_location', true);
    ?>
    <p>
        <label>Date: <input type="date" name="sem_event_date" value="<?php echo esc_attr($date); ?>" /></label>
    </p>
    <p>
        <label>Time: <input type="time" name="sem_event_time" value="<?php echo esc_attr($time); ?>" /></label>
    </p>
    <p>
        <label>Location: <input type="text" name="sem_event_location" value="<?php echo esc_attr($location); ?>" /></label>
    </p>
    <?php
}

function sem_save_event_meta($post_id) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (isset($_POST['sem_event_date'])) update_post_meta($post_id, '_sem_event_date', sanitize_text_field($_POST['sem_event_date']));
    if (isset($_POST['sem_event_time'])) update_post_meta($post_id, '_sem_event_time', sanitize_text_field($_POST['sem_event_time']));
    if (isset($_POST['sem_event_location'])) update_post_meta($post_id, '_sem_event_location', sanitize_text_field($_POST['sem_event_location']));
}
add_action('save_post', 'sem_save_event_meta');

// Enqueue Scripts and Styles
function sem_enqueue_scripts() {
    if (!is_singular() && !is_page()) return;
    wp_enqueue_style('fullcalendar-css', 'https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css');
    wp_enqueue_script('fullcalendar-js', 'https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js', [], null, true);
    wp_enqueue_script('sem-calendar-js', plugin_dir_url(__FILE__) . 'js/sem-calendar.js', ['fullcalendar-js'], null, true);
    wp_localize_script('sem-calendar-js', 'sem_events', ['events' => sem_get_events()]);
}
add_action('wp_enqueue_scripts', 'sem_enqueue_scripts');

// Shortcode
function sem_calendar_shortcode() {
    return '<div id="sem-calendar"></div>';
}
add_shortcode('simple_event_calendar', 'sem_calendar_shortcode');

// Get Events as JSON
function sem_get_events() {
    $args = [
        'post_type' => 'sem_event',
        'posts_per_page' => -1
    ];
    $query = new WP_Query($args);
    $events = [];

    while ($query->have_posts()) {
        $query->the_post();
        $events[] = [
            'title' => get_the_title(),
            'start' => get_post_meta(get_the_ID(), '_sem_event_date', true) . 'T' . get_post_meta(get_the_ID(), '_sem_event_time', true),
            'url' => get_permalink()
        ];
    }
    wp_reset_postdata();

    return $events;
}
