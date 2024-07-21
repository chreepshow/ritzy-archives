<?php
add_action('woocommerce_before_shop_loop', 'output_product_categories_as_checkboxes', 10);
add_filter('woocommerce_product_categories_widget_args', 'custom_product_categories_widget_args');
add_action('pre_get_posts', 'filter_products_by_selected_categories');

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
        $output .= '<li class="cat-item cat-item-' . $category->term_id . '">';
        $output .= '<label>';
        $output .= '<input type="checkbox" class="product-category-checkbox" value="' . $category->slug . '"> ';
        $output .= $cat_name;
        $output .= '</label>';
    }
}

function output_product_categories_as_checkboxes()
{
    // $categories = get_terms('product_cat', array('hide_empty' => false));
    // echo '<form id="product-category-filter">';
    // foreach ($categories as $category) {
    //     echo '<div><input type="checkbox" class="product-category-checkbox" name="product_categories[]" value="' . esc_attr($category->slug) . '"> ' . esc_html($category->name) . '</div>';
    // }
    // // echo '<button type="button" id="apply-filters">Apply</button>';
    // // echo '<button type="button" id="reset-filters">Reset</button>';
    // echo '</form>';
    echo '<script src="' . plugin_dir_url(__FILE__) . '/js/filter-products.js"></script>';
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
                $custom_tax_query[] = array(
                    'taxonomy' => 'pa_' . $attribute,
                    'field'    => 'slug',
                    'terms'    => explode(',', $value),
                    'operator' => 'IN',
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
