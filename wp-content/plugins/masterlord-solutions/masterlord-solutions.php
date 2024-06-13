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
require_once plugin_dir_path(__FILE__) . 'rent-functions.php';
require_once plugin_dir_path(__FILE__) . 'utils.php';
require_once plugin_dir_path(__FILE__) . 'cart-functions.php';
const HAS_ACTIVE_RENT_META_KEY = 'has_active_rent';
const RENTED_PRODUCT_ID_META_KEY = 'rented_product_ids';

//------------------------------------------------------------------------------------------------
//wp-content\plugins\membership-for-woocommerce\public\class-membership-for-woocommerce-public.php
//wp-content\plugins\membership-for-woocommerce\membership-for-woocommerce.php
//------------------------------------------------------------------------------------------------

function show_and_register_rent_button_logic()
{
    global $product;
    $product_id = $product->get_id();
    // Check if the user has a membership
    $user_id = get_current_user_id();
    // $is_member_meta = get_user_meta($user_id, 'is_member');
    $current_memberships = get_user_meta($user_id, 'mfw_membership_id', true);

    $has_acces_to_product = false;
    $has_active_membership = false;
    foreach ($current_memberships as $key => $membership_id) {
        if ('publish' == get_post_status($membership_id) || 'draft' == get_post_status($membership_id)) {
            $membership_status = wps_membership_get_meta_data($membership_id, 'member_status', true);
            if ($membership_status == 'complete') {
                $membership_plan = wps_membership_get_meta_data($membership_id, 'plan_obj', true);
                $has_acces_to_product = is_product_accessible_in_membership_plan($product_id, $membership_plan);
                $has_active_membership = true;
            }
        }
    }

    $has_active_rent_id = get_rent_id_of_user($user_id);
    $rent_status = null;
    if ($has_active_rent_id) {
        $rent_status = get_rent_status_by_id($has_active_rent_id);
    }

    echo get_rent_button_html($product->is_in_stock(), $has_acces_to_product, $has_active_rent_id, $rent_status, $product_id, $has_active_membership);
}

function get_rent_button_html($product_in_stock, $has_acces_to_product, $has_active_rent_id, $rent_status, $product_id, $has_active_membership)
{
    $html = '';
    if ($product_in_stock && $has_acces_to_product && !$has_active_rent_id) {
        $html .= '<p>This product is in stock!</p>';
        $html .= '<button type="button" name=add-to-cart class="msl-rent-button" data-product-id="' . $product_id . '">Rent this awesome bag!</button>';
    } elseif ($has_active_rent_id && $rent_status == RENT_STATUS_IN_CART) {
        $html .= '<p>You have a bag in your cart. Remove it first if you would like to rent another one.</p>';
    } elseif ($has_active_rent_id) {
        $html .= '<p>Sorry, but you already have an active rent.</p>';
    } elseif (!$has_active_membership) {
        $html .= '<p>Sorry, but you do not have an active membership.</p>';
    } elseif ($has_active_membership && !$has_acces_to_product) {
        $html .= '<p>Sorry, but you do not have the right membership to access this product.</p>';
    } elseif (!$product_in_stock) {
        $html .= '<p>This product is out of stock!</p>';
    } else {
        $html .= '<p>Sorry, but you do not have access to this product.</p>';
    }
    return $html;
}

function add_rent_product_to_cart()
{
    // Get the product ID from the AJAX request
    $product_id = intval($_POST['product_id']);

    // Get the user ID
    $user_id = get_current_user_id();

    if (!$user_id) {
        wp_die();
    }

    // Add the product to the cart
    global $woocommerce;
    $woocommerce->cart->add_to_cart($product_id);
    $rent_post_id = create_rent_post($user_id, $product_id, RENT_STATUS_IN_CART);

    // Update the user meta data
    update_rent_id_of_user($user_id, $rent_post_id);

    // Send a response back to the AJAX request
    echo json_encode(array('success' => true));
    wp_die();
}

function add_rent_button_script()
{
?>
    <script type="text/javascript">
        document.addEventListener('DOMContentLoaded', function() {
            var buttons = document.querySelectorAll('.msl-rent-button');
            buttons.forEach(function(button) {
                button.addEventListener('click', function() {
                    var product_id = this.getAttribute('data-product-id');
                    var xhr = new XMLHttpRequest();
                    xhr.open('POST', '<?php echo admin_url('admin-ajax.php'); ?>', true);
                    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                    xhr.onload = function() {
                        if (xhr.status === 200) {
                            var response = JSON.parse(xhr.responseText);
                            console.log(response); // Keep this line for debugging purposes
                            if (response.success) {
                                alert('Product rented successfully!');
                                if (window.location.hostname == 'localhost') {
                                    window.location.href = '/ritzy-archives/cart';
                                } else {
                                    window.location.href = '/cart'; // Redirect to the cart page
                                }
                            } else {
                                alert('There was an error renting the product.');
                            }
                        }
                    };
                    xhr.send('action=add_rent_product_to_cart&product_id=' + product_id);
                }, {
                    passive: true
                });
            });
        });
    </script>
<?php
}

function is_product_accessible_in_membership_plan($product_id, $membership_plan)
{
    $accessible_prod = accessible_products_in_membership_plan($membership_plan);
    $accessible_cat = accessible_categories_in_membership_plan($membership_plan);
    $accessible_tag = accessible_tags_in_membership_plan($membership_plan);

    if (in_array($product_id, $accessible_prod) || (!empty($accessible_cat) && has_term($accessible_cat, 'product_cat')) || (!empty($accessible_tag) && has_term($accessible_tag, 'product_tag'))) {
        return true;
    }

    return false;
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

add_action('woocommerce_single_product_summary', 'show_and_register_rent_button_logic', 20);
add_action('wp_ajax_add_rent_product_to_cart', 'add_rent_product_to_cart');
add_action('wp_ajax_nopriv_add_rent_product_to_cart', 'add_rent_product_to_cart');
add_action('wp_footer', 'add_rent_button_script');
