jQuery('#testimonials-filter-select').change(function() {
    var filter = jQuery('#testimonials-filter');
    jQuery.ajax({
        url: filter.attr('action'),
        data: filter.serialize(),
        type: filter.attr('method'),
        beforeSend: function(xhr) {
            jQuery('#testimonials-filter .ajax-loader').css('display', 'inline-block');
        },
        success: function(data) {
            jQuery('#testimonials-filter .ajax-loader').css('display', 'none');
            jQuery('.testimonials-items').html(data);
        }
    });
    return false;
});