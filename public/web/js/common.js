/**
 * Handy little file for all our common js bits and pieces
 */

var __SUGGEST_ARR	= [];

$(document).ready(function(){
	/**
	 * Message/Error handling
	 */
	$('.message-success, .message-error, .message-notice').click(function(){
		$(this).slideUp(300);
	})
			
	/**
	 * autocomplete for search
	 */
	$(function() {
		var cache = {}, 
		lastXhr;

		$("#q").autocomplete({
			minLength: 3,
			source: function( request, response ) {
				var term = request.term;
				if ( term in cache ) {
					response( cache[ term ] );
					return;
				}

				lastXhr = $.getJSON("/search/suggest/q/" + term, request, function(data, status, xhr) {
					cache[term] = data;
					if ( xhr === lastXhr ) {
						response(data);
					}
				});
			},
			select: function( event, ui ) {
				if (ui.item.id == 0) {
					window.location	= project_base_path + 'search/index/q/' + $("#q").val();
				} else {
					window.location	= '/' + ui.item.id;
				}
				
				return false;
			},
			open: function(event, ui) { $(".ui-autocomplete.ui-menu").css("z-index", 100); },
			close: function(event, ui) { $(".ui-autocomplete.ui-menu").css("z-index", -1); },
			delay: 500 
		});
	});

	
	//text suggestions
	$('.suggest').each(function(){
		if ($(this).val() != '') {return;}
		
		__SUGGEST_ARR[$(this).attr('id')]	= $(this).attr('title');
		
		$(this)
			.addClass('disabled_text')
			.val($(this).attr('title'))
			.focus(function(){
				if ($(this).val() == __SUGGEST_ARR[$(this).attr('id')]) {
					$(this).val('');
				}
				$(this).removeClass('disabled_text');
			})
			.blur(function(){
				var oldVal	= (__SUGGEST_ARR[$(this).attr('id')]) ? 
						__SUGGEST_ARR[$(this).attr('id')] : '';
				if ($(this).val() == '' && oldVal != '') {
					$(this)
						.addClass('disabled_text')
						.val(oldVal);
						
				}
			})
	});
});


/**
 * Clears input suggests
 */
function clearSuggests() {
	$('.suggest').each(function(){
		if (__SUGGEST_ARR[$(this).attr('id')]	== $(this).val()) {
			$(this).val('');
		}
	});
}

/**
 * Remove functionality to stop autofilling
 */
function removeSuggests() {
	clearSuggests();
	__SUGGEST_ARR	= [];
}