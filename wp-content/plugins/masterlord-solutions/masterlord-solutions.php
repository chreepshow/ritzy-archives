<?php

/**
 * Plugin Name: Masterlord Solutions
 * Plugin URI: https://masterlorsolutions.com/
 * Description: This is a plugin that does something with WooCommerce
 * Version: 1.0.0
 * Author: Peter Koppany
 * Author URI: https://masterlorsolutions.com/
 * License: GPL2
 */

add_action('woocommerce_single_product_summary', 'custom_product_description', 20);
function custom_product_description()
{
    global $product;
    // Assuming the class is loaded and available
    $membership = new Membership_For_Woocommerce_Public('', '');

    // Check if the user has a membership
    $user_id = get_current_user_id();
    $is_member_meta = get_user_meta($user_id, 'is_member');
    $current_memberships = get_user_meta($user_id, 'mfw_membership_id', true);
    // -------------------------------------------------------------------------------------------------------
    // TODO
    // Megvannak a felhasználó tagságai, ki kell deríteni, hogy melyiknek van aktív státusza
    // és azt is ellenőrizni kell, hogy a product, amit néz az elérhető-e ebben a tagságban,
    // illetve, hogy van-e éppen rentje, ez is 1 get_user_meta kérés kéne, hogy legyen.
    // Ha minden stimmel, akkor megjeleníthetjük neki a gombot, egyébként nem.
    // Ha rákattint a gombra, akkor el kéne menteni user metába, a product id-ját egy rented_product_id néven
    // és azt a metát is, hogy has_active_rent.

    //Én innen lopom a kódokat
    //wp-content\plugins\membership-for-woocommerce\public\class-membership-for-woocommerce-public.php
    //wp-content\plugins\membership-for-woocommerce\membership-for-woocommerce.php
    // -------------------------------------------------------------------------------------------------------

    foreach ($current_memberships as $key => $membership_id) {

        if ('publish' == get_post_status($membership_id) || 'draft' == get_post_status($membership_id)) {
            $membership_status = wps_membership_get_meta_data($membership_id, 'member_status', true);
            console_log2("membership_status", $membership_status);
            // Get Saved Plan Details.
            $membership_plan = wps_membership_get_meta_data($membership_id, 'plan_obj', true);
            console_log2("membership_plan", $membership_plan);
            // if (!empty($membership_plan->ID)) {
            //     array_push($all_member_plans, $membership_plan->ID);
            // }


            // $accessible_prod = !empty($membership_plan['wps_membership_plan_target_ids']) ? maybe_unserialize($membership_plan['wps_membership_plan_target_ids']) : array();
            // $accessible_cat  = !empty($membership_plan['wps_membership_plan_target_categories']) ? maybe_unserialize($membership_plan['wps_membership_plan_target_categories']) : array();
            // $accessible_tag  = !empty($membership_plan['wps_membership_plan_target_tags']) ? maybe_unserialize($membership_plan['wps_membership_plan_target_tags']) : array();

            // if (in_array($product->get_id(), $accessible_prod) || (!empty($accessible_cat) && has_term($accessible_cat, 'product_cat')) || (!empty($accessible_tag) && has_term($accessible_tag, 'product_tag'))) {

            //     $access = true;

            //     if (!empty($membership_status) && in_array($membership_status, array('complete'))) {
            //         $access = true;

            //         array_push($all_member_tag, $product->get_id());
            //     }
            //     if (!empty($membership_status) && in_array($membership_status, array('expired'))) {
            //         $access = false;
            //     } elseif ('pending' == $membership_status || 'hold' == $membership_status && 'publish' == $membership_plan['post_status']) {

            //         $this->under_review_products = $this->under_review_products ? $this->under_review_products : array();
            //         array_push($this->under_review_products, $product->get_id());
            //         array_unique($this->under_review_products, $product->get_id());
            //         $access = true;
            //     }
            // } else {
            //     $access = false;
            // }
        }
    }

    if ($product->is_in_stock() && $is_member_meta) {
        echo '<p>This product is in stock!</p>';
        echo '<button type="button">Rent this awesome bag!</button>';
    } else {
        echo '<p>Sorry, this product is out of stock or you do not have a membership.</p>';
    }
}

function console_log2($prefix, $data)
{
    echo "<script>console.log(" . json_encode($prefix) . ", " . json_encode($data) . ");</script>";
}

function console_log1($data)
{
    echo "<script>console.log( " . json_encode($data) . ");</script>";
}
