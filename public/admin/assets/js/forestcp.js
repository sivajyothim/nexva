 $(document).ready(function(){ 
    	
    	/* Drop Down Menu */
        $('ul.sf-menu').superfish({ 
            delay:       1000,
            dropShadows: false
        });
        
        /* Sidebar Datepicker */
        $('#datepicker').datepicker({
			inline: true
		});
		
		/* Sidebar Boxes Sortable */
		$("#sidebar").sortable({
			axis: 'y',
			connectWith: '.sort',
			delay: 300
		});
		
		/* wysiwyg */
		$('#wysiwyg').wysiwyg({
        css : { fontFamily: 'Trebuchet MS', fontSize : '11px'}
       	});

		
		/* Welcome Dialog */
		$('#dialog').click(function() {
			$('#welcome').dialog('open');
		})


		$('#welcome').dialog({
			autoOpen: false,
			width: 540,
			height: 180,
			bgiframe: true,
			modal: true,
			buttons: {
				"View ForestCP": function() { 
					$(this).dialog("close"); 
				}			
			}
		}); 
		
		/* Switch between Fixed and Fluid */
		$("#switch-layout").click(function() {
			$('#wrap').toggleClass('fixed');		
			return false;
		});
		
		/* Collapsible Boxes */
		
		/* removed my Jahufars' request 
		$("#content .title h3").css({ "cursor":"s-resize" });
		$("#content .title h3").click(function() {
			$(this).parent().find(".selector").toggle();
			$(this).parent().next().toggle();	
			$(this).parent().toggleClass('collapsed');	
			return false;
		});
		*/
		
		
		/* Error */
		$(".message").click(function () { 
			$(this).fadeOut();
		});
		
		/* Table */
		$('#content tbody tr:even td').addClass("alt");
		
		/* Tabs */
		$(".selector a.active").each(function() {
			var content = $(this).parent().attr("title");
			var tab = $(this).attr("href");

			$("#"+content + " div").hide();
			$("#"+content + " div."+tab).show();
			$("#"+content + " div div").show();
		});
		
			$(".selector a").click(function () { 
				var content = $(this).parent().attr("title");
			
					$(this).parents().children("a").each(function() {
					var tab = $(this).attr("href");
					$("#"+content + " ."+tab).hide();
			});
			
			$(this).parents().children("a").removeClass("active");
			$(this).addClass("active");
			
			var show_tab = $(this).attr("href");  
        	$("#"+content + " ."+show_tab).show();
        
        	return false;
		});
		
		/**
		* Stylesheet toggle variation on styleswitch stylesheet switcher.
		* Built on jQuery.
		* By Kelvin Luck ( http://www.kelvinluck.com/ )
		**/
			$(function()
				{
					// Call stylesheet init so that all stylesheet changing functions 
					// will work.
					$.stylesheetInit();
					
					// This code loops through the stylesheets when you click the link with 
					// an ID of "toggler" below.
					$('#toggler').bind(
						'click',
						function(e)
						{
							$.stylesheetToggle();
							return false;
						}
					);
					
					// When one of the styleswitch links is clicked then switch the stylesheet to
					// the one matching the value of that links rel attribute.
					$('.styleswitch').bind(
						'click',
						function(e)
						{
							$.stylesheetSwitch(this.getAttribute('rel'));
							return false;
						}
					);
				}
			);

    }); 
    
    