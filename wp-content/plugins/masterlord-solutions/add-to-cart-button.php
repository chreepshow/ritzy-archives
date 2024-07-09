<?php
// Hook into WordPress to ensure our override runs after the membership_for_woocommerce's plugin's filter is added.
add_action('plugins_loaded', 'override_membership_for_woocommerce_is_purchasable_logic', 20);

function override_membership_for_woocommerce_is_purchasable_logic()
{
    add_filter('woocommerce_is_purchasable', 'our_purchasable_filter', 10, 2);
}

function our_purchasable_filter($is_purchasable, $product)
{
    $user_id = get_current_user_id();
    if($user_id) {
        $has_active_rent_id = get_rent_id_of_user($user_id);
        $rent_status = get_rent_status_by_id($has_active_rent_id);
        $rent_is_for_this_product = get_product_id_of_rent($has_active_rent_id) == $product -> get_id();

        // Ahhoz, hogy a termék ne legyen megvásárolható akkor, amikor rentelni is szeretné a user, felül kell írni az is_purchasable fitlert
        // Ha a rent_status IN_CART és a rent a jelenlegi termékre vonatkozik, akkor a termék nem lehet megvásárolható, különben 0 Ft-ért kap kettőt
        // Az is_product pedig azért kell, hogy a többi oldalon ne szedje ki a woocommerce a terméket a kosárból azért, mert a felülírt filter miatt nem lehetne megvásárolni
        // Így csak a single product page-en nem lehet megvásárolni a terméket
        if($rent_status == RENT_STATUS_IN_CART && $rent_is_for_this_product && is_product()) {
            return false;
        }
    }
    return true;
}
