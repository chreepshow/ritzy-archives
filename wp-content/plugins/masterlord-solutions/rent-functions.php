<?php
add_action('init', 'register_rent_post_type');
add_action('init', 'define_custom_rent_statuses');
add_action('add_meta_boxes', 'rent_details_metabox');
add_action('save_post', 'save_rent_details');
add_action('before_delete_post', 'remove_user_meta_on_rent_deletion');
add_filter('manage_rent_posts_columns', 'add_rent_details_columns');
add_action('manage_rent_posts_custom_column', 'rent_details_columns_content', 10, 2);

const RENT_ID_META_KEY = 'rent_id';
const RENT_POST_TYPE = 'rent';

const RENT_STATUS_ACTIVE = 'active';
const RENT_STATUS_CANCELLED = 'cancelled';
const RENT_STATUS_DELIVERING = 'delivering';
const RENT_STATUS_DELIVERED = 'delivered';
const RENT_STATUS_DRAFT = 'draft';
const RENT_STATUS_IN_CART = 'in_cart';

const RENT_STATUSES_WITH_LABELS = [
    RENT_STATUS_ACTIVE => [
        'label' => 'Active',
        'label_count' => 'Active (%s)'
    ],
    RENT_STATUS_CANCELLED => [
        'label' => 'Cancelled',
        'label_count' => 'Cancelled (%s)'
    ],
    RENT_STATUS_DELIVERING => [
        'label' => 'Delivering',
        'label_count' => 'Delivering (%s)'
    ],
    RENT_STATUS_DELIVERED => [
        'label' => 'Delivered',
        'label_count' => 'Delivered (%s)'
    ],
    RENT_STATUS_DRAFT => [
        'label' => 'Draft',
        'label_count' => 'Draft (%s)'
    ],
    RENT_STATUS_IN_CART => [
        'label' => 'In Cart',
        'label_count' => 'In Cart (%s)'
    ],
];

function register_rent_post_type()
{
    $labels = array(
        'name' => 'Rents',
        'singular_name' => 'Rent',
        'add_new' => 'Add New Rent',
        'add_new_item' => 'Add New Rent',
        'all_items' => 'All Rents',
    );

    $args = array(
        'public' => true,
        'label'  => 'Rents',
        'labels' => $labels,
        'supports' => array('title', 'editor', 'custom-fields'),
        'menu_icon' => 'dashicons-calendar',
    );
    register_post_type('rent', $args);
}

function create_rent_post($user_id, $product_id, $status)
{
    $postarr = array(
        'post_type' => 'rent',
        'post_title' => 'Rent for User ' . $user_id,
        'post_status' => $status,
        'meta_input' => array(
            'user_id' => $user_id,
            'product_id' => $product_id,
        ),
    );
    $post_id = wp_insert_post($postarr);
    return $post_id;
}

function get_rent_status_by_id($rent_id)
{
    $post_status = get_post_status($rent_id);
    if (!$post_status) {
        return false; // Return false if the rent ID does not exist or there was an error
    }
    return $post_status;
}

function update_rent_status($rent_post_id, $new_status)
{
    $postarr = array(
        'ID' => $rent_post_id,
        'post_status' => $new_status,
    );
    wp_update_post($postarr);
}

function update_rent_id_of_user($user_id, $rent_id)
{
    update_user_meta($user_id, RENT_ID_META_KEY, $rent_id);
}

function get_rent_id_of_user($user_id)
{
    return get_user_meta($user_id, RENT_ID_META_KEY, true);
}

function remove_rent_id_of_user($user_id)
{
    delete_user_meta($user_id, RENT_ID_META_KEY);
}

function remove_user_meta_on_rent_deletion($post_id)
{
    // Check if the post being deleted is of the 'rent' post type
    if (get_post_type($post_id) === RENT_POST_TYPE) {
        // Get the user ID associated with this rent post
        $user_id = get_post_meta($post_id, 'user_id', true); // Assuming 'user_id' is the meta key

        // Check if a user ID was found
        if (!empty($user_id)) {
            // Delete the user meta associated with this rent post
            remove_rent_id_of_user($user_id);
        }
    }
}

function rent_details_metabox()
{
    add_meta_box(
        'rent_details', // ID of the metabox
        'Rent Details', // Title of the metabox
        'rent_details_metabox_callback', // Callback function
        RENT_POST_TYPE, // Post type
        'normal', // Context
        'high' // Priority
    );
}

function rent_details_metabox_callback($post)
{
    // Nonce field for security
    wp_nonce_field('save_rent_details', 'rent_details_nonce');

    // Get post meta
    $user_id = get_post_meta($post->ID, 'user_id', true);
    $product_id = get_post_meta($post->ID, 'product_id', true);
    $current_status = get_post_status($post->ID, 'rent_status', true);

    // User ID field
    echo '<p><label for="user_id">User ID:</label>';
    echo '<input type="text" id="user_id" name="user_id" value="' . esc_attr($user_id) . '" /></p>';

    // Product ID field
    echo '<p><label for="product_id">Product ID:</label>';
    echo '<input type="text" id="product_id" name="product_id" value="' . esc_attr($product_id) . '" /></p>';

    // Status dropdown
    echo '<select name="rent_status" id="rent_status">';
    foreach (RENT_STATUSES_WITH_LABELS as $status_key => $status_info) {
        echo '<option value="' . esc_attr($status_key) . '"' . selected($current_status, $status_key, false) . '>' . esc_html($status_info['label']) . '</option>';
    }
    echo '</select>';
}

function save_rent_details($post_id)
{
    // Check nonce for security
    if (!isset($_POST['rent_details_nonce']) || !wp_verify_nonce($_POST['rent_details_nonce'], 'save_rent_details')) {
        return;
    }

    // Check if the current user has permission to edit the post
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    // Avoid triggering action when updating the post programmatically
    remove_action('save_post', 'save_rent_details');

    // Save User ID
    if (isset($_POST['user_id'])) {
        update_post_meta($post_id, 'user_id', sanitize_text_field($_POST['user_id']));
    }

    // Save Product ID
    if (isset($_POST['product_id'])) {
        update_post_meta($post_id, 'product_id', sanitize_text_field($_POST['product_id']));
    }

    // Check if rent_status is set in POST request
    if (isset($_POST['rent_status']) && array_key_exists($_POST['rent_status'], RENT_STATUSES_WITH_LABELS)) {
        // Update the rent status
        wp_update_post(array(
            'ID' => $post_id,
            'post_status' => sanitize_text_field($_POST['rent_status'])
        ));
    }

    // Re-hook this function
    add_action('save_post', 'save_rent_details');
}

function add_rent_details_columns($columns)
{
    $columns['user_id'] = 'User ID';
    $columns['product_id'] = 'Product ID';
    $columns['status'] = 'Status';
    return $columns;
}

function rent_details_columns_content($column, $post_id)
{
    switch ($column) {
        case 'user_id':
            echo get_post_meta($post_id, 'user_id', true);
            break;
        case 'product_id':
            echo get_post_meta($post_id, 'product_id', true);
            break;
        case 'status':
            $post_status = get_post_status($post_id);
            echo ucfirst($post_status);
            break;
    }
}

function define_custom_rent_statuses()
{
    foreach (RENT_STATUSES_WITH_LABELS as $status_key => $status_info) {
        register_post_status($status_key, array(
            'label'                     => _x($status_info['label'], 'rent'),
            'public'                    => true,
            'exclude_from_search'       => false,
            'show_in_admin_all_list'    => true,
            'show_in_admin_status_list' => true,
            'label_count'               => _n_noop($status_info['label_count'], $status_info['label_count']),
        ));
    }
}
