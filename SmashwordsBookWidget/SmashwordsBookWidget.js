// Thanks to http://sixrevisions.com by whose tutorial this functionality was inspired:
// http://sixrevisions.com/tutorials/javascript_tutorial/create-a-slick-and-accessible-slideshow-using-jquery/

jQuery(document).ready(function($){
	$(".swbw_slideshow").each(


function decorateSlider(){
	  var currentPosition = 0;
	  var slideWidth = 170;
	  var slideHeight = 212;
	  var slides = $('.swbw_slide', this);
	  var numberOfSlides = slides.length;
	  var swbw_slideshow = $(this);
	  var verticalEnabled = $(this).hasClass('vertical');

	  // Hack: Find out how many books should be displayed:
	  var noOfImagesToShow = verticalEnabled ? $(this).css('height') : $(this).css('width');
	  noOfImagesToShow = noOfImagesToShow.substring(0, noOfImagesToShow.length-2);
	  var divisor = verticalEnabled ? slideHeight : slideWidth;
	  noOfImagesToShow = (noOfImagesToShow)/divisor;
	  
	  // Insert left and right arrow controls in the DOM
	  if(! $(this).hasClass('no_slides') && numberOfSlides > noOfImagesToShow){
		  var left = verticalEnabled ? '&and;' : '&laquo;';
		  var right = verticalEnabled ? '&or;' : '&raquo;';
		  
	  $(this)
	    .prepend('<span class="swbw_control swbw_leftControl">'+left+'</span>')
	    .append('<span class="swbw_control swbw_rightControl">'+right+'</span>');

	  // Hide left arrow control on first load
	  manageControls(currentPosition);

	  // Create event listeners for .controls clicks
	  $('.swbw_control', this)
	    .bind('click', function(){
	    // Determine new position
	      currentPosition = ($(this).hasClass('swbw_rightControl'))
	    ? currentPosition+1 : currentPosition-1;
	      // Dont go over borders
	      if(currentPosition<0)currentPosition=0;
	      if(currentPosition>numberOfSlides-noOfImagesToShow)currentPosition=numberOfSlides-noOfImagesToShow;
	      
	      // Hide / show controls
	      manageControls(currentPosition);
	      // Move swbw_slideInner using margin-left
	      if(verticalEnabled){
		      $('.swbw_slideInner',swbw_slideshow).animate({
			        'marginTop' : slideHeight*(-currentPosition)
			      });
	      }else{
	      $('.swbw_slideInner',swbw_slideshow).animate({
	        'marginLeft' : slideWidth*(-currentPosition)
	      });
	      }
	    });
	  }
	  // manageControls: Hides and shows controls depending on currentPosition
	  function manageControls(position){
	      // Hide left arrow if position is first slide
	      if(isLeftAllowed(position)){
	    	  $('.swbw_leftControl', swbw_slideshow).removeClass("swbw_control_deactivated");
	      }else{
	    	  $('.swbw_leftControl', swbw_slideshow).addClass("swbw_control_deactivated");
	      }
	      // Hide right arrow if position is last slide
	      if(isRightAllowed(position)){
	    	  $('.swbw_rightControl', swbw_slideshow).removeClass("swbw_control_deactivated");   	
	      } else{
	    	  $('.swbw_rightControl', swbw_slideshow).addClass("swbw_control_deactivated");
	      }
	    }
	  
	  function isLeftAllowed(position){
		  return position>0;
	  }
	  
	  function isRightAllowed(position){
		  return position<numberOfSlides-noOfImagesToShow;
	  }
	  
	  })});
