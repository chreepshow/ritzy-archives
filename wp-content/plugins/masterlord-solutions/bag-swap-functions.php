<?php

const BAG_SWAP_COUNT_META_KEY = 'bag_swap_count';

const TES_BAG_SWAP_COUNT = 500;
const LOWEST_BAG_SWAP_COUNT = 1;
const MID_BAG_SWAP_COUNT = 1;
const HIGHEST_BAG_SWAP_COUNT = 1;

add_action('wp_ajax_start_swap_bag', 'start_swap_bag');
add_action('wp_ajax_nopriv_start_swap_bag', 'start_swap_bag'); // For logged-out users

function update_user_bag_swap_count($user_id, $bag_swap_count)
{
    update_user_meta($user_id, BAG_SWAP_COUNT_META_KEY, $bag_swap_count);
}

function get_current_bag_swap_count_of_user($user_id)
{
    return get_user_meta($user_id, BAG_SWAP_COUNT_META_KEY, true);
}

function has_remaining_bag_swaps($user_id)
{
    $bag_swap_count = get_current_bag_swap_count_of_user($user_id);
    return $bag_swap_count > 0;
}

function decrement_bag_swap_count($user_id)
{
    if (!has_remaining_bag_swaps($user_id)) {
        return;
    }

    $bag_swap_count = get_current_bag_swap_count_of_user($user_id);
    $bag_swap_count--;
    update_user_bag_swap_count($user_id, $bag_swap_count);
}

function get_bag_swap_count_by_membership_plan($membership_plan_name)
{
    switch ($membership_plan_name) {
        case TEST_MEMBERSHIP_PLAN:
            return TES_BAG_SWAP_COUNT;
        case LOWEST_MEMBERSHIP_PLAN:
            return LOWEST_BAG_SWAP_COUNT;
        case MID_MEMBERSHIP_PLAN:
            return MID_BAG_SWAP_COUNT;
        case HIGHEST_MEMBERSHIP_PLAN:
            return HIGHEST_BAG_SWAP_COUNT;
        default:
            return 0;
    }
}

function reset_bag_swap_count_for_user($user_id)
{
    $active_membership_plan = get_active_membership_plan_of_user_or_null($user_id);
    if (!$active_membership_plan) {
        return;
    }

    $bag_swap_count = get_bag_swap_count_by_membership_plan($active_membership_plan['post_title']);
    update_user_bag_swap_count($user_id, $bag_swap_count);
}

function start_swap_bag()
{
    $rent_id = isset($_POST['rent_id']) ? intval($_POST['rent_id']) : 0;
    $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;

    if ($rent_id === 0 || $user_id === 0) {
        wp_send_json_error('Invalid post ID or user ID');
    }

    if ($user_id !== get_current_user_id()) {
        wp_send_json_error('Invalid user ID');
    }

    $rent_id = get_rent_id_of_user($user_id);

    if ($rent_id !== ($rent_id)) {
        wp_send_json_error('Invalid rent ID ');
    }

    $active_membership_plan = get_active_membership_plan_of_user_or_null($user_id);

    if (!$active_membership_plan) {
        wp_send_json_error('User does not have an active membership plan');
    }

    $product_id = get_product_id_of_rent($rent_id);

    if (is_product_accessible_in_users_membership_plan($product_id, $active_membership_plan)) {
        wp_send_json_error('Product is not accessible in the user\'s membership plan');
    }

    if (!is_rent_currently_able_to_be_bag_swapped($rent_id)) {
        wp_send_json_error('User is not able to start a bag swap');
    }

    if (!has_remaining_bag_swaps($user_id)) {
        wp_send_json_error('User does not have any remaining bag swaps');
    }

    $current_bag_swap_count = get_current_bag_swap_count_of_user($user_id);

    try {
        if (start_bag_swap_for_rent($rent_id)) {
            decrement_bag_swap_count($user_id);
            update_rent_status($rent_id, RENT_STATUS_BAG_SWAP_WAITING_FOR_CHOOSING_BAG);
        } else {
            wp_send_json_error('Failed to start bag swap');
        }
    } catch (Exception $e) {
        get_current_bag_swap_count_of_user($user_id, $current_bag_swap_count);
        wp_send_json_error('Failed to decrement bag swap count');
    }

    wp_send_json_success('Bag swap started. OLD count: ' . $current_bag_swap_count . ' NEW count: ' . get_current_bag_swap_count_of_user($user_id));
}

// TODO: somewhere need to reset the user bag swap count
// maybe at login or single product page? This should be renewed every month when the membership plan is renewed

// TODO: Handle product "return" or "swap" action
// Maybe can be handled with the rent product or should it has its own product type?
// Stock needs to be updated, another "bag" should be sent to the user and sotck needs to be updated again
// Shipping should be created in order to track the delivery of the bag
