$(window).resize(function () { 
   $('body').css('padding-top', parseInt($('#main-navbar').css("height"))+10);
}); 
 $(window).on('load', function () {
   $('body').css('padding-top', parseInt($('#main-navbar').css("height"))+10);         
}); 