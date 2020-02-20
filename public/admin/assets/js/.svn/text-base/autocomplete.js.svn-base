var _ac	= {};


/**
 * Pass in the input box, the url to be called and the select event to begin
 */

_ac.init	= function(input, url, selectEvent){
	_ac[url]			= {};
	_ac[url].i			= input;
	_ac[url].url		= url;
	_ac[url].sEv		= selectEvent;
	_ac[url].cache 		= []; 
	_ac[url].lastXhr	= null;
	
	_ac[url].i.autocomplete({
		minLength: 3,
		source: function( request, response ) {
			(function(request, response, url){
				var term = request.term;
				if ( term in _ac[url].cache ) {
					response( _ac[url].cache[ term ] );
					return;
				}
	
				lastXhr = $.getJSON(_ac[url].url + term, request, function(data, status, xhr) {
					_ac[url].cache[term] = data;
					if ( xhr === lastXhr ) {
						response(data);
					}
				});
			})(request, response, _ac[url].url);
		},
		select: selectEvent,
		delay: 500 
	});
};

function _e(d){console.log(d)};
	