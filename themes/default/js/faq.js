$(function() {
    $('#faq').find('.panel-collapse').each(function() {
        $(this).on('show.bs.collapse', function() {
            $(this).parents('.panel').toggleClass('panel-default panel-primary')
        })
        /* .on('shown.bs.collapse', function() {
            $('html,body').stop().animate({
                scrollTop: $(this).prev('.panel-heading').offset().top
            }, 500)
        }) */
        .on('hide.bs.collapse', function() {
            $(this).parents('.panel').toggleClass('panel-default panel-primary')
        })
    })
})