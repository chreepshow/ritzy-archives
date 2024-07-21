<?php
add_action('wp_enqueue_scripts', 'enqueue_filter_products_script');
add_filter('woocommerce_product_categories_widget_args', 'custom_product_categories_widget_args');
add_action('pre_get_posts', 'filter_products_by_selected_categories');

function enqueue_filter_products_script() {
    if (is_shop() || is_product_category() || is_product_taxonomy()) {
        wp_enqueue_script('filter-products', plugin_dir_url(__FILE__) . '/js/filter-products.js', array('jquery'), '1.0.1', true);
    }
}

function custom_product_categories_widget_args($args)
{
    $args['walker'] = new WC_Product_Cat_List_Walker_Custom();
    return $args;
}

class WC_Product_Cat_List_Walker_Custom extends Walker_Category
{
    public function start_el(&$output, $category, $depth = 0, $args = array(), $current_object_id = 0)
    {
        $cat_name = apply_filters('list_product_cats', esc_attr($category->name), $category);
        $output .= '<li class="cat-item cat-item-' . $category->term_id . ' mls-product-category-item">';
        $output .= '<input type="checkbox" class="product-category-checkbox mls-product-category-checkbox" value="' . $category->slug . '"> ';
        $output .= '<span class="mls-product-category-label">';
        // $output .= '<svg class="wc-block-components-checkbox__mark" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 20"><path d="M9 16.2L4.8 12l-1.4 1.4L9 19 21 7l-1.4-1.4L9 16.2z"></path></svg>';
        $output .= $cat_name;
        $output .= '</span>';
    }
}

function filter_products_by_selected_categories($query)
{
    if (!is_admin() && $query->is_main_query() && is_post_type_archive('product')) {
        // Retrieve any existing tax queries
        $existing_tax_query = (array) $query->get('tax_query');

        // Initialize our custom tax query
        $custom_tax_query = array();

        // Check if product_categories is set and add it to the custom tax query
        if (isset($_GET['product_categories'])) {
            $selected_categories = explode(',', $_GET['product_categories']);
            $custom_tax_query[] = array(
                'taxonomy' => 'product_cat',
                'field'    => 'slug',
                'terms'    => $selected_categories,
                'operator' => 'IN',
            );
        }

        // Dynamically build the filter for attributes and add them to the custom tax query
        foreach ($_GET as $key => $value) {
            if (strpos($key, 'filter_') === 0) {
                $attribute = str_replace('filter_', '', $key);
                $query_type_key = 'query_type_' . $attribute;
                $operator = isset($_GET[$query_type_key]) && $_GET[$query_type_key] == 'or' ? 'IN' : 'AND';

                $custom_tax_query[] = array(
                    'taxonomy' => 'pa_' . $attribute,
                    'field'    => 'slug',
                    'terms'    => explode(',', $value),
                    'operator' => $operator,
                );
            }
        }

        // If there are custom tax queries, merge them with any existing tax queries
        if (!empty($custom_tax_query)) {
            // If there's an existing tax query, merge. Otherwise, just use the custom tax query.
            if (!empty($existing_tax_query)) {
                $existing_tax_query[] = array(
                    'relation' => 'AND', // Use AND relation to combine with existing queries
                );
                $tax_query = array_merge($existing_tax_query, array('relation' => 'AND'), $custom_tax_query);
            } else {
                $tax_query = array_merge(array('relation' => 'AND'), $custom_tax_query);
            }

            // Set the modified tax query back on the main query
            $query->set('tax_query', $tax_query);
        }
    }
}
