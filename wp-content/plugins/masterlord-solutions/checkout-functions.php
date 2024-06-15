<?php
add_action('woocommerce_thankyou', 'custom_function_for_order_received', 10, 1);

function custom_function_for_order_received($order_id)
{
    $order = wc_get_order($order_id);

    // Loop through order items
    foreach ($order->get_items() as $item) {
        // Get the product object
        $product = $item->get_product();
        $user_id = get_current_user_id();
        $rent_post_id = get_rent_post_by_user_id_and_product_id($user_id, $product->get_id());

        if ($rent_post_id) {
            update_rent_status($rent_post_id, RENT_STATUS_PURCHASED);
        }
    }
}
