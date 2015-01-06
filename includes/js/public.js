jQuery(document).ready(function($){
    /* Hide toggleable content on page load*/
    $('.toggleable').hide();

    /* When a toggle is clicked, show the toggle-content */
    $('.toggle-link').click(function( e ){
        // Traverse for some items
        var $toggleable = $( this ).parent().siblings( ".toggleable" ).toggle();

        return false;
    });

},(jQuery));