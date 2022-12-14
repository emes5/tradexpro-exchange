/*-------------------------------------------
  jQuery MeanMenu
--------------------------------------------- */
jQuery(".mobile-menu").meanmenu();

$("#metismenu").metisMenu();

$(".menu-toggler").on("click", function() {
    $('body').toggleClass('_toggle');
});

$(".menu-close").on("click", function() {
    $('body').removeClass('_toggle');
});

$(".cp-user-sidebar-toggler-s2").on("click", function() {
    $('body').toggleClass('sidebar-cllopse');
});
$('.cp-user-sidebar-toggler-s2').on('click', function () {
    $('.page-left-sidebar').toggleClass('sidebar-show');
});



$('.cp-user-sidebar-toggler, .mb-sidebar-toggler').on('click', function () {
    $('.cp-user-sidebar').toggleClass('sidebar-show');
});


$(".cp-user-deposit-card-select ul").on("click", ".init", function () {
    $(this).closest("ul").children('li:not(.init)').toggle();
});

var allOptions = $(".cp-user-deposit-card-select ul").children('li:not(.init)');
$(".cp-user-deposit-card-select ul").on("click", "li:not(.init)", function () {
    allOptions.removeClass('selected');
    $(this).addClass('selected');
    $(".cp-user-deposit-card-select ul").children('.init').html($(this).html());
    allOptions.toggle();
});


$(document).ready(function () {
    $('.scrollbar-inner').scrollbar();
});