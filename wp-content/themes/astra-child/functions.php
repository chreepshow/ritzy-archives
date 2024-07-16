<?php
/**
 * Astra Child Theme functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package Astra Child
 * @since 1.0.0
 */

/**
 * Define Constants
 */
define( 'CHILD_THEME_ASTRA_CHILD_VERSION', '1.0.0' );

/**
 * Enqueue styles
 */
function child_enqueue_styles() {

	wp_enqueue_style( 'astra-child-theme-css', get_stylesheet_directory_uri() . '/style.css', array('astra-theme-css'), CHILD_THEME_ASTRA_CHILD_VERSION, 'all' );

}

add_action( 'wp_enqueue_scripts', 'child_enqueue_styles', 15 );

// Add custom field to the shipping options
function add_custom_shipping_field() {
    global $post;
    
    echo '<div class="options_group">';
    
    woocommerce_wp_text_input( 
        array( 
            'id' => '_handle_drop', 
            'label' => __('Handle Drop', 'woocommerce'), 
            'placeholder' => '', 
            'description' => __('Enter the handle drop value.', 'woocommerce'), 
            'type' => 'text'
        )
    );
    
    echo '</div>';
}
add_action('woocommerce_product_options_shipping', 'add_custom_shipping_field');

// Save custom field data
function save_custom_shipping_field($post_id) {
    $handle_drop = isset($_POST['_handle_drop']) ? sanitize_text_field($_POST['_handle_drop']) : '';
    update_post_meta($post_id, '_handle_drop', $handle_drop);
}
add_action('woocommerce_process_product_meta', 'save_custom_shipping_field');


// Add the receipt cost to the cart item
function add_receipt_cost_to_cart_item($cart_item_data, $product_id, $variation_id) {
    if (isset($_POST['authenticity_receipt']) && $_POST['authenticity_receipt'] == '1') {
        $cart_item_data['authenticity_receipt'] = true;
        $cart_item_data['authenticity_receipt_cost'] = 30;
    }
    return $cart_item_data;
}
add_filter('woocommerce_add_cart_item_data', 'add_receipt_cost_to_cart_item', 10, 3);

// Calculate the total price with the receipt cost
function calculate_receipt_cost($cart_object) {
    foreach ($cart_object->cart_contents as $key => $value) {
        if (isset($value['authenticity_receipt']) && $value['authenticity_receipt'] === true) {
            $value['data']->set_price($value['data']->get_price() + $value['authenticity_receipt_cost']);
        }
    }
}
add_action('woocommerce_before_calculate_totals', 'calculate_receipt_cost');

// Display the receipt cost in the cart
function display_receipt_cost_in_cart($item_data, $cart_item) {
    if (isset($cart_item['authenticity_receipt'])) {
        $item_data[] = array(
            'key' => __('Authenticity Receipt', 'woocommerce'),
            'value' => __('30', 'woocommerce')
        );
    }
    return $item_data;
}
add_filter('woocommerce_get_item_data', 'display_receipt_cost_in_cart', 10, 2);
// Remove the receipt cost from the cart item
function remove_receipt_cost_from_cart_item($cart_item_data, $cart_item_key) {
    if (isset($cart_item_data['authenticity_receipt']) && $cart_item_data['authenticity_receipt'] === true) {
        $product_price = $cart_item_data['data']->get_price() - $cart_item_data['authenticity_receipt_cost'];
        $cart_item_data['data']->set_price($product_price);
        unset($cart_item_data['authenticity_receipt']);
        unset($cart_item_data['authenticity_receipt_cost']);
    }
    return $cart_item_data;
}
add_filter('woocommerce_cart_item_removed', 'remove_receipt_cost_from_cart_item', 10, 2);


// Ensure the functions.php starts correctly
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// Remove the description tab
add_filter( 'woocommerce_product_tabs', 'remove_description_tab', 98 );
function remove_description_tab( $tabs ) {
    unset( $tabs['description'] ); // Remove the description tab
    return $tabs;
}

// Remove WooCommerce tabs
remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs', 10 );

// Remove the default WooCommerce availability (stock status) section
add_filter( 'woocommerce_get_stock_html', '__return_null', 99, 2 );

// Hypothetical removal of Astra specific stock detail function
remove_action( 'woocommerce_single_product_summary', 'astra_woo_product_single_meta', 20 ); // Replace 'astra_woo_product_single_meta' and '20' with the actual hook and priority

// Remove default WooCommerce single product summary hooks
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_title', 5 );
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_rating', 10 );
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 );
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20 );
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40 );
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_sharing', 50 );

// Custom function to generate breadcrumbs and store in a variable
function get_custom_woocommerce_breadcrumbs() {
    if ( is_product() ) {
        global $post;
        $shop_page_id = wc_get_page_id( 'shop' );
        $shop_page_url = get_permalink( $shop_page_id );
        $shop_page_title = get_the_title( $shop_page_id );

        // Get product categories
        $terms = get_the_terms( $post->ID, 'product_cat' );
        $breadcrumb = array();

        if ( $terms && ! is_wp_error( $terms ) ) {
            $category = current( $terms );
            $breadcrumb[] = '<a href="' . esc_url( get_term_link( $category ) ) . '">' . esc_html( $category->name ) . '</a>';
        }

        // Add Shop link
        array_unshift( $breadcrumb, '<a href="' . esc_url( $shop_page_url ) . '">' . esc_html( $shop_page_title ) . '</a>' );

        // Add Product name
        $breadcrumb[] = esc_html( get_the_title() );

        // Generate breadcrumbs HTML
        $breadcrumbs_html = '<nav class="woocommerce-breadcrumb" itemprop="breadcrumb">' . implode( ' / ', $breadcrumb ) . '</nav>';

        return $breadcrumbs_html;
    }

    return '';
}

// Custom function to display product summary in a custom order
function custom_single_product_summary() {
    global $product;
    $breadcrumbs_html = get_custom_woocommerce_breadcrumbs(); // Call the breadcrumb function and store the result
    $tags = wc_get_product_tag_list( $product->get_id(), ', ' );
    $condition = $product->get_attribute('condition');
    $type = $product->get_attribute('type');
    $stock_status = $product->is_in_stock() ? 'available' : 'not available';

    // Retrieve the Handle Drop value
    $handle_drop = get_post_meta($product->get_id(), '_handle_drop', true);

    // Add Handle Drop to the dimensions array
    $dimensions = array(
        'Length' => $product->get_length() ? $product->get_length() . ' ' . get_option( 'woocommerce_dimension_unit' ) : '',
        'Width'  => $product->get_width() ? $product->get_width() . ' ' . get_option( 'woocommerce_dimension_unit' ) : '',
        'Height' => $product->get_height() ? $product->get_height() . ' ' . get_option( 'woocommerce_dimension_unit' ) : '',
        'Weight' => $product->get_weight() ? $product->get_weight() . ' ' . get_option( 'woocommerce_weight_unit' ) : '',
        'Handle Drop' => $handle_drop ? $handle_drop . ' ' . get_option( 'woocommerce_dimension_unit' ) : '', // Assuming Handle Drop is in the same unit as dimensions
    );
    ?>

    <div class="custom-summary">
        <?php echo $breadcrumbs_html; // Echo the breadcrumbs here ?>
        <div class="product-category">
            <?php 
            if ( $tags ) {
                // Display product tags without any title
                echo $tags;
            } 
            ?>
        </div>
        <div class="top-summary">
            <div class="product-title">
                <?php woocommerce_template_single_title(); ?>
            </div>
            <div class="product-price unique-product-price">
                <?php woocommerce_template_single_price(); ?>
            </div>
            <div class="product-condition">
                <?php 
                if ( $condition ) {
                    echo '<div class="condition">Condition: ' . esc_html($condition) . '</div>';
                } 
                if ( $type ) {
                    echo '<div class="condition">' . esc_html($type) . '</div>';
                } 
                ?>
            </div>
            
            <div class="product-stock">
                <div class="stock <?php echo $product->is_in_stock() ? 'available' : 'not-available'; ?>">
                    <?php echo $stock_status; ?>
                </div>
            </div>
        </div>
        <div class="product-description">
            <div class="accordion-tab description-tab active">
                <div class="tab-title">
                    DESCRIPTION <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect x="10" y="2" width="4" height="20" fill="#27292f"/>
                        <rect x="2" y="10" width="20" height="4" fill="#27292f"/>
                    </svg>
                </div>
                <div class="tab-content">
                    <?php the_content(); ?>
                </div>
            </div>
        </div>
        <div class="product-add-to-cart">
            <?php woocommerce_template_single_add_to_cart(); ?>
        </div>
        <div class="product-dimensions">
            <div class="accordion-tab dimensions-tab">
                <div class="tab-title">
                    DIMENSIONS<svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect x="10" y="2" width="4" height="20" fill="#27292f"/>
                        <rect x="2" y="10" width="20" height="4" fill="#27292f"/>
                    </svg>
                </div>
                <div style="display:none;" class="tab-content">
                    <?php foreach ( $dimensions as $dimension => $value ) : ?>
                        <?php if ( $value ) : ?>
                            <div class="dim-text">      
                                <span style="font-weight:600;"><?php echo esc_html( $dimension ); ?>:</span> <?php echo esc_html( $value ); ?>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <div class="product-authenticity">
            <div class="accordion-tab authenticity-tab" style="background:#F4EBD0;">
                <div class="tab-title">
                    AUTHENTICITY<svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect x="10" y="2" width="4" height="20" fill="#27292f"/>
                        <rect x="2" y="10" width="20" height="4" fill="#27292f"/>
                    </svg>
                </div>
                <div style="display:none;" class="tab-content">
					All of our bags are checked by our professional team and are 100% authentic. However, if you want, you can buy a certificate of authenticity by entrupy if you want to make sure.</br>
                   <div style="margin-top:20px;" class="authenticity-receipt-checkbox">
   						<input type="checkbox" id="authenticity_receipt" name="authenticity_receipt" value="1" />
    					<label for="authenticity_receipt" style="color:rgba(39, 41, 47, 0.75);font-size: 16px;font-weight: 400;">I want an Entrupy Certificate of authenticity for this bag <b>(30€)</b></label>
    				</div>
                </div>
            </div>
        </div>
		<div class="product-accessories">
            <div class="accordion-tab noreturn-tab">
                <div class="tab-title">
                    ACCESSORIES<svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect x="10" y="2" width="4" height="20" fill="#27292f"/>
                        <rect x="2" y="10" width="20" height="4" fill="#27292f"/>
                    </svg>
                </div>
                <div style="display:none;" class="tab-content">
                   <?php echo get_field('accessories'); ?>
                </div>
            </div>
        </div>
        <div class="product-noreturn">
            <div class="accordion-tab noreturn-tab">
                <div class="tab-title">
                    NO RETURN POLICY<svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect x="10" y="2" width="4" height="20" fill="#27292f"/>
                        <rect x="2" y="10" width="20" height="4" fill="#27292f"/>
                    </svg>
                </div>
                <div style="display:none;" class="tab-content">
                    While we do not offer returns, we understand the importance of your satisfaction. If your bag arrives and is substantially different from its described condition, please contact us immediately. Our goal is to ensure every handbag you rent meets your expectations for luxury and quality.
                </div>
            </div>
        </div>
        <div class="rent-this-bag">
            <img src="https://masterlordsolutions.com/wp-content/uploads/2024/06/membership-coming-soon.png" />
        </div>
    </div>
    <?php
}

// Add the custom single product summary
add_action( 'woocommerce_single_product_summary', 'custom_single_product_summary', 5 );


// Rename "Add to Cart" button text to "PURCHASE BAG"
add_filter( 'woocommerce_product_single_add_to_cart_text', 'custom_add_to_cart_text' );
function custom_add_to_cart_text() {
    return __( 'ADD TO CART', 'woocommerce' );
}

// Ensure only one product can be added to cart by removing quantity box
add_filter( 'woocommerce_is_sold_individually', 'custom_remove_all_quantity_fields', 10, 2 );
function custom_remove_all_quantity_fields( $return, $product ) {
    return true;
}


//remove item from table on cart page after clicking remove
add_action('wp_ajax_remove_item_from_cart', 'remove_item_from_cart_ajax');
add_action('wp_ajax_nopriv_remove_item_from_cart', 'remove_item_from_cart_ajax');

function remove_item_from_cart_ajax() {
    $cart_item_key = $_POST['cart_item_key'];
    if($cart_item_key) {
        WC()->cart->remove_cart_item($cart_item_key);
    }
    WC()->cart->calculate_totals();
    
    // Clear cart session if empty
    if (WC()->cart->is_empty()) {
        WC()->cart->empty_cart(true);
    }
    
    $response = array(
        'success' => true,
        'total' => WC()->cart->get_cart_total(),
        'is_empty' => WC()->cart->is_empty()
    );
    wp_send_json($response);
}

//Load custom subscriptions table
add_filter('wc_get_template', 'custom_woocommerce_template', 10, 3);
function custom_woocommerce_template($located, $template_name, $args) {
    $custom_template = get_stylesheet_directory() . '/subscriptions-for-woocommerce/public/partials/templates/' . $template_name;
    if (file_exists($custom_template)) {
        return $custom_template;
    }
    return $located;
}

//Custom logout url
// Add this to your theme's functions.php file

// Define a custom logout URL
function custom_wc_logout_url( $logout_url, $redirect ) {
    // Set your custom logout URL here
    $custom_logout_url = home_url( '/custom-logout' );
    
    // Return the custom logout URL
    return wp_nonce_url( $custom_logout_url, 'log-out' );
}
add_filter( 'logout_url', 'custom_wc_logout_url', 10, 2 );

// Handle the custom logout process
function custom_wc_logout() {
    // Check the nonce
    if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'log-out' ) ) {
        return;
    }

    // Log out the user
    wp_logout();

    // Redirect to the custom URL after logout
    wp_redirect( 'https://masterlordsolutions.com' );
    exit;
}
add_action( 'template_redirect', 'custom_wc_logout' );


//register button on anvbar
// Add body class if user is logged in
function add_logged_in_body_class( $classes ) {
    if ( is_user_logged_in() ) {
        $classes[] = 'user-logged-in';
    } else {
        $classes[] = 'user-logged-out';
    }
    return $classes;
}
add_filter( 'body_class', 'add_logged_in_body_class' );