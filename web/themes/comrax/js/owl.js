(function ($, Drupal) {

  'use strict';

  Drupal.behaviors.owl = {
    attach: function (context, settings) {
      
      let isRtl = false;
      if (jQuery("html").attr("dir")=="rtl") {
        isRtl = true;
      }
    
    $(".owl-1").each(function(){
        if ($(this).children(".field__item").length > '1') {
          $(this).owlCarousel({
            rtl: isRtl,
            autoplay:false,
            loop:false,
            margin:23,
            nav: false,
            dots: false,
            items: 1,
          });
        }
        if ($(this).find(".field--name-field-cubes").children(".field__item").length > '1') {
          $(this).find(".field--name-field-cubes").owlCarousel({
            rtl: isRtl,
            autoplay:true,
            autoplayTimeout:3000,
            autoplayHoverPause:true,
            loop:true,
            margin:23,
            nav: false,
            dots: true,
            items: 1,
          });
        }
    });

    $(".paragraph--type--mobile-gallery").each(function(){
      if ($(this).find('.field--name-field-gallery-image').children(".field__item").length > '1') {
        $(this).find('.field--name-field-gallery-image').owlCarousel({
          rtl: isRtl,
          autoplay:false,
          loop:true,
          margin:30,
          nav: true,
          dots: false,
          items: 5,
          responsive: {
            0: {
                items: 1,
            },
            769:{
              items: 3,
            },
            1025:{
              items: 5,
            },
          }
        });
      }
  });

  $(".paragraph--type--gallery").each(function(){
    if ($(this).find('.field--name-field-gallery-images').children(".field__item").length > '1') {
      $(this).find('.field--name-field-gallery-images').owlCarousel({
        rtl: isRtl,
        autoplay:false,
        loop:true,
        margin:30,
        nav: true,
        dots: false,
        items: 1,
        responsive: {
          0: {
            margin:10,
          },
          769:{
            margin:30,
          },
        }
      });
    }
});

    if ($('.paragraph--type--articles .field--name-field-articles > .field__item').length > '1') {
      $('.paragraph--type--articles .field--name-field-articles').owlCarousel({
        rtl: isRtl,
        autoplay:false,
        loop:true,
        margin:10,
        nav: true,
        dots: false,
        items: 1,
      });
    }

    if ($('.view-projects.view-display-id-block_1 > .view-content > .views-row').length > '1') {
      $('.view-projects.view-display-id-block_1 > .view-content').owlCarousel({
        rtl: isRtl,
        autoplay:false,
        loop:false,
        margin:60,
        nav: true,
        dots: false,
        navRewind: false,
        responsive: {
          0: {
              items: 1,
          },
          769:{
            items: 2,
            slideBy: 2,
          },
          1025:{
            items: 3,
            slideBy: 3,
          },
        }
      });
    }

    $(".paragraph--type--image-text").each(function(){
      if ($(this).find('.field--name-field-image').children(".field__item").length > '1') {
        $(this).find('.field--name-field-image').owlCarousel({
          rtl: isRtl,
          autoplay:false,
          loop:true,
          margin:30,
          nav: false,
          dots: false,
          items: 1,
        });
      }
  });

  // text around image owl

  $('.paragraph--type--texts-around-image').each(function() {
    if ($(window).width()<1025){
      if ($(this).find(".field--name-field-texts-around > .field__item").length > '1') {
        $(this).find(".field--name-field-texts-around").owlCarousel({
          rtl: isRtl,
          autoplay:false,
          loop:false,
          margin:25,
          nav: true,
          dots: true,
          navRewind: false,
          items: 1,
          singleItem: true,
        });
      }
    }
  });
  $(window).resize(function() {
    $('.paragraph--type--texts-around-image').each(function() {
      if ($(window).width()>1024){
        $(this).find(".field--name-field-texts-around").trigger('destroy.owl.carousel').removeClass('owl-carousel owl-loaded');
        $(this).find(".field--name-field-texts-around").find('.owl-stage-outer').children().unwrap();
      } else {
        if ($(this).find(".field--name-field-texts-around > .field__item").length > '1') {
          $(this).find(".field--name-field-texts-around").owlCarousel({
            rtl: isRtl,
            autoplay:false,
            loop:false,
            margin:25,
            nav: true,
            dots: true,
            navRewind: false,
            items: 1,
            singleItem: true,
          });
        }
      }
    });
  });

    if ($(window).width()<769){
      if ($(".page-node-type-lp-new .field--name-field-cubes > .field__item").length > '1') {
        $(".page-node-type-lp-new .field--name-field-cubes").owlCarousel({
              rtl: isRtl,
              autoplay:false,
              loop:false,
              margin:20,
              dots: false,
              nav: true,
              navRewind: false,
              rewindNav: false,
              items: 1,
              slideBy: 1,
        });
      }
    }
  $(window).resize(function() {
      if ($(window).width()>768){
        $(".page-node-type-lp-new .field--name-field-cubes").trigger('destroy.owl.carousel').removeClass('owl-carousel owl-loaded');
        $(".page-node-type-lp-new .field--name-field-cubes").find('.owl-stage-outer').children().unwrap();
      } else {
        if ($(".page-node-type-lp-new .field--name-field-cubes > .field__item").length > '1') {
          $(".page-node-type-lp-new .field--name-field-cubes").owlCarousel({
              rtl: isRtl,
              autoplay:false,
              loop:false,
              margin:20,
              dots: false,
              nav: true,
              navRewind: false,
              rewindNav: false,
              items: 1,
              slideBy: 1,
          });
        }
      }
  });

      if ($(window).width()<769){
      if ($(".field--name-field-four-images > .field__item").length > '1') {
        $(".field--name-field-four-images").owlCarousel({
              rtl: isRtl,
              autoplay:false,
              loop:false,
              margin:20,
              dots: false,
              nav: true,
              navRewind: false,
              rewindNav: false,
              items: 1,
              slideBy: 1,
        });
      }
    }
  $(window).resize(function() {
      if ($(window).width()>768){
        $(".field--name-field-four-images").trigger('destroy.owl.carousel').removeClass('owl-carousel owl-loaded');
        $(".field--name-field-four-images").find('.owl-stage-outer').children().unwrap();
      } else {
        if ($(".field--name-field-four-images > .field__item").length > '1') {
          $(".field--name-field-four-images").owlCarousel({
              rtl: isRtl,
              autoplay:false,
              loop:false,
              margin:20,
              dots: false,
              nav: true,
              navRewind: false,
              rewindNav: false,
              items: 1,
              slideBy: 1,
          });
        }
      }
  });

     }
  };

})(jQuery, Drupal);

