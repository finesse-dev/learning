<?php
/*
Plugin Name: Custom Content Plugin
Description: A plugin for managing custom content (images and text) in WordPress posts.
Version: 1.2
Author: Courtney Shea Smith
*/

// Enqueue scripts and styles
function custom_content_plugin_scripts() {
    if (is_admin()) {
        wp_enqueue_media();
    }
    wp_enqueue_script('custom-content-script', plugin_dir_url(__FILE__) . 'custom-content-script.js', array('jquery'), '1.2', true);
}
add_action('admin_enqueue_scripts', 'custom_content_plugin_scripts');

// AJAX callback to add WP editor
// function add_wp_editor_callback() {
//     $index = intval($_POST['index']);
//     ob_start();
//     wp_editor('', 'custom_text_' . $index, array(
//         'textarea_name' => 'custom_texts[' . $index . ']',
//         'textarea_rows' => 5
//     ));
//     echo '<button class="remove-text">Remove</button>';
//     $editor_html = ob_get_clean();
//     echo '<div class="content-item text-container">' . $editor_html . '</div>';
//     wp_die();
// }
function add_wp_editor_callback() {
    $index = intval($_POST['index']);
    ob_start();
    wp_editor('', 'custom_text_' . $index, array(
        'textarea_name' => 'custom_texts[' . $index . ']',
        'textarea_rows' => 5
    ));
    echo '<button class="remove-text">Remove</button>';
    $editor_html = ob_get_clean();
    echo '<div class="content-item text-container">' . $editor_html . '</div>';
    wp_die();
}

add_action('wp_ajax_add_wp_editor', 'add_wp_editor_callback');

// Add meta box for custom content
function custom_meta_box() {
    add_meta_box(
        'custom_meta_box',
        'Custom Content',
        'display_custom_meta_box',
        'post',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'custom_meta_box');

// Display custom meta box content
function display_custom_meta_box($post) {
    $content_data = get_post_meta($post->ID, '_custom_content_data', true) ?: [];
    ?>
    <div id="custom_content_container">
        <?php
        foreach ($content_data as $content_item) {
            if (isset($content_item['type'])) {
                if ($content_item['type'] === 'image') {
                    if (isset($content_item['id'])) {
                        $image_url = wp_get_attachment_image_url($content_item['id'], 'thumbnail');
                        if ($image_url) {
                            echo '<div class="content-item image-container"><img src="' . esc_url($image_url) . '" alt="Custom Image" style="max-width: 100%;"><button class="remove-image" data-id="' . $content_item['id'] . '">Remove</button></div>';
                        }
                    }
                } elseif ($content_item['type'] === 'text') {
                    $editor_id = 'custom_text_' . $content_item['id'];
                    $text_content = isset($content_item['text']) ? $content_item['text'] : '';
                    echo '<div class="content-item text-container">';
                    wp_editor($text_content, $editor_id, array(
                        'textarea_name' => 'custom_texts[' . $content_item['id'] . ']',
                        'textarea_rows' => 5
                    ));
                    echo '<button class="remove-text">Remove</button></div>';
                }
            }
        }
        ?>
    </div>
    <input type="button" id="upload_images_button" class="button" value="Upload Images">
    <input type="button" id="add_text_button" class="button" value="Add Text">
    <input type="hidden" id="custom_content_data" name="custom_content_data" value="<?php echo esc_attr(json_encode($content_data)); ?>">
    <input type="hidden" id="removed_content" name="removed_content" value="">
    <?php
}


// Save custom meta box data
function save_custom_meta_box_data($post_id) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    $new_content_data = isset($_POST['custom_content_data']) ? json_decode(stripslashes($_POST['custom_content_data']), true) : [];
    $existing_content_data = get_post_meta($post_id, '_custom_content_data', true) ?: [];
    $removed_content = isset($_POST['removed_content']) ? json_decode(stripslashes($_POST['removed_content']), true) : [];

    // Remove content items marked for deletion
    foreach ($removed_content as $removed_item) {
        $existing_content_data = array_filter($existing_content_data, function($item) use ($removed_item) {
            return !($item['type'] === $removed_item['type'] && $item['id'] === $removed_item['id']);
        });
    }

    // Update content data with text fields
    $text_data = isset($_POST['custom_texts']) ? $_POST['custom_texts'] : [];
    foreach ($existing_content_data as &$item) {
        if ($item['type'] === 'text' && isset($item['id']) && isset($text_data[$item['id']])) {
            $item['text'] = wp_kses_post($text_data[$item['id']]);
        }
    }

    // Merge new content data with existing content data
    $updated_content_data = array_merge($existing_content_data, $new_content_data);

    // Remove duplicate entries
    $unique_content_data = [];
    foreach ($updated_content_data as $item) {
        if (isset($item['type']) && isset($item['id'])) {
            $unique_key = $item['type'] . '_' . $item['id'];
            $unique_content_data[$unique_key] = $item;
        }
    }
    $unique_content_data = array_values($unique_content_data);

    // Ensure `text` key is present for text content
    foreach ($unique_content_data as &$item) {
        if (isset($item['type']) && $item['type'] === 'text' && !isset($item['text'])) {
            $item['text'] = ''; // Default to empty string if not set
        }
    }
    unset($item); // Break the reference

    update_post_meta($post_id, '_custom_content_data', $unique_content_data);
}


add_action('save_post', 'save_custom_meta_box_data');

// Display custom content using shortcode
function cstm_meta_imgs() {
    ob_start();

    $post_id = get_the_ID();
    $content_data = get_post_meta($post_id, '_custom_content_data', true) ?: [];

    if (!empty($content_data)) {
        echo '<div class="custom-content-container">';

        $lastContentType = '';

        foreach ($content_data as $content_item) {
            if (!isset($content_item['type'])) {
                continue; // Skip items without a type
            }

            if ($content_item['type'] === 'image') {
                if ($lastContentType !== 'image') {
                    echo '<div class="content-item images-wrapps">';
                }

                if (isset($content_item['id'])) {
                    $image_url = wp_get_attachment_image_url($content_item['id'], 'full');
                    if ($image_url) {
                        echo '<img src="' . esc_url($image_url) . '" alt="Custom Image" style="max-width: 100%;">';
                    }
                }

                $lastContentType = 'image';
            } elseif ($content_item['type'] === 'text') {
                if ($lastContentType === 'image') {
                    echo '</div>';
                }

                $text_content = isset($content_item['text']) ? esc_html($content_item['text']) : '';
                echo '<div class="content-item text-container"><div class="custom-text">' . $text_content . '</div></div>';

                $lastContentType = 'text';
            }
        }

        if ($lastContentType === 'image') {
            echo '</div>';
        }

        echo '</div>';

        echo '<style>
        .content-item.images-wrapps {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            column-gap: 10px;
            row-gap: 10px;
        }
        
        .content-item.images-wrapps > * {
            width: calc(50% - 5px);
            flex-grow: 1;
            object-fit: cover;
        }
        
        .content-item {
            margin: 20px;
        }
        .content-item {
            margin-left: 0 !important;
            margin-right: 0 !important;
        }
        @media (max-width:767px){
            .content-item {
                margin-left: 0 !important;
                margin-right: 0 !important;
            }
            
            .custom-text {
                font-size: 15px !important;
                line-height: 1.7 !important;
            }
        }
        </style>';
    }

    $output = ob_get_clean();
    return $output;
}
add_shortcode('meta_imgs', 'cstm_meta_imgs');
