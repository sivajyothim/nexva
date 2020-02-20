var __SUGGEST_ARR	= [];

$(document).ready(function(){

	$('#send_mail, #send_mail_friend').click(sendMail);
	addSuggests();

	/**
	 * Default behavior. Enter triggers button click
	 */
	$('#email_field').keydown(function(e){
		if (e.which == 13) {
			$('#send_mail').trigger('click');
		}
	});
	$('#my_name_friend, #email_field_friend').keydown(function(e){
		if (e.which == 13) {
			$('#send_mail_friend').trigger('click');
		}
	});
})

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
 * Adds the title text to the value as suggestion in lieu of labels
 */
function addSuggests() {
	$('.suggest').each(function(){
		__SUGGEST_ARR[$(this).attr('id')]	= $(this).attr('title');
		
		$(this)
			.val($(this).attr('title'))
			.addClass('disabled_text')
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
}

function doRequest(url, params, callback) {
	params.callback		= callback;
	params.chapId		= __CHAP_ID;
	
	$.get(url, params, function(data){
          var  jsonData=$.parseJSON(data);
           
                if(jsonData.error){
                    alert(jsonData.error);
                }
                
                if(jsonData.message){
                    alert(jsonData.message);
                }
		try {
                    
			var	jobj		= eval("(" + data + ")");
			var callback	=(jobj.callback) ? jobj.callback : '';
			if (window[callback]) {
				window[callback](true, jobj);
			}
		} catch (e) {
			showError('An error occured while processing your request. Please reload the page and try again');
		}
	});
}

/**
 * Common function for sending a mail to your self or tell a friend
 * @param callback
 * @param data
 */
function sendMail(callback, data) {
       
	if (callback == true) {
		var idAppend	= (data.isShare == true)? '_friend' : '';
		
		$('#send_mail_loader' + idAppend).fadeOut();
		$('#send_mail' + idAppend).removeAttr('disabled').removeClass('ui-state-disabled');
		
		if (data.error == '') {
			showMessage(data.message);
		} else {
			showError(data.error);
		}
		addSuggests();
		return;
	} else {
             
		clearSuggests();
		//find out which button was clicked. mail user or mail friend
		var idAppend	= ($(this).hasClass('friend')) ? '_friend' : '';
		
		var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/ 
		var eaddress            = $('#email_field' + idAppend).val();
		var appId	 	= $('#appId').val();
                var buildId	 	= $('#buildId').val();
		var promocode           = $('#promoCode').val();
		var chapId		= $('#chapId').val();
                var regType             = $('#regType').val();
                var capValue             = $('#capValue').val();
                
                var recaptchaResponseField='';
                var recaptchaChallengeField='';
                
                //if( chapId == '283006' ){
                    
                    var recaptchaResponseField		= ($('#recaptcha_response_field').val())?$('#recaptcha_response_field').val():'';
                    var recaptchaChallengeField		= ($('#recaptcha_challenge_field').val())?$('#recaptcha_challenge_field').val():'';
                //}
                
		if (!eaddress.match(re)) {
			alert('Please enter a valid email');
			return;
		}
		
		var myName		= $('#my_name_friend').val();
		
		var opts		= 
			{
				'email' : eaddress, 
				'appId' : appId,
                                'buildId' : buildId,
				'share'	: (idAppend != ''),
				'sender': myName,
				'promo'	: promocode,
				'chapId': chapId,
                                'regType': regType,
                                'recaptchaResponseField' : recaptchaResponseField,
                                'recaptchaChallengeField' : recaptchaChallengeField,
                                'capValue' : capValue
			};

		$('#send_mail_loader' + idAppend).fadeIn();
		$('#send_mail' + idAppend)
			.attr('disabled', 'disabled')
			.addClass('ui-state-disabled');
		doRequest('/nexlinker/sendmail', opts, 'sendMail');
	}
}

function showError(message) {
	if (message == '') return;
	var markup	= '<div style="padding: 0 .7em;" class="ui-state-error ui-corner-all">';
	markup		+= '<p><span style="float: left; margin-right: 0.3em;" class="ui-icon ui-icon-alert"></span>';
	markup		+= message + '</p></div>';

	$('#notices').append(markup);
	var gap	= 0;
	$('#notices div.ui-state-error').each(function(){
		$(this).delay(5000 + gap).slideUp(300);
		gap	+= 3000
	})
}

function showMessage(message) {
	if (message == '') return;
	var markup	= '<div style="padding: 0pt 0.7em;" class="ui-state-highlight ui-corner-all">';
	markup		+= '<p><span style="float: left; margin-right: 0.3em;" class="ui-icon ui-icon-info"></span>';
	markup		+= message + '</p>';
	
	$('#notices').append(markup);
	var gap	= 0;
	$('#notices div.ui-state-highlight').each(function(){
		$(this).delay(5000 + gap).slideUp(300);
		gap	+= 3000
	})
}