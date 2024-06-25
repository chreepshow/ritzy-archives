<?php

/**
 * Plugin Name: Masterlord Solutions
 * Plugin URI: https://masterlorsolutions.com/
 * Description: This is a plugin that supports renting products.
 * Version: 1.0.3
 * Author: Peter Koppany
 * Author URI: https://masterlorsolutions.com/
 * License: GPL2
 */
require_once plugin_dir_path(__FILE__) . 'utils.php';
require_once plugin_dir_path(__FILE__) . 'rent-functions.php';
require_once plugin_dir_path(__FILE__) . 'checkout-functions.php';
require_once plugin_dir_path(__FILE__) . 'cart-functions.php';
require_once plugin_dir_path(__FILE__) . 'account-functions.php';
require_once plugin_dir_path(__FILE__) . 'add-to-cart-button.php';
const HAS_ACTIVE_RENT_META_KEY = 'has_active_rent';
const RENTED_PRODUCT_ID_META_KEY = 'rented_product_ids';

add_action('woocommerce_single_product_summary', 'show_and_register_rent_button_logic', 20);
add_action('wp_ajax_add_rent_product_to_cart', 'add_rent_product_to_cart');
add_action('wp_ajax_nopriv_add_rent_product_to_cart', 'add_rent_product_to_cart');
add_action('wp_footer', 'add_rent_button_script');
add_filter('woocommerce_product_single_add_to_cart_text', 'woocommerce_custom_single_add_to_cart_text');

add_action('wp_enqueue_scripts', 'my_plugin_enqueue_styles');
function my_plugin_enqueue_styles()
{
    // Use plugins_url() to correctly get the path to your stylesheet file
    $stylesheet_url = plugins_url('masterlord-solutions.css', __FILE__);

    // Enqueue the stylesheet
    wp_enqueue_style('masterlord-solutions-styles-handle', $stylesheet_url);
}

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

    if (!$current_memberships) {
        return;
    }

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
        // If the product is not in the cart, delete the rent post and meta data if it exists with rent status "in_cart"
        if ($rent_status == RENT_STATUS_IN_CART && !is_product_in_cart($product_id)) {
            delete_rent_and_meta_for_user($has_active_rent_id, $user_id);

            // Refresh the status and active rent
            $has_active_rent_id = get_rent_id_of_user($user_id);
            $rent_status = null;
            if ($has_active_rent_id) {
                $rent_status = get_rent_status_by_id($has_active_rent_id);
            }
        }
    }

    echo get_rent_button_html($product->is_in_stock(), $has_acces_to_product, $has_active_rent_id, $rent_status, $product_id, $has_active_membership);
}

function get_rent_button_html($product_in_stock, $has_acces_to_product, $has_active_rent_id, $rent_status, $product_id, $has_active_membership)
{
    $html = '';
    if ($product_in_stock && $has_acces_to_product && !$has_active_rent_id) {
        $html .= '<p style="margin-bottom: 0;">- Or -</p>';
        $html .= '<button type="button" name=add-to-cart class="msl-rent-button" data-product-id="' . $product_id . '">Rent this bag</button>';
        $html .= '<p style="margin-bottom: 0;">For free, until you have a membership.</p>';
    } elseif ($has_active_rent_id && $rent_status == RENT_STATUS_IN_CART) {
        $html .= '<p style="margin-bottom: 0;">You have a bag in your cart. Remove it first if you would like to rent another one.</p>';
        $html .= '<button id="goToCartButton" class="go-to-cart-btn"><i class="fa fa-shopping-cart"></i> Go to cart</button>';

        // Add JavaScript for navigation
        $html .= '
        <script>
        document.getElementById("goToCartButton").addEventListener("click", function() {
             if (window.location.hostname == \'localhost\') {
                                window.location.href = \'/ritzy-archives/cart\';
                            } else {
                                window.location.href = \'/cart\'; // Redirect to the cart page
                            }
        });
        </script>
    ';
    } elseif ($has_active_rent_id) {
        $html .= '<p class="mls-rent-product-already-in-cart">You already have an active rent, so you can\'t rent another bag, but you can buy them.</p>';
    } elseif (!$has_active_membership) {
        // Don't have to show anything here, because the user can't rent the product, but they can buy it.
        $html .= '';
        // $html .= '<p>Sorry, but you do not have an active membership.</p>';
    } elseif ($has_active_membership && !$has_acces_to_product) {
        $html .= '';
        // $html .= '<p>Sorry, but you do not have the right membership to rent this product.</p>';
    } elseif (!$product_in_stock) {
        // $html .= '<p>This product is out of stock!</p>';
    } else {
        // Don't have to show anything here, because the user can't rent the product, but they can buy it.
        $html .= '';
        // $html .= '<p>Sorry, but you do not have access to this product.</p>';
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

    if (get_rent_post_by_user_id_and_product_id($user_id, $product_id)) {
        wp_die();
    }

    // Add the product to the cart
    WC()->cart->add_to_cart($product_id);

    // Check if the product was added to the cart and return an error message if it wasn't
    if (!is_product_in_cart($product_id)) {
        echo json_encode(array('success' => false, 'message' => 'Product could not be added to cart!'));
        wp_die();
    }
    $rent_post_id = create_rent_post($user_id, $product_id, RENT_STATUS_IN_CART);

    // Update the user meta data
    update_rent_id_of_user($user_id, $rent_post_id);

    // Send a response back to the AJAX request
    echo json_encode(array('success' => true, 'message' => 'Product added to cart successfully!'));
    wp_die();
}

function is_product_in_cart($product_id)
{
    foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
        if ($cart_item['product_id'] == $product_id) {
            // Product is found in the cart
            return true;
        }
    }
    // Product is not found in the cart
    return false;
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
                            button.disabled = true;
                            if (response.success) {
                                console.log(response.message);
                                if (window.location.hostname == 'localhost') {
                                    window.location.href = '/ritzy-archives/cart';
                                } else {
                                    window.location.href = '/cart'; // Redirect to the cart page
                                }
                            } else {
                                button.disabled = false;
                                console.error(response.message);
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

function woocommerce_custom_single_add_to_cart_text()
{
    return __('Buy this bag', 'woocommerce');
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
