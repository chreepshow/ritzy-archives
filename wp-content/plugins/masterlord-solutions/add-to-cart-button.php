<?php
// Hook into WordPress to ensure our override runs after the membership_for_woocommerce's plugin's filter is added.
add_action('plugins_loaded', 'override_membership_for_woocommerce_is_purchasabel_logic', 20);

function override_membership_for_woocommerce_is_purchasabel_logic()
{
    add_filter('woocommerce_is_purchasable', 'our_purchasable_filter', 10, 2);
}

function our_purchasable_filter($is_purchasable, $product)
{
    if (is_user_logged_in()) {
        return true;
    }
    return false;
}
