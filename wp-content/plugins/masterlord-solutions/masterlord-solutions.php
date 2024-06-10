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
const HAS_ACTIVE_RENT_META_KEY = 'has_active_rent';
const RENTED_PRODUCT_ID_META_KEY = 'rented_product_ids';

//------------------------------------------------------------------------------------------------
//wp-content\plugins\membership-for-woocommerce\public\class-membership-for-woocommerce-public.php
//wp-content\plugins\membership-for-woocommerce\membership-for-woocommerce.php
//------------------------------------------------------------------------------------------------

function show_and_register_rent_button_logic()
{
    global $product;
    // Check if the user has a membership
    $user_id = get_current_user_id();
    // $is_member_meta = get_user_meta($user_id, 'is_member');
    $current_memberships = get_user_meta($user_id, 'mfw_membership_id', true);

    $has_acces_to_product = false;
    foreach ($current_memberships as $key => $membership_id) {
        if ('publish' == get_post_status($membership_id) || 'draft' == get_post_status($membership_id)) {
            $membership_status = wps_membership_get_meta_data($membership_id, 'member_status', true);
            if ($membership_status == 'complete') {
                $membership_plan = wps_membership_get_meta_data($membership_id, 'plan_obj', true);
                $has_acces_to_product = is_product_accessible_in_membership_plan($product->get_id(), $membership_plan);
            }
        }
    }

    $has_active_rent = user_has_active_rent($user_id);

    if ($product->is_in_stock() && $has_acces_to_product && !$has_active_rent) {
        echo '<p>This product is in stock!</p>';
        echo '<button type="button" class="msl-rent-button" data-product-id="' . $product->get_id() . '">Rent this awesome bag!</button>';
    } elseif ($has_active_rent) {
        echo '<p>Sorry, but you already have an active rent.</p>';
    } else {
        echo '<p>Sorry, this product is out of stock or you do not have a membership.</p>';
    }
}

// TODO - rent_product function
// ADD TO CART
// UPDATE USER META or HOW TO TRACK THE STATUS OF A RENT????
function rent_product()
{
    // Get the product ID from the AJAX request
    $product_id = intval($_POST['product_id']);

    // Get the user ID
    $user_id = get_current_user_id();

    // Add the product to the cart
    global $woocommerce;
    $woocommerce->cart->add_to_cart($product_id);

    // Update the user meta data
    update_user_meta($user_id, HAS_ACTIVE_RENT_META_KEY, true);
    update_user_meta($user_id, RENTED_PRODUCT_ID_META_KEY, $product_id);

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
                            console.log(response); // Add this line
                            if (response.success) {
                                alert('Product rented successfully!');
                            } else {
                                alert('There was an error renting the product.');
                            }
                        }
                    };
                    xhr.send('action=rent_product&product_id=' + product_id);
                }, {
                    passive: true
                });
            });
        });
    </script>
<?php
}

function user_has_active_rent($user_id)
{
    return get_user_meta($user_id, HAS_ACTIVE_RENT_META_KEY, true);
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

function console_log2($prefix, $data)
{
    echo "<script>console.log(" . json_encode($prefix) . ", " . json_encode($data) . ");</script>";
}

function console_log1($data)
{
    echo "<script>console.log( " . json_encode($data) . ");</script>";
}

add_action('woocommerce_single_product_summary', 'show_and_register_rent_button_logic', 20);
add_action('wp_ajax_rent_product', 'rent_product');
add_action('wp_ajax_nopriv_rent_product', 'rent_product');
add_action('wp_footer', 'add_rent_button_script');
