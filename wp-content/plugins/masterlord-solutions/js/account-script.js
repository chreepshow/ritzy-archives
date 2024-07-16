function swapMyBag(rentId) {
    jQuery.post(myAjax.ajaxurl, {
        'action': 'start_swap_bag',
        'rent_id': rentId,
        'user_id': myAjax.user_id // Assuming you've passed user_id through wp_localize_script
    }, function(response) {
        console.log('Bag swap response: ', response);
        // Assuming the response is already a parsed JSON object
        if (response.success === true) {
            window.location.reload();
        }
    });
}