<?php

const BAG_SWAP_COUNT_META_KEY = 'bag_swap_count';

const TES_BAG_SWAP_COUNT = 500;
const LOWEST_BAG_SWAP_COUNT = 1;
const MID_BAG_SWAP_COUNT = 1;
const HIGHEST_BAG_SWAP_COUNT = 1;

function update_user_bag_swap_count($user_id, $bag_swap_count) {
    update_user_meta($user_id, BAG_SWAP_COUNT_META_KEY, $bag_swap_count);
}

function get_current_bag_swap_count_of_user($user_id) {
    return get_user_meta($user_id, BAG_SWAP_COUNT_META_KEY, true);
}

function has_remaining_bag_swaps($user_id) {
    $bag_swap_count = get_current_bag_swap_count_of_user($user_id);
    return $bag_swap_count > 0;
}

function decrement_bag_swap_count($user_id) {
    if (!has_remaining_bag_swaps($user_id)) {
        return;
    }

    $bag_swap_count = get_current_bag_swap_count_of_user($user_id);
    $bag_swap_count--;
    update_user_bag_swap_count($user_id, $bag_swap_count);
}

function get_bag_swap_count_by_membership_plan($membership_plan_name) {
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

function reset_bag_swap_count_for_user($user_id) {
    $active_membership_plan = get_active_membership_plan_of_user_or_null($user_id);
    if (!$active_membership_plan) {
        return;
    }

    $bag_swap_count = get_bag_swap_count_by_membership_plan($active_membership_plan['post_title']);
    update_user_bag_swap_count($user_id, $bag_swap_count);
}

// TODO: somewhere need to reset the user bag swap count
// maybe at login or single product page? This should be renewed every month when the membership plan is renewed

// TODO: start bag swap function
// check if the user has active membership plan
// check if the user has active rent with status delivered
// check if the user already started a bag swap (is_rent_currently_being_swapped)
// check if user has remaining bag swaps

// TODO: Handle product "return" or "swap" action
// Maybe can be handled with the rent product or should it has its own product type?
// Stock needs to be updated, another "bag" should be sent to the user and sotck needs to be updated again
// Shipping should be created in order to track the delivery of the bag
