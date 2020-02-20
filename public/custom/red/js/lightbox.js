$(document).ready(function() {
			/*
			*   Examples - images
			*/

			$("#example1, #example2, #example3, #example4, #example5, #example6, #example7, #example8, #example9, #example10, #example11, #example12, #example13, #example14, #example15, #example16, #example17, #example18, #example19, #example20, ").fancybox({
				'titleShow'		: false
			});
			
			$("#tip1, #tip2, #tip3, #tip4, #tip5, #tip6, #tip7, #tip8, #tip9, #tip10, #tip11, #tip12, #tip13, #tip14, #tip15").click(function() {
	$.fancybox({
			'padding'		: 0,
			'autoScale'		: false,
			'transitionIn'	: 'none',
			'transitionOut'	: 'none',
			'title'			: this.title,
			'width'		: 680,
			'height'		: 495,
			'href'			: this.href.replace(new RegExp("watch\\?v=", "i"), 'v/'),
			'type'			: 'swf',
			'swf'			: {
			   	 'wmode'		: 'transparent',
				'allowfullscreen'	: 'true'
			}
		});

	return false;
});
			
		});