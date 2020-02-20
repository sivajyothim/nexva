jQuery(function() {
	jQuery('.error').hide();
	jQuery(".but").click(
			function() {
				// validate and process form
				// first hide any error messages
				jQuery('.error').hide();

				var name = jQuery("input#name").val();
				var email = jQuery("input#email").val();
				var emailReg = /^([\w-\.]+@([\w-]+\.)+[\w-]{2,4})?$/;
				var msg = jQuery("textarea#msg").val();
				
				var hasErrors	= false;
				
				if (name == "") {
					jQuery("span#name_error").show();
					jQuery("input#name").focus();
					hasErrors	= true;
				}
				if (email == "") {
					jQuery("span#email_error").show();
					jQuery("input#email").focus();
					hasErrors	= true;
				}
				
				if (!emailReg.test(email)) {
					jQuery("span#email_error2").show();
					jQuery("input#email").focus();
					hasErrors	= true;
				}

				if (msg == "") {
					jQuery("span#msg_error").show();
					jQuery("textarea#msg").focus();
					hasErrors	= true;
				}

				if (hasErrors == true) {return false;}

				var dataString = 'name=' + name + '&email=' + email + '&msg='
						+ msg + '&cpid=' + __CP_ID;
				// alert (dataString);return false;

				jQuery.ajax( {
					type : "POST",
					url : "/nexpage/contact/",
					data : dataString,
					success : function() {
						jQuery('#contact_form')
								.html("<div id='message'></div>");
						jQuery('#message').html(
								"<strong>Your message has been sent successfully</strong>")
								.append("<p>We will be in touch soon.</p>")
								.hide().fadeIn(1500, function() {
									jQuery('#message');
								});
					}
				});
				return false;
			});
});
