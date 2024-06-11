<?php
add_action('init', 'register_rent_post_type');
add_action('init', 'define_custom_rent_statuses');

function register_rent_post_type()
{
    $args = array(
        'public' => true,
        'label'  => 'Rents',
        'supports' => array('title', 'editor', 'custom-fields'),
    );
    register_post_type('rent', $args);
}

function create_rent_post($user_id, $product_id, $status = 'draft')
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

function update_rent_status($rent_post_id, $new_status)
{
    $postarr = array(
        'ID' => $rent_post_id,
        'post_status' => $new_status,
    );
    wp_update_post($postarr);
}

add_action('add_meta_boxes', 'rent_details_metabox');
function rent_details_metabox()
{
    add_meta_box(
        'rent_details', // ID of the metabox
        'Rent Details', // Title of the metabox
        'rent_details_metabox_callback', // Callback function
        'rent', // Post type
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

    // User ID field
    echo '<p><label for="user_id">User ID:</label>';
    echo '<input type="text" id="user_id" name="user_id" value="' . esc_attr($user_id) . '" /></p>';

    // Product ID field
    echo '<p><label for="product_id">Product ID:</label>';
    echo '<input type="text" id="product_id" name="product_id" value="' . esc_attr($product_id) . '" /></p>';
}

add_action('save_post', 'save_rent_details');
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

    // Save User ID
    if (isset($_POST['user_id'])) {
        update_post_meta($post_id, 'user_id', sanitize_text_field($_POST['user_id']));
    }

    // Save Product ID
    if (isset($_POST['product_id'])) {
        update_post_meta($post_id, 'product_id', sanitize_text_field($_POST['product_id']));
    }
}

add_filter('manage_rent_posts_columns', 'add_rent_details_columns');
function add_rent_details_columns($columns)
{
    $columns['user_id'] = 'User ID';
    $columns['product_id'] = 'Product ID';
    $columns['status'] = 'Status';
    return $columns;
}

add_action('manage_rent_posts_custom_column', 'rent_details_columns_content', 10, 2);
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
    register_post_status('active', array(
        'label'                     => _x('Active', 'rent'),
        'public'                    => true,
        'exclude_from_search'       => false,
        'show_in_admin_all_list'    => true,
        'show_in_admin_status_list' => true,
        'label_count'               => _n_noop('Active (%s)', 'Active (%s)'),
    ));

    register_post_status('cancelled', array(
        'label'                     => _x('Cancelled', 'rent'),
        'public'                    => true,
        'exclude_from_search'       => false,
        'show_in_admin_all_list'    => true,
        'show_in_admin_status_list' => true,
        'label_count'               => _n_noop('Cancelled (%s)', 'Cancelled (%s)'),
    ));

    register_post_status('delivering', array(
        'label'                     => _x('Delivering', 'rent'),
        'public'                    => true,
        'exclude_from_search'       => false,
        'show_in_admin_all_list'    => true,
        'show_in_admin_status_list' => true,
        'label_count'               => _n_noop('Delivering (%s)', 'Delivering (%s)'),
    ));

    register_post_status('delivered', array(
        'label'                     => _x('Delivered', 'rent'),
        'public'                    => true,
        'exclude_from_search'       => false,
        'show_in_admin_all_list'    => true,
        'show_in_admin_status_list' => true,
        'label_count'               => _n_noop('Delivered (%s)', 'Delivered (%s)'),
    ));

    register_post_status('draft', array(
        'label'                     => _x('Draft', 'rent'),
        'public'                    => true,
        'exclude_from_search'       => false,
        'show_in_admin_all_list'    => true,
        'show_in_admin_status_list' => true,
        'label_count'               => _n_noop('Draft (%s)', 'Draft (%s)'),
    ));
}
