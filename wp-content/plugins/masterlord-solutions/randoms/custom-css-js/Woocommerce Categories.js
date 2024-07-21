jQuery(document).ready(function($) {	
    const $categoryWidget = $('.widget_product_categories');
    
    // Add buttons after the category list
    $categoryWidget.append(`
        <div class="category-filter-buttons">
            <button type="button" class="button" id="apply-category-filter" disabled>APPLY</button>
            <button type="button" class="button" id="reset-category-filter" style="display:none;">RESET</button>
        </div>
    `);

     // Category filter functionality
    const $categoryCheckboxes = $('.product-category-checkbox');
    const $applyCategoryFilter = $('#apply-category-filter');
    const $resetCategoryFilter = $('#reset-category-filter');
    const $categoryFilterButtons = $('.category-filter-buttons');

    function updateCategoryFilterButtons() {
        const anyChecked = $categoryCheckboxes.is(':checked');
        $categoryFilterButtons.toggle(anyChecked);
        $applyCategoryFilter.prop('disabled', !anyChecked);
    }

    $categoryCheckboxes.on('change', updateCategoryFilterButtons);

    $applyCategoryFilter.on('click', function() {
        const selectedCategories = $categoryCheckboxes.filter(':checked').map(function() {
            return this.value;
        }).get();

        // Create URL with selected categories
        const currentUrl = new URL(window.location.href);
        currentUrl.searchParams.set('product_categories', selectedCategories.join(','));
        window.location.href = currentUrl.toString();
    });

    $resetCategoryFilter.on('click', function() {
        $categoryCheckboxes.prop('checked', false);
        updateCategoryFilterButtons();

        // Remove product_cat from URL
        const currentUrl = new URL(window.location.href);
        currentUrl.searchParams.delete('product_cat');
        window.location.href = currentUrl.toString();
    });

    // Initialize button state
    updateCategoryFilterButtons();
});