(function ($, Drupal) {

  'use strict';

  Drupal.behaviors.my_custom_behavior = {
    attach: function (context, settings) {





			$("a.toscroll").on('click',function(e) {
				var url = e.target.href;
				var hash = url.substring(url.indexOf("#")+1);
				$('html, body').animate({scrollTop: $('#'+hash).offset().top - 60}, 500);
        $("body").removeClass("open-menu");
				return false;
			});

      $(".coffee-box .mug").on('click',function(e) {
				$('html, body').animate({scrollTop: $('#contact').offset().top - 60}, 500);
        $("body").removeClass("open-menu");
				return false;
			});
      
      /********** Home line **********/

      if ($("body").hasClass("path-frontpage")){
        var heightline = $(".on-image .toscroll").offset().top + $(".on-image .toscroll").height() + 22;
        $(".line").css("top",heightline+"px");
        $(".line").css("height","calc(100% - "+heightline+"px");
        $(".line").css("opacity",1);
        $( window ).resize(function() {
          var heightline = $(".on-image .toscroll").offset().top + $(".on-image .toscroll").height() + 22;
          $(".line").css("top",heightline+"px");
          $(".line").css("height","calc(100% - "+heightline+"px");
        });
      }

      // ** popup  **/

      $(".popup-open span").on('click',function(e) {
        $("body").addClass("open-popup");
      });

      $(".popup-wrapper .close").on('click',function(e) {
        $("body").removeClass("open-popup");
      });

      $(".popup-wrapper").unbind('click').bind('click', function (e) {
        if((!$(e.target).closest($(".region-popup-wrapper")).length)) {
          if($("body").hasClass("open-popup")){     
            $("body").removeClass("open-popup");
          }
        }
      });
      if ($(".popup-once").length > 0) {
        if (document.cookie.indexOf('popupClosed=true') === -1) {
          $("body").addClass("open-popup-once");
        }
        $(".popup-once .close").on('click',function(e) {
            document.cookie = "popupClosed=true; path=/; max-age=3600"; // 1 hour expiry
          $("body").removeClass("open-popup-once");
        });

        $(".popup-once").unbind('click').bind('click', function (e) {
          if((!$(e.target).closest($(".region-popup-once")).length)) {
            document.cookie = "popupClosed=true; path=/; max-age=3600"; // 1 hour expiry
            $("body").removeClass("open-popup-once");
          }
        });
      }
      /*** contact button  */

      $(".contact-button button").on('click',function(e) {
        $("body").addClass("contact-button-press");
      });

      $(".contact-button .close").on('click',function(e) {
        $("body").removeClass("contact-button-press");
      });

      $("body").unbind('click').bind('click', function (e) {
        if((!$(e.target).closest($(".contact-button")).length)) {
          if($("body").hasClass("contact-button-press")){     
            $("body").removeClass("contact-button-press");
          }
        }
      });

      /************ sub menu *************/

      $('.wrapper-sub-menu').each(function(element){
        $(this).find(".hover-items").children(".content:first-child").addClass("active");
        $(this).find(".menu").children("li:first-child").addClass("active");
      });

      
      $(".region-menu .menu-item--expanded").hover(
        function() {
        }, function() {
          $(".wrapper-sub-menu .hover-items .active").removeClass("active");
          $(".wrapper-sub-menu .menu .active").removeClass("active");
          $(this).find(".hover-items").children(".content:first-child").addClass("active");
          $(this).find(".menu").children("li:first-child").addClass("active");
        }
      );

      $(".wrapper-sub-menu .menu li").hover(
        function() {
          $(".wrapper-sub-menu .hover-items .active").removeClass("active");
          $(".wrapper-sub-menu .menu .active").removeClass("active");
          $(".wrapper-sub-menu .hover-items ."+$(this).attr("class")).addClass("active");
          $(this).addClass("active");
        }, function() {
        }
      );
      
      /***** PG numbers  *********/

  $.fn.isInViewport = function() {
    var elementTop = $(this).offset().top;
    var elementBottom = elementTop + $(this).outerHeight();

    var viewportTop = $(window).scrollTop();
    var viewportBottom = viewportTop + $(window).height();

    return elementBottom > viewportTop && elementTop < viewportBottom;
  };

  if ($("div").hasClass("paragraph--type--numbers")){
    function commaSeparateNumber(val) {
      while (/(\d+)(\d{3})/.test(val.toString())) {
        val = val.toString().replace(/(\d+)(\d{3})/, '$1' + ',' + '$2');
      }
      return val;
    }
    if ($('.paragraph--type--numbers').isInViewport()) {
      if (!$(".paragraph--type--numbers").hasClass("done")){
          $(".paragraph--type--numbers").addClass("done");
          $('.paragraph--type--numbers .field--name-field-number').each(function () {
              var duration=2000;
              if (!$(this).parent().parent().attr("data-time")==""){
                  duration=$(this).parent().parent().attr("data-time")*1000;
              }
              var start=0;
              $(this).prop('Counter',start).animate({
                  Counter: $(this).text()
              }, {
                  duration: duration,
                  easing: 'linear',
                  step: function (now) {
                      $(this).text(commaSeparateNumber(Math.ceil(now)));
                  }
              });
          });
      }
  }
    $(window).on('resize scroll', function() {
      if ($('.paragraph--type--numbers').isInViewport()) {
          if (!$(".paragraph--type--numbers").hasClass("done")){
              $(".paragraph--type--numbers").addClass("done");
              $('.paragraph--type--numbers .field--name-field-number').each(function () {
                  var duration=2000;
                  if (!$(this).parent().parent().attr("data-time")==""){
                      duration=$(this).parent().parent().attr("data-time")*1000;
                  }
                  var start=0;
                  $(this).prop('Counter',start).animate({
                      Counter: $(this).text()
                  }, {
                      duration: duration,
                      easing: 'linear',
                      step: function (now) {
                          $(this).text(commaSeparateNumber(Math.ceil(now)));
                      }
                  });
              });
          }
      }
    });
  }
      /*********  bar scroll   *******/

      var screenTop = $(document).scrollTop();
      $(".bar-inner").css("width",100 - (screenTop/($(document).height()-$(window).height()))*100+"%");
      $(window).scroll(function(){
        screenTop = $(window).scrollTop()
        $(".bar-inner").css("width",100 - (screenTop/($(document).height()-$(window).height()))*100+"%");
      });

      /*****  forms ********/

      if ($("form .messages--status").length > 0){
        $("form .messages--status").parent().parent().addClass("confirmation-message");
      }
      
      $(document).ajaxStart(function() {
        $("form").addClass("ajax");
      });
      $(document).ajaxComplete(function() {
        $("form").removeClass("ajax");
        if ($("form .messages--status").length > 0){
          $("form .messages--status").parent().parent().addClass("confirmation-message");
        }
      });

      $( document ).ajaxComplete(function() {
        $('.required.error').each(function(element){
            $(this).focus();
            return false;
        });
      });


      function autoHeightAnimate(element, time){
				var curHeight = element.height(),
				autoHeight = element.css('height', 'auto').height(); 
				element.height(curHeight);
				element.stop().animate({ height: autoHeight }, time);
			}

      /*****  Cubes more service PG */

      $(".paragraph--type--cubes .more").unbind('click').bind('click', function (e) {
        var nav = $(this).parent().find(".hide-section");
        if ($(this).parent().find(".hide-section").hasClass("show")){
          $(this).parent().find(".hide-section").removeClass("show");
          nav.stop().animate({ height: '0' }, 500);
        } else {
          $(this).parent().find(".hide-section").addClass("show");
          $(this).hide();
          autoHeightAnimate(nav, 500);
        }
      });


      /*****  FAQ PG */

      $(".paragraph--type--faq-item .field--name-field-questions").unbind('click').bind('click', function (e) {
        var nav = $(this).parent().children(".field--name-field-answers");
        if ($(this).parent().hasClass("show")){
          $(this).parent().removeClass("show");
          nav.stop().animate({ height: '0' }, 500);
        } else {
          $(".paragraph--type--faq-item.show .field--name-field-answers").stop().animate({ height: '0' }, 500);
          $(".paragraph--type--faq-item.show").removeClass("show");
          $(this).parent().addClass("show");
          autoHeightAnimate(nav, 500);
        }
      });

      /*****  responsive two level menu  */


      $(".region-menu .menu--main > ul.menu > li.menu-item--expanded span").unbind('click').bind('click', function (e) {
        var nav = $(this).parent().find("ul");
        if ($(this).parent().hasClass("menu-item--active-trail")){
          $(this).parent().removeClass("menu-item--active-trail");
          nav.stop().animate({ height: '0' }, 500);
        } else {
          $(".region-menu .menu--main > ul.menu > li.menu-item--expanded ul").stop().animate({ height: '0' }, 500);
          $(".region-menu .menu--main > ul.menu > li.menu-item--active-trail").removeClass("menu-item--active-trail");
          $(this).parent().addClass("menu-item--active-trail");
          autoHeightAnimate(nav, 500);
        }
      });
      
      // footer

      $(".top-page").on('click',function(e) {
				$('html, body').animate({scrollTop: $('body').offset().top - 0}, 500);
				return false;
			});

  
      // 4 icons

      $.fn.isInViewport = function() {
        var elementTop = $(this).offset().top;
        var elementBottom = elementTop + $(this).outerHeight();
      
        var viewportTop = $(window).scrollTop();
        var viewportBottom = viewportTop + $(window).height();
      
        return elementBottom > viewportTop && elementTop < viewportBottom;
      };
      
      $(window).scroll(function(){
        screenTop = $(window).scrollTop()
        $(".field--name-field-paragraphs > .paragraph").each(function(){
          if ($(this).isInViewport()) {
            $(this).addClass("visible");
          }
        });
      });

      $(".field--name-field-paragraphs > .paragraph").each(function(){
        if ($(this).isInViewport()) {
          $(this).addClass("visible");
        }
      });



     }
  };

})(jQuery, Drupal);