<!-- start Simple Custom CSS and JS -->
<script type="text/javascript">
jQuery(document).ready(function($) {
    var receiptCost = 30; // Cost of the authenticity receipt
    var originalPrice = <?php echo $product->get_price(); ?>; // Get the original price of the product

    $('#authenticity_receipt').change(function() {
        if ($(this).is(':checked')) {
            // Add the receipt cost to the product price
            var newPrice = originalPrice + receiptCost;
        } else {
            // Revert to the original price
            var newPrice = originalPrice;
        }

        // Update the displayed price
        $('.woocommerce-Price-amount').text('$' + newPrice.toFixed(2));
    });
});

</script>
<!-- end Simple Custom CSS and JS -->
