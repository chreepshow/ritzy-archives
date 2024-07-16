<!-- start Simple Custom CSS and JS -->
<script type="text/javascript">
jQuery(document).ready(function($) {
    var receiptCost = 30; // Cost of the authenticity receipt

    // Get the original price
    var originalPriceText = $('.unique-product-price bdi').text().replace(/[^0-9.,]/g, '');
    var originalPrice = parseFloat(originalPriceText.replace(',', '.')); // Convert to float for arithmetic

    // Initialize the adjusted price
    var adjustedPrice = originalPrice;

    $('#authenticity_receipt').change(function() {
        if ($(this).is(':checked')) {
            adjustedPrice = originalPrice + receiptCost;
        } else {
            adjustedPrice = originalPrice;
        }

        // Round to the nearest integer
        adjustedPrice = Math.round(adjustedPrice);

        // Update the displayed price
        $('.unique-product-price bdi').html(adjustedPrice + '&nbsp;<span class="woocommerce-Price-currencySymbol">€</span>');
    });
});
</script>
<!-- end Simple Custom CSS and JS -->
