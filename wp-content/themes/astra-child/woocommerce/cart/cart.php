<?php
defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_cart' ); ?>

<div id="cart-content-container" class="cart-content-container">
        <form class="woocommerce-cart-form" action="<?php echo esc_url( wc_get_cart_url() ); ?>" method="post">
            <table class="shop_table cart-table">
                <thead>
                    <tr>
                        <th class="product-info">PRODUCT</th>
                        <th class="product-price">PRICE</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
                        $_product   = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
                        $product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );

                        if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
                            $product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );
                            ?>
                            <tr class="cart-item" data-cart-item-key="<?php echo esc_attr($cart_item_key); ?>">
                                <td class="product-info">
                                    <div class="product-thumbnail">
                                        <?php
                                        $thumbnail = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image(), $cart_item, $cart_item_key );
                                        echo $thumbnail;
                                        ?>
                                    </div>
                                    <div class="product-details">
                                        <div class="product-name"><?php echo wp_kses_post( apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key ) ); ?></div>
                                        <div class="product-remove">
                                            <?php
                                            echo apply_filters( 'woocommerce_cart_item_remove_link', sprintf(
                                                '<a href="%s" class="remove ajax-remove" aria-label="%s" data-product_id="%s" data-product_sku="%s" data-cart_item_key="%s">Remove</a>',
                                                esc_url( wc_get_cart_remove_url( $cart_item_key ) ),
                                                esc_html__( 'Remove this item', 'woocommerce' ),
                                                esc_attr( $product_id ),
                                                esc_attr( $_product->get_sku() ),
                                                esc_attr( $cart_item_key )
                                            ), $cart_item_key );
                                            ?>
                                        </div>
                                    </div>
                                </td>
                                <td class="product-price" data-title="<?php esc_attr_e( 'Price', 'woocommerce' ); ?>">
                                    <?php
                                        echo apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $cart_item, $cart_item_key );
                                    ?>
                                </td>
                            </tr>
                            <?php
                        }
                    }
                    ?>
                </tbody>
            </table>

            <div class="cart-disclaimer">
                <h3>DISCLAIMER</h3>
                <p>This bag is a used bag blablabla, you can't return it only in 1 or 2 days if you see that it's condition is different from the one displayed on the site blablabla. By purchasing the bag, you agree to this.</p>
            </div>

            <div class="cart-summary">
                <div class="summary-row">
                    <span>SUMMARY</span>
                    <span class="cart-total"><?php echo WC()->cart->get_cart_total(); ?></span>
                </div>
            </div>

            <?php do_action( 'woocommerce_cart_actions' ); ?>
            <?php wp_nonce_field( 'woocommerce-cart', 'woocommerce-cart-nonce' ); ?>
        </form>

        <div class="wc-proceed-to-checkout">
            <a href="<?php echo esc_url( wc_get_checkout_url() ); ?>" class="checkout-button button alt wc-forward">
                <?php esc_html_e( 'Proceed to Checkout', 'woocommerce' ); ?>
            </a>
        </div>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
    $(document).on('click', '.ajax-remove', function(e) {
        e.preventDefault();
        var $thisbutton = $(this);
        var product_id = $thisbutton.data('product_id');
        var cart_item_key = $thisbutton.data('cart_item_key');

        $.ajax({
            type: 'POST',
            url: wc_add_to_cart_params.ajax_url,
            data: {
                action: 'remove_item_from_cart',
                product_id: product_id,
                cart_item_key: cart_item_key
            },
            success: function(response) {
                if (response.success) {
                    if (response.is_empty) {
                        $('#cart-content-container').html('');
                    } else {
                        $('tr[data-cart-item-key="' + cart_item_key + '"]').remove();
                        $('.cart-total').html(response.total);
                    }
                }
            }
        });
    });
});
</script>

<?php do_action( 'woocommerce_after_cart' ); ?>