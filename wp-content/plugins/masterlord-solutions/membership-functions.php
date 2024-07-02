<?php
// THESE SHOULD BE EQUAL TO THE TITLE OF THE MEMBERHSIP PLANS
const TEST_MEMBERSHIP_PLAN = 'Test Membership';
const LOWEST_MEMBERSHIP_PLAN = 'Bronze Membership';
const MID_MEMBERSHIP_PLAN = 'Silver Membership';
const HIGHEST_MEMBERSHIP_PLAN = 'Gold Membership';

const MEMBERSHIP_PLANS_PRIORITY = [
    TEST_MEMBERSHIP_PLAN,
    LOWEST_MEMBERSHIP_PLAN,
    MID_MEMBERSHIP_PLAN,
    HIGHEST_MEMBERSHIP_PLAN
];

function get_all_membership_plans() {
    $results = get_posts(
        array(
            'post_type' => 'wps_cpt_membership',
            'post_status' => 'publish',
            'meta_key' => 'wps_membership_plan_target_ids',
            'numberposts' => -1,
        )
    );

    $final_results = array();

    foreach ( $results as $key => $value ) {
        foreach ( $value as $key1 => $value1 ) {
            $final_results[ $key ][ $key1 ] = $value1;
        }
    }
    return $final_results;
}

function is_product_accessible_in_membership_plan($product_id, $membership_plan_id)
{
    $target_ids     = wps_membership_get_meta_data( $membership_plan_id, 'wps_membership_plan_target_ids', true );
    $target_cat_ids = wps_membership_get_meta_data( $membership_plan_id, 'wps_membership_plan_target_categories', true );
    $target_tag_ids  = wps_membership_get_meta_data( $membership_plan_id, 'wps_membership_plan_target_tags', true );

    if(! empty( $target_ids ) && is_array( $target_ids )) {
        if( in_array( $product_id, $target_ids ) ) {
            return true;
        }
    }

    if(! empty( $target_cat_ids ) && is_array( $target_cat_ids )) {
        if( has_term( $target_cat_ids, 'product_cat', $product_id ) ) {
            return true;
        }
    }

    if(! empty( $target_tag_ids ) && is_array( $target_tag_ids )) {
        if( has_term( $target_tag_ids, 'product_tag', $product_id ) ) {
            return true;
        }
    }

    return false;
}

function is_product_accessible_in_users_membership_plan($product_id, $membership_plan)
{
    $accessible_prod = accessible_products_in_membership_plan($membership_plan);
    $accessible_cat = accessible_categories_in_membership_plan($membership_plan);
    $accessible_tag = accessible_tags_in_membership_plan($membership_plan);

    if (in_array($product_id, $accessible_prod) || (!empty($accessible_cat) && has_term($accessible_cat, 'product_cat')) || (!empty($accessible_tag) && has_term($accessible_tag, 'product_tag'))) {
        return true;
    }

    return false;
}

function get_membership_plan_by_name($membership_plans, $name)
{
    foreach ($membership_plans as $membership_plan) {
        if ($membership_plan['post_title'] === $name) {
            return $membership_plan;
        }
    }

    return null;
}

function accessible_products_in_membership_plan($membership_plan)
{
    return !empty($membership_plan['wps_membership_plan_target_ids']) ? maybe_unserialize($membership_plan['wps_membership_plan_target_ids']) : array();
}

function accessible_categories_in_membership_plan($membership_plan)
{
    return !empty($membership_plan['wps_membership_plan_target_categories']) ? maybe_unserialize($membership_plan['wps_membership_plan_target_categories']) : array();
}

function accessible_tags_in_membership_plan($membership_plan)
{
    return !empty($membership_plan['wps_membership_plan_target_tags']) ? maybe_unserialize($membership_plan['wps_membership_plan_target_tags']) : array();
}