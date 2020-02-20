var	__RATINGS, __DEFAULT_RATE_LABEL, __CURRENT_RATING_HTML = null;

__RATINGS			= new Array;
__RATINGS[1]	= 'Uninstalling it now';
__RATINGS[2]	= 'Meh, could do better';
__RATINGS[3]	= 'Nothing special';
__RATINGS[4]	= 'Pretty good';
__RATINGS[5]	= 'Awesome!';

	
$(document).ready(function(){
	$('.starLinks').each(function(){
		$(this).attr('href', $(this).attr('href') + '/do/1');
	}) 
	
	__DEFAULT_RATE_LABEL	= $("#rateLabel").html();
	__CURRENT_RATING_HTML	= $('#fullRating').html();
	
	$('.starLinks').live('mouseover', rateHoverIn);
	$('.starLinks').live('mouseout', rateHoverOut);
	

	
  	$('.starLinks').live('click', function(){
  		if ($('#appRated').val() > 0) { //it's already been rated. 
  			$.get($(this).attr('href'), {}, function(message){
  				var inHtml	= '<ul id="theMessage" class="messages"><li>' + message + '</li></ul>';
  				$(".blkHead:first").prepend(inHtml);
  				$("#theMessage").delay(2000).slideUp(300);
  			})
  			return false;
  		}
	});

});

function rateHoverIn(e) {
	var rateLevel	= null;
	rateLevel	= $(this).attr('rel');
	count		= 1;
	$('#fullRating a img').each(function(){
		
		if (count > rateLevel) {
			$(this).attr('src', $('#starEmpty').attr('src'));
		} else {
			$(this).attr('src', $('#starFull').attr('src'));
		}
		count++;
	});
	$("#rateLabel").html('Your rating : ' + __RATINGS[rateLevel]);
}

function rateHoverOut(e) {
	$("#rateLabel").html(__DEFAULT_RATE_LABEL);
	$('#fullRating').html(__CURRENT_RATING_HTML);
}