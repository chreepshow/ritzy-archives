<?php
// Add filter button
add_action('woocommerce_before_shop_loop', 'add_filter_button', 20);
function add_filter_button() {
    echo '<button id="open-filter-menu" class="filter-button">Filter Products</button>';
}

// Add filter menu container
add_action('wp_footer', 'add_filter_menu_container');
function add_filter_menu_container() {
    echo '<div id="filter-overlay" class="filter-overlay"></div>';
    echo '<div id="filter-menu" class="filter-menu-container">';
    echo '<div class="filter-menu-header">';
    echo '<h3>Filter Products</h3>';
    echo '<button id="close-filter-menu" class="close-filter-button">&times;</button>';
    echo '</div>';
    echo '<div class="filter-menu-content">';
    dynamic_sidebar('woocommerce-sidebar');
    echo '</div>';
    echo '</div>';
}

// Enqueue JavaScript
add_action('wp_enqueue_scripts', 'enqueue_filter_script');
function enqueue_filter_script() {
    wp_enqueue_script('filter-script', plugins_url('filter-script.js', __FILE__), array('jquery'), '1.0', true);
}

add_filter('woocommerce_product_categories_widget_args', 'custom_product_categories_widget_args');

function custom_product_categories_widget_args($args) {
    $args['walker'] = new WC_Product_Cat_List_Walker_Custom();
    return $args;
}

class WC_Product_Cat_List_Walker_Custom extends Walker_Category {
    public function start_el(&$output, $category, $depth = 0, $args = array(), $current_object_id = 0) {
        $cat_name = apply_filters('list_product_cats', esc_attr($category->name), $category);
        $output .= '<li class="cat-item cat-item-' . $category->term_id . '">';
        $output .= '<label>';
        $output .= '<input type="checkbox" class="product-category-checkbox" value="' . $category->slug . '"> ';
        $output .= $cat_name;
        $output .= '</label>';
    }
}