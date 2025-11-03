/**
 * @file
 * Frontpage scripts.
 */

(function ($, Drupal) {

  'use strict';

  if ($(window).width()>1350){
    var i = 3; var j = 16; var k=3;
    var num = 7;
  } else if ($(window).width()>768){
    var i = 3; var j = 15; var k=3;
    var num = 7;
  } else {
    var i = 3; var j = 13; var k=3;
    var num = 6;
  }
  $( window ).resize(function() {
    if ($(window).width()>1350){
      var i = 3; var j = 16; var k=3;
      var num = 7.5;
    } else if ($(window).width()>768){
      var i = 3; var j = 15; var k=3;
      var num = 7.5;
    } else {
      var i = 3; var j = 13; var k=3;
      var num = 6;
    }
  });
  var changegrid = function(){
    var logo = $('.paragraph--type--logos.theme-change .field--name-field-logos > .field__item:nth-child('+i+')');
    var logo_img = logo.find("img");
    var logo_img_src = logo_img.attr("src");
    var logo_img_alt = logo_img.attr("alt");
    var logo_img_width = logo_img.attr("width");
    var logo_img_height = logo_img.attr("height");
    var swapitem = $('.paragraph--type--logos.theme-change .field--name-field-logos > .field__item:nth-child('+j+') img');
    
    logo_img.css("opacity",0);
    setTimeout(
      function() 
      {
        logo_img.attr('src',swapitem.attr("src"));
        logo_img.attr('alt',swapitem.attr("alt"));
        logo_img.attr('height',swapitem.attr("height"));
        logo_img.attr('width',swapitem.attr("width"));
        logo_img.css("opacity",1);

        swapitem.attr('src',logo_img_src);
        swapitem.attr('alt',logo_img_alt);
        swapitem.attr('height',logo_img_height);
        swapitem.attr('width',logo_img_width);
    
        k = (Math.floor((Math.random() * num) + 1))*2-1;
        while (k == i) {
          k = (Math.floor((Math.random() * num) + 1))*2-1;
        }
        i=k;
        j++;
        if (j>$(".paragraph--type--logos.theme-change .field--name-field-logos > .field__item").length){
            j=(num*2)+1; 
        }
      }, 500);

  };

  var interval_id = setInterval(changegrid, 2500);

document.addEventListener( 'visibilitychange' , function() {
  if (document.hidden) {
    clearInterval(interval_id);
    interval_id = 0;
  } else {
    interval_id = setInterval(changegrid, 2500);
  }
}, false );


})(jQuery, Drupal);
