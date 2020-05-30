/* Add Custom Code Jquery
 ========================================================*/
$(document).ready(function(){
	// Fix hover on IOS
	$('body').bind('touchstart', function() {}); 
	// Product Cart Gallery
	$('.product-card__gallery .item-img').hover(function() {
		
		$(this).addClass('thumb-active').siblings().removeClass('thumb-active');
		var thumb_src = $(this).attr("data-src");
		$(this).closest('.product-item-container').find('img.img-responsive').attr("src",thumb_src);
	}); 
	
	// Messenger Top Link
	$('.list-msg').owlCarousel2({
		 autoplay: false,
		 items: 1,
		 nav: false,
		 loop: false,
		 autoplayHoverPause: true,
		 animateOut: 'slideOutUp',
		 animateIn: 'slideInUp',
		 autoplayTimeout: 5000,
		 autoplayHoverPause: false,
	});

	// click header search header 
	jQuery(document).ready(function($){
		$( ".search-header-w .icon-search" ).click(function() {
		$('#sosearchpro .search').slideToggle(200);
		$(this).toggleClass('active');
		});
	});

	
	// custom to show footer center
	$(".button-toggle").click(function () {
		if($(this).children('.showmore').hasClass('active')) $(this).children().removeClass('active');
		else $(this).children().addClass('active');
		
		
		
		if($(this).prev().hasClass('showdown')) $(this).prev().removeClass('showdown').addClass('showup');
		else $(this).prev().removeClass('showup').addClass('showdown');
	}); 


	$(".clearable").each(function() {
  
	  var $inp = $(this).find("input:text"),
	      $cle = $(this).find(".clearable__clear");

	  $inp.on("input", function(){
	    $cle.toggle(!!this.value);
	  });
	  
	  $cle.on("touchstart click", function(e) {
	    e.preventDefault();
	    $inp.val("").trigger("input");
	  });
	  
	});

});
