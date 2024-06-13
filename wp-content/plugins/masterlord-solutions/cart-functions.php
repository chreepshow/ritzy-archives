<?php
add_action('woocommerce_remove_cart_item', 'remove_associated_rent_post_from_cart', 10, 2);


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
