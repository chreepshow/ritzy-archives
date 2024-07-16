<!-- start Simple Custom CSS and JS -->
<script type="text/javascript">

jQuery(document).ready(function( $ ){
    // When a .tab-title is clicked
    $('.tab-title').on('click', function() {
        var $accordion = $(this).closest('.accordion-tab'); // Find the closest accordion-tab
        
        // Toggle the active class on the clicked accordion-tab
        $accordion.toggleClass('active');
        
        // Toggle the visibility of the .tab-content within the clicked accordion-tab
        $accordion.find('.tab-content').slideToggle();
        
        // Optionally, close other accordions if you want only one to be open at a time
        //$('.accordion-tab').not($accordion).removeClass('active').find('.tab-content').slideUp();
    });
});



</script>
<!-- end Simple Custom CSS and JS -->
