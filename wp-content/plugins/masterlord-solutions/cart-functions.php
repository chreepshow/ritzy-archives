<?php
add_action('woocommerce_remove_cart_item', 'remove_associated_rent_post_from_cart', 10, 2);
add_action('woocommerce_before_calculate_totals', 'custom_price_for_rent_product');

function remove_associated_rent_post_from_cart($cart_item_key, $cart)
{
    $user_id = get_current_user_id(); // Get the current user ID
    if ($user_id) {
        $product_id = $cart->cart_contents[$cart_item_key]['product_id']; // Get the product ID of the item being removed

        // Find the rent post associated with this user and product
        $rent_post_id = get_rent_post_by_user_id_and_product_id($user_id, $product_id); // Assuming you have this function from previous discussions
        if ($rent_post_id) {
            delete_rent_and_meta_for_user($rent_post_id, $user_id); // Delete the rent post and associated user meta
        }
    }
}

function custom_price_for_rent_product($cart)
{
    if (is_admin() && !defined('DOING_AJAX')) {
        return;
    }

    if (did_action('woocommerce_before_calculate_totals') >= 2) {
        return;
    }

    $user_id = get_current_user_id();
    if (!$user_id) {
        return;
    }

    // Loop through the cart items
    foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
        // Check if the cart item is the specific product you want to modify
        $product_id = $cart_item['product_id'];

        // Assuming get_rent_post_by_user_id_and_product_id() checks if the product is a rent product
        $active_rent_id = get_rent_id_of_user($user_id);
        $rent_id_of_user_and_product = get_rent_post_by_user_id_and_product_id($user_id, $product_id);
        if ($active_rent_id && $rent_id_of_user_and_product && $active_rent_id == $rent_id_of_user_and_product) {
            $rent_status = get_rent_status_by_id($active_rent_id);
            if ($rent_status == RENT_STATUS_IN_CART) {
                // Set the price to 0 (or any other price)
                $cart_item['data']->set_price(0);
            }
        }
    }
}
