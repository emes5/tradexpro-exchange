(function($) {
"use strict";

/*------------------------------------------------------------------
[Table of contents]


/*-------------------------------------------
  js wow
--------------------------------------------- */
 new WOW().init();
/*-------------------------------------------
  js AOS
--------------------------------------------- */
 AOS.init();
/*-------------------------------------------
  js scrollup
--------------------------------------------- */
$.scrollUp({
	scrollText: '<i class="fa fa-angle-up"></i>',
	easingType: 'linear',
	scrollSpeed: 900,
	animation: 'fade'
});

/*-------------------------------------------
  jQuery MeanMenu
--------------------------------------------- */
jQuery(".main-menu").meanmenu();

// smoot scroll nav
jQuery('#nav').onePageNav({});

/*-------------------------------------------
Sticky Header
--------------------------------------------- */
$(window).on('scroll', function(){
    if( $(window).scrollTop()>80 ){
        $('#sticky').addClass('stick');
    } else {
        $('#sticky').removeClass('stick');
    }
});

/*-------------------------------------------
magnific popup
--------------------------------------------- */
$('.expand-image').magnificPopup({
  type: 'image',
  gallery: {
      enabled: true
  }
});
$('.expand-image2').magnificPopup({
  type: 'image',
  gallery: {
      enabled: true
  }
});
$('.expand-image3').magnificPopup({
  type: 'image',
  gallery: {
      enabled: true
  }
});

/*================================
  Gift-carousel
==================================*/
function top_banner_slider() {
  var owl = $(".top-banner-slider");
  owl.owlCarousel({
    loop: true,
    margin: 15,
    navText: ['<i class="fa fa-angle-left" aria-hidden="true"></i>','<i class="fa fa-angle-right" aria-hidden="true"></i>'],
    nav: true,
    items: 4,
    smartSpeed: 1000,
    dots: false,
    autoplay: true,
    autoplayTimeout: 5000,
    responsive: {
      0: {
        items: 1
      },
      480: {
        items: 2
      },
      760: {
        items: 3
      },
      1080: {
        items: 4
      }
    }
  });
}
top_banner_slider();

var angle = 0;
  setInterval(function(){
    angle+=3;
  $("#img").rotate(angle);
  },50);

  var angle = 0;
  setInterval(function(){
    angle+=3;
  $("#img2").rotate(angle);
  },50);

  var angle = 0;
  setInterval(function(){
    angle+=3;
  $("#img3").rotate(angle);
  },50);

})(jQuery);
