new function($) {
    $.fn.getCursorPosition = function() {
        var pos = 0;
        var input = $(this).get(0);
        // IE Support
        if (document.selection) {
            input.focus();
            var sel = document.selection.createRange();
            var selLen = document.selection.createRange().text.length;
            sel.moveStart('character', -input.value.length);
            pos = sel.text.length - selLen;
        }
        // Firefox support
        else if (input.selectionStart || input.selectionStart == '0')
            pos = input.selectionStart;

        return pos;
    }
} (jQuery);

$(document).ready(function(){
	
	//put in a ui dialog box for later
	var div 	= $('<div>').attr('id', 'transDialog').appendTo('body');
	var input	= $('<input>').attr({
		'type'	: 'hidden',
		'id'	: 'phrase'
	});
	var translation	= $('<textarea>').attr({
		'type'	: 'text',
		'id'	: 'translationValue',
		'rows'	: '3',
		'cols'	: '40'
	});
	
	var message		= $('<div>').attr('id', 'dialogTitle');
	var phraseText	= $('<p>').attr('id', 'phraseCont').html('<strong>Phrase</strong><label id="phraseId"></label>');
	var phraseValue	= $('<p>').attr('id', 'valueCont').html('<strong>Current Value</strong><label id="phraseText"></label>');
	var transLabel	= $('<p>').append('<strong>Translation</strong>').append(translation);
	var defultValue	= $('<p>').html('<strong>Default</strong><label id="phraseDefault"></label>');
	var tagsPara	= $('<p>').attr('id', 'tagsPara');
	var tagsLabel	= $('<label>').attr('for', 'tagSelect').html('Available Tags');
	var tags	= $('<select>').attr('id', 'tagSelect').change(insertTag);
	var option	= $('<option>').val('').html('[SELECT]');
	tags.append(option);
	if (typeof(__PHRASE_TAGS) != 'undefined') {
		for (tag in __PHRASE_TAGS) {
			option	= $('<option>').val(tag).html(tag + ' => ' +__PHRASE_TAGS[tag]);
			tags.append(option);
		}
	}
	tagsPara.append(tagsLabel).append(tags);
	
	div
	.append(message)
	.append(phraseText).append(phraseValue).append(tagsPara)
	.append(transLabel).append(input).append(defultValue);
	
	var dialog	= div.dialog({
		'autoOpen'	: false,
		'modal'		: true,
		'width'		: 465,
		'height'	: 360,
		'buttons' 	: {
			Cancel: function() {
				$(this).dialog("close");
			},
			"Add Translation": function() {
				saveTranslation()
				$(this).dialog("close");
			}
		}
	});
	
	function insertTag() {
		$this	= $(this);
		var pos = $('#translationValue').getCursorPosition();
		var tag	= ' {T_' + $this.val() + '} ';
		var text	= $('#translationValue').val().substring(0, pos) +
					  tag + $('#translationValue').val().substring(pos);
		$('#translationValue').val(text);
		$this.children().removeAttr('selected').find('option:first').attr('selected', 'selected');
	}
	
	function saveTranslation() {
		var t	= $('#translationValue').val().trim();
		var p	= $('#phrase').val();
		var s	= encodeURI(__SECTION);
		var u   = '/translate/add/s/' + s + '/p/' + p + '/t/' + t
		
		showPendingStatus(p, t);
		
		$.get(u, {}, function(data){
			data	= eval('(' + data + ')');
			if (data.error) {
				alert(data.error);
			} else {
				var p 	= data.phrase;
				var t	= data.translation;
				
				$('#transPending' + p).slideUp().remove();
				var $div	= $('<div>')
									.addClass('quick_message')
									.html("<p>Translation added for " + p + " (" + t + "). Refresh to see changes (click to dismiss)</p>")
									.hide();
				if ($('.quick_message_cont').length == 0) {
					$('body').prepend('<div class="quick_message_cont"></div>');
				}
				$('.quick_message_cont').prepend($div);
				$div.slideDown();
				setTimeout(function(){
					$div.slideUp(400, function(){$div.remove()}); 
				}, 5000);
				
				$('span[rel="' + p + '"]').removeClass('ui-icon-circle-plus').addClass('ui-icon-check');
			}
		});
	}
	
	function showPendingStatus(phraseKey, phraseVal) {
		phraseVal	= phraseVal.substr(0, 30);
		if (!$('#transPendingCont').length) {
			var div 	= $('<div>').attr({
				'class'	: 'quick_message_cont',
				'id'	: 'transPendingCont'
			});
			$('body').prepend(div);
		} 
		
		var message		= $('<p>').html('<span style="float:left;" class="ui-icon ui-icon-alert"></span>Saving phrase ' + phraseKey + ' (' + phraseVal + ')');
		var mesCont		= $('<div>')
							.attr('id', 'transPending' + phraseKey)
							.addClass('quick_message_pending')
							.html(message);
		
		$('#transPendingCont').append(mesCont);
	}
	
	$('.translate-me').click(function(e){
		var p	= encodeURI($(this).attr('rel'));
		$('#phrase').val(p);
		$('#phraseId').html(p);
		$('#phraseText').html($(this).attr('title'));
		$('#phraseDefault').html($(this).attr('default'));
		
		$('#translationValue').val('');
		
		$('#dialogTitle').html('You are adding translations for <strong>' + $(this).attr('language') + '</strong>')
		dialog.dialog('option', 'title', 'Add translation in ' + $(this).attr('language'));
		dialog.dialog('open');
		return false;
	});
	
	$('.quick_message p').live('click', function(){
		$(this).parent().slideUp();
	});
})