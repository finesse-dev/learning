<?php
/**
 * Plugin Name: Custom Form Plugin
 * Description: A plugin to create and manage a custom form.
 * Version: 1.0
 * Author: Your Name
 */

// Hook to create the database table on plugin activation
register_activation_hook(__FILE__, 'cfp_create_table');

function cfp_create_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'custom_form_entries';
    
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        name tinytext NOT NULL,
        email varchar(100) NOT NULL,
        message text NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

// Hook to display the form shortcode
add_shortcode('custom_form', 'cfp_display_form');

function cfp_display_form() {
    ob_start();
    ?>
    <form id="customForm" method="post">
        <label for="cfp_name">Name:</label>
        <input type="text" id="cfp_name" name="cfp_name" required>
        <label for="cfp_email">Email:</label>
        <input type="email" id="cfp_email" name="cfp_email" required>
        <label for="cfp_message">Message:</label>
        <textarea id="cfp_message" name="cfp_message" required></textarea>
        <input type="submit" name="cfp_submit" value="Submit">
    </form>
    <?php
    cfp_handle_form_submission();
    return ob_get_clean();
}

function cfp_handle_form_submission() {
    if (isset($_POST['cfp_submit'])) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'custom_form_entries';
        $name = sanitize_text_field($_POST['cfp_name']);
        $email = sanitize_email($_POST['cfp_email']);
        $message = sanitize_textarea_field($_POST['cfp_message']);
        
        $wpdb->insert(
            $table_name,
            array(
                'name' => $name,
                'email' => $email,
                'message' => $message
            )
        );
    }
}

// Hook to add the admin menu
add_action('admin_menu', 'cfp_add_admin_menu');

function cfp_add_admin_menu() {
    add_menu_page('Custom Form Entries', 'Form Entries', 'manage_options', 'custom_form_entries', 'cfp_admin_page');
}

function cfp_admin_page() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'custom_form_entries';
    $entries = $wpdb->get_results("SELECT * FROM $table_name");
    ?>
    <div class="wrap">
        <h1>Custom Form Entries</h1>
        <table class="wp-list-table widefat fixed">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Message</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($entries as $entry) : ?>
                <tr>
                    <td><?php echo esc_html($entry->id); ?></td>
                    <td><?php echo esc_html($entry->name); ?></td>
                    <td><?php echo esc_html($entry->email); ?></td>
                    <td><?php echo esc_html($entry->message); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
}
