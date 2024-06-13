<?php
add_action('woocommerce_order_status_completed', 'change_rent_post_status_on_purchase');

function change_rent_post_status_on_purchase($order_id)
{
    // Get the order object
    $order = wc_get_order($order_id);

    // Loop through order items
    foreach ($order->get_items() as $item) {
        // Get the product object
        $product_id = $item->get_product_id();
        $user_id = get_current_user_id();
        $rent_post_id = get_rent_post_by_user_id_and_product_id($user_id, $product_id);

        if ($rent_post_id) {
            update_rent_status($rent_post_id, RENT_STATUS_PURCHASED);
        }
    }
}
