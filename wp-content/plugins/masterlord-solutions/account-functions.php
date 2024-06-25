<?php
// Add a new menu item to the My Account page
add_filter('woocommerce_account_menu_items', 'add_rent_posts_menu_item', 40);
function add_rent_posts_menu_item($items)
{
    // Define the new menu item
    $new_item = ['rent-posts' => __('My Rented Bag', 'text-domain')];

    // Find the position of the 'Orders' menu item
    $orders_position = array_search('orders', array_keys($items)) + 1;

    // Split the array into two parts: before and after 'Orders'
    $items_before = array_slice($items, 0, $orders_position, true);
    $items_after = array_slice($items, $orders_position, null, true);

    // Insert the new item in between
    $items = $items_before + $new_item + $items_after;

    return $items;
}

// Register new endpoint to use for My Account page
add_filter('woocommerce_get_query_vars', 'add_rent_posts_query_vars', 0);
function add_rent_posts_query_vars($vars)
{
    $vars['rent-posts'] = 'rent-posts'; // 'rent-posts' is the endpoint
    return $vars;
}

// Add endpoint
add_action('init', 'add_rent_posts_endpoint');
function add_rent_posts_endpoint()
{
    add_rewrite_endpoint('rent-posts', EP_ROOT | EP_PAGES);
}

// Handle the display of the new endpoint content
add_action('woocommerce_account_rent-posts_endpoint', 'rent_posts_endpoint_content');
function rent_posts_endpoint_content()
{
    $user_id = get_current_user_id();
    // Query the rent posts for the current user
    $args = [
        'post_type' => RENT_POST_TYPE, // Use your custom post type
        'author' => $user_id,
        // Add any additional query parameters you need
    ];
    $rent_posts = new WP_Query($args);

    // Start outputting your HTML
    echo '<h3>' . __('My Rented Bag', 'text-domain') . '</h3>';
    if ($rent_posts->have_posts()) {
        echo '<table class="woocommerce-orders-table woocommerce-MyAccount-orders shop_table shop_table_responsive my_account_orders account-orders-table">';
        echo '<thead><tr><th>' . __('Rent ID', 'text-domain') . '</th><th>' . __('Product Name', 'text-domain') . '</th><th>' . __('Status', 'text-domain') . '</th></tr></thead>';
        echo '<tbody>';

        // Loop through the rent posts
        while ($rent_posts->have_posts()) {
            $rent_posts->the_post();
            $rent_post_id = get_the_ID();
            // Retrieve product_id from the rent post's meta
            $product_id = get_product_id_of_rent($rent_post_id);
            // Get the product name by product_id
            $product_name = get_the_title($product_id);
            $rent_status = get_rent_status_by_id($rent_post_id);
            $status_label = isset(RENT_STATUSES_WITH_LABELS[$rent_status]) ? RENT_STATUSES_WITH_LABELS[$rent_status]['label'] : 'Unknown'; // Default to 'Unknown' if status not found

            echo '<tr>';
            echo '<td>' . $rent_post_id . '</td>';
            echo '<td>' . esc_html($product_name) . '</td>'; // Display the product name
            echo '<td>' . esc_html($status_label) . '</td>';
            echo '</tr>';
        }
        echo '</tbody></table>';
    } else {
        echo '<p>' . __('No rent posts found.', 'text-domain') . '</p>';
    }
    wp_reset_postdata();
}

// // Ensure the new query var doesn't interfere with WooCommerce or WordPress
// add_filter('request', 'rent_posts_query_vars_filter');
// function rent_posts_query_vars_filter($vars)
// {
//     $vars = array_merge($vars, array('rent-posts' => ''));
//     return $vars;
// }