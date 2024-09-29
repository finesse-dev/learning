<?php 

// Add this code to your theme's functions.php or a custom plugin
function create_event_post_type() {
    $labels = array(
        'name'               => _x('Events', 'post type general name'),
        'singular_name'      => _x('Event', 'post type singular name'),
        'menu_name'          => _x('Events', 'admin menu'),
        'name_admin_bar'     => _x('Event', 'add new on admin bar'),
        'add_new'            => _x('Add New', 'event'),
        'add_new_item'       => __('Add New Event'),
        'new_item'           => __('New Event'),
        'edit_item'          => __('Edit Event'),
        'view_item'          => __('View Event'),
        'all_items'          => __('All Events'),
        'search_items'       => __('Search Events'),
        'not_found'          => __('No events found.'),
        'not_found_in_trash' => __('No events found in Trash.')
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => array('slug' => 'event'),
        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => false,
        'menu_position'      => null,
        'supports'           => array('title', 'editor', 'thumbnail', 'excerpt', 'custom-fields')
    );

    register_post_type('event', $args);
}

add_action('init', 'create_event_post_type');


function create_event_taxonomy() {
    $labels = array(
        'name'              => _x('Event Types', 'taxonomy general name'),
        'singular_name'     => _x('Event Type', 'taxonomy singular name'),
        'search_items'      => __('Search Event Types'),
        'all_items'         => __('All Event Types'),
        'parent_item'       => __('Parent Event Type'),
        'parent_item_colon' => __('Parent Event Type:'),
        'edit_item'         => __('Edit Event Type'),
        'update_item'       => __('Update Event Type'),
        'add_new_item'      => __('Add New Event Type'),
        'new_item_name'     => __('New Event Type Name'),
        'menu_name'         => __('Event Types'),
    );

    $args = array(
        'labels'            => $labels,
        'hierarchical'      => true, // Set to true for parent/child relationships
        'public'            => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array('slug' => 'event-type'),
    );

    register_taxonomy('event', array('event'), $args);
}

add_action('init', 'create_event_taxonomy');

function event_meta_box() {
    add_meta_box('event_details', 'Event Details', 'event_meta_box_callback', 'event', 'normal', 'high');
}
add_action('add_meta_boxes', 'event_meta_box');

function event_meta_box_callback($post) {
    wp_nonce_field('event_meta_box', 'event_meta_box_nonce');
    $date = get_post_meta($post->ID, '_event_date', true);

    echo '<label for="event_date">Event Date:</label>';
    echo '<input type="date" id="event_date" name="event_date" value="' . esc_attr($date) . '"/>';
}

function save_event_meta_box_data($post_id) {

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (isset($_POST['event_date'])) {
        update_post_meta($post_id, '_event_date', sanitize_text_field($_POST['event_date']));
    }
}
add_action('save_post', 'save_event_meta_box_data');
