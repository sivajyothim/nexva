function createViewer(story, files) {
	return {
		highlightLinks: story.highlightLinks,
		currentPage : -1,
		cache: [],
		initialize: function() {
			this.createImageMaps();
			this.addHotkeys();
		},
		preloadImages: function() {
			var pages = story.pages;
			for(var i = 0; i < pages.length; i ++) {
				var cacheImage = document.createElement('img');
				cacheImage.src = encodeURIComponent(files) + '/' + encodeURIComponent(pages[i].image);
				this.cache.push(cacheImage);
			}
		},
		createImageMaps: function() {
			var pages = story.pages;
			for(var i = 0; i < pages.length; i ++) {
				var page = pages[i];
				var name = 'map' + i;
				var map = $('<map/>', {
					id: name,
					name: name
				});
				map.appendTo('body');
				for(var j = page.links.length - 1; j >= 0; j --) {
					var link = page.links[j];
					var title, href, target;
					if(link.page != null) {
						title = story.pages[link.page].title;
						href = 'javascript:viewer.goTo(' + link.page + ')';
						target = null;
					} else {
						title = link.url;
						href = link.url;
						target = '_blank';
					}
					
					$('<area/>', {
						shape: 'rect',
						coords: link.rect.join(','),
						href: href,
						alt: title,
						title: title,
						target: target
					}).appendTo(map);
				}
			}
		},
		addHotkeys: function() {
			var v = this;
			$(document).bind('keydown', 'right space return', function() {
				v.next();
			});
			$(document).bind('keydown', 'left backspace', function() {
				v.previous();
			});
			$(document).bind('keydown', 'shift', function() {
				v.toggleLinks();
			});
		},
		getPageHash: function(index) {
			var image = story.pages[index].image;
			return image.substring(0, image.length - 4); // strip .png suffix
		},
		getPageHashes: function() {
			if(this.pageHashes == null) {
				var hashes = {};
				var pages = story.pages;
				for(var i = 0; i < pages.length; i ++) {
					hashes[this.getPageHash(i)] = i;
				}
				this.pageHashes = hashes;
			}
			return this.pageHashes;
		},
		getPageIndex: function(page) {
			var index;

			if (typeof page === "number") {
				index = page;
			} else if (page === "") {
				index = 0;
			} else {
				index = this.getPageHashes()[page];
				if (!index) {
					var pageNumber = parseInt(page);
					if (isNaN(pageNumber))
						index = 0;
					else
						index = pageNumber - 1;
				}
			}
			return index;
		},
		goTo : function(page) {
			var index = this.getPageIndex(page);
			
			if (index != this.currentPage && index >= 0 && index < story.pages.length) {
				this.currentPage = index;
				this.refresh();
				$(document).attr('title', story.title + ': ' + story.pages[index].title);
				
				location.hash = '#' + encodeURIComponent(this.getPageHash(this.currentPage));
			}
		},
		refresh: function() {
			var page = story.pages[this.currentPage];
			
			$('#nav h1').html(page.title);
			$('#nav-left-prev').toggleClass('disabled', !this.hasPrevious());
			$('#nav-left-next').toggleClass('disabled', !this.hasNext());
			$('#nav-right-links').toggleClass('active', this.highlightLinks);
			$('#nav-right-links').toggleClass('disabled', page.links.length == 0);
			
			if(this.hasPrevious()) {
				$('#nav-left-prev a').attr('title', story.pages[this.currentPage - 1].title)
			} else {
				$('#nav-left-prev a').removeAttr('title');
			}
			
			if(this.hasNext()) {
				$('#nav-left-next a').attr('title', story.pages[this.currentPage + 1].title)
			} else {
				$('#nav-left-next a').removeAttr('title');
			}
			
			var img = $('<img/>', {
				src : encodeURIComponent(files) + '/' + encodeURIComponent(page.image),
				usemap: '#map' + this.currentPage,
				width: page.width,
				height: page.height
			});
			
			var highlight = this.highlightLinks;
	
			img.one('load', function() {
				var content = $('#content');
				content.empty();
				img.appendTo(content);
				content.width(page.width);
				
				img.maphilight({
					alwaysOn: highlight,
					stroke: false,
					fillColor: 'FFC400',
					fillOpacity: 100.0/255
				});
			}).each(function() {
				if(this.complete || (jQuery.browser.msie && parseInt(jQuery.browser.version) == 6)) 
					$(this).trigger('load');
			});
		},
		next : function() {
			if (this.hasNext())
				this.goTo(this.currentPage + 1);
		},
		previous : function() {
			if (this.hasPrevious())
				this.goTo(this.currentPage - 1);
		},
		hasNext : function() {
			return this.currentPage < story.pages.length - 1;
		},
		hasPrevious : function() {
			return this.currentPage > 0;
		},
		toggleLinks : function() {
			this.highlightLinks = !this.highlightLinks;
			this.refresh();
		},
		hideNavbar : function() {
			$('#nav').slideToggle('fast', function() {
				$('#nav-hide').removeClass('hidden');
			});
		},
		showNavbar : function() {
			$('#nav-hide').addClass('hidden');
			$('#nav').slideToggle('fast');
		}
	};
}

$(document).ready(function() {
	viewer.initialize();
	
	if(!!('ontouchstart' in window) || !!('onmsgesturechange' in window)) {
		$('body').removeClass('screen');
	}
	
	$(window).hashchange(function(e) {
		var hash = location.hash;
		if(hash == null || hash.length == 0)
			hash = '#';
		hash = '#' + hash.replace( /^[^#]*#?(.*)$/, '$1' );
		
		var page = decodeURIComponent(hash.substring(1));
		viewer.goTo(page);
	});
	$(window).hashchange();
});
