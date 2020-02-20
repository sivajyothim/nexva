/*
 * admin.js
 * admin jQuery
 * author : Heshan (heshan at nexva dot com)
 * package : neXva V 2.0
 */

// document ready function
$(document).ready(function(){
	

  // enable JS vallidations
  try {
	  $('#product_basic_info').ketchup();
	  $('#claim').ketchup();
	  $('.form_validate').ketchup();
  } catch (e){
	  
  }

    /*$("#click-me").click(function(){
        $("#user-message").removeClass("user-unread-messages");
    });*/

  //    $('#checkchildren ul').hide();
  // category checkboxes, when change
  $('#checkchildren input:.parent').change(function() {
    if ($(this).is(":checked")) {
      var current = $(this).parent('li');
      current.children('ul').show();
    } else {
      var current = $(this).parent('li');
      current.children('ul').hide();
      //            if(!parent_li.children('ul').find("input").is(":checked"))
      current.children('ul').find("input").removeAttr("checked");
    }
  });

  // allow only to seleact one category
  // adding to sticky only to one category
  $('#checkchildren input:.child').change(function() {
    if ($(this).is(":checked")) {
      //            alert('oki');
      $('#checkchildren input:.child').each(function(){
        if ($(this).is(":checked")) {
          $(this).removeAttr('checked');
        //                    alert('checked found');
        }
      });
      $(this).attr('checked', 'checked');

    } else {
  //            alert('oki');
  }
  });


  // check onload
  $('#checkchildren input:.parent').each(function() {
    // Find this list's parent list item.
    if ($(this).is(":checked")) {
      //            alert('oki');
      //            alert($(this).val());
      $(this).next("ul").show();
    }
    else {
      // Hide all lists except the parent selected.
      var parent_li = $(this).parent('li');
      parent_li.children('ul').hide();
    }
  });
    
  // adding to sticky only to one category
  $('#checkchildren input:.child').each(function() {
    if ($(this).is(":checked")) {

      //            alert('oki');
      var parent_li = $(this).parents('li');
      //            alert(parent_li.html());
      parent_li.children('ul').show();
    } else {
  //            var parent_li = $(this).parent('li');
  //            parent_li.children('ul').hide();
  //            if(!parent_li.children('ul').find("input").is(":checked"))
  //            parent_li.children('ul').find("input").removeAttr("checked");
  }
  });


  // Add confirmation on the Save button button
  $(".confirm").click(function(){
    var approve = confirm('Confim save details of this product?.');
    if(approve)
      return true;
    else
      return false;
  });
  
  
  if(!$("#device_os").val() || $("#device_os").val() == 'any'){
      $("#build_supported_versions_1").hide();
  }
  
  $("#device_os").change(function() {
  	
  	 if($("#device_os").val() && $("#device_os").val() != 'any')	{
  		 
  		 $("#build_supported_versions_1").show();
  		 
  		 if($('#minimum_version').attr('checked'))	{
 		  	  
 			  $("#build_supported_versions_2").show();
 			  
 		  } else {
 			  
 			  $("#build_supported_versions_2").hide();
 			  
 		  }
 		 
  		 
  	 } else {
  		 
  		 $("#build_supported_versions_1").hide();
  	 }

    });
  
  
  if($('#minimum_version').attr('checked'))	{
	  	  
	  $("#build_supported_versions_2").show();
	  
  } else {
	  
	  $("#build_supported_versions_2").hide();
	  
  }
  
  
  $("#minimum_version").click(function() {
	  
	  
	  if($('#minimum_version').attr('checked'))	{
		  
		  
		  $("#build_supported_versions_2").show();
		  
	  } else {
		  
		  $("#build_supported_versions_2").hide();
		  
	  }
  
});

  
  // submit button to nevigate to the next step
  //    $(".submit").click(function () {
  ////        $(".submit").attr('value', 'adafsd');
  ////        alert('oki');
  ////        var links = $(".selector a");
  //        var content = $(".selector a").parent().attr("title");
  //
  //        $(".selector a").parents().children("a").each(function() {
  //            var tab = $(this).attr("href");
  //            $("#"+content + " ."+tab).hide();
  //        });
  //
  //        $(".selector a").parents().children("a").removeClass("active");
  ////        $(".selector a").addClass("active");
  //
  //        var show_tab = $(this).attr("href");
  //        $("#"+content + " ."+show_tab).show();
  ////        $("#"+content + " ."+show_tab).addClass("active");
  //
  //        return false;
  //    });
  //
  //    $(".selector a").click(function () {
  //        var content = $(this).parent().attr("title");
  //
  //        $(this).parents().children("a").each(function() {
  //            var tab = $(this).attr("href");
  //            $("#"+content + " ."+tab).hide();
  //        });
  //
  //        $(this).parents().children("a").removeClass("active");
  //        $(this).addClass("active");
  //
  //        var show_tab = $(this).attr("href");
  //        $("#"+content + " ."+show_tab).show();
  //
  //        return false;
  //    });

  $("#button_type").change(function(){
    alert("oki");
  });


  $(function() {
    setInterval( "slideSwitch()", 5000 );
  });
  
  $("#price").keyup(function() {
	  

	  
	  if($("#price").val() > 0)	{

		  $actual_amount = "You will get US $ " + $("#price").val()*($("#payout").val()/100)

		  $hostname = window.location.hostname;

		  $("#info").attr("href", "http://"+$hostname+"/product/royalties-display/amount/"+$("#price").val())
		  $('#info').html( " Click here to get your earnings" );

	  }

	  else
	  {
		  $('#info').html( " " );
	  }
	  

  });


  

});

// adding JS slideshow
function slideSwitch() {
  var $active = $('.last-active');
  if ( $active.length == 0 )
    $active = $('.first');
  var $next =  $active.next(".announcement li");

  if ( $next.length == 0 ){
    //    alert('nno any next');
    $next = $('.first');
    $next.removeClass('disabled');
  }
  
  $active.removeClass('last-active');
  $active.addClass('disabled');
  
  $next.css({
    opacity: 0.0
  })
  .addClass('last-active')
  .removeClass('disabled')
  .animate({
    opacity: 1.0
  }, "slow", function() {
    });
}