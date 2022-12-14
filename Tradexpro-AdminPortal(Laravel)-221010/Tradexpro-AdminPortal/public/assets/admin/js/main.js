;
(function($) {
    "use strict";

    $("#metismenu").metisMenu();

    $('.menu-bars').on('click', function() {
        $('.sidebar').toggleClass('sidebar-hide');
        $('.top-bar').toggleClass('content-expend');
        $('.main-wrapper').toggleClass('content-expend');
        $('.top-bar-logo').toggleClass('top-bar-logo-hide');
    });

    $(window).resize(function() {
        sidebarMenuCollpase();
    });

    function sidebarMenuCollpase() {
        if ($(window).width() <= 1199) {

            $('.top-bar-logo').hide();
            $('.top-bar').addClass('content-expend');
            $('.main-wrapper').addClass('content-expend');
            $('.sidebar').addClass('sidebar-hide');

            $('.menu-bars').on('click', function () {
                $('.main-wrapper').toggleClass('content-expend');
            });

        }
        if ($(window).width() <= 426) {
            $('.top-bar-logo').show();
            $('.top-bar').addClass('content-expend');
            $('.main-wrapper').addClass('content-expend');
            $('.sidebar').addClass('sidebar-hide');

            $('.menu-bars').on('click', function () {
                $('.main-wrapper').toggleClass('content-expend');
            });
        }
    }
    sidebarMenuCollpase();


    $("#select-all").click(function () {
        $('input:checkbox').not(this).prop('checked', this.checked);
    });

}(jQuery));
