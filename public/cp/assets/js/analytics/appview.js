/**
 * This file really needs to be broken up into parts. Right now 
 * it contains all the graphs in one file :( 
 * If you have time, please refactor and do the same for CP - JP
 */

var __CHART_OPTIONS	= null;

function showComingSoon() {
	$('.coming_soon_overlay').remove();
	$('.coming_soon').each(function(){
		var $csDiv	= $('<div>').addClass('coming_soon_overlay').hide();
		var $this 	= $(this);
		var offset	= $this.offset();
		
		$csDiv.insertAfter($this);
		$csDiv.css({
			height 	: $this.outerHeight() + 'px',
			width  	: $this.outerWidth() + 'px',
			position : 'absolute',
			top		: offset.top,
			left	: offset.left
		});
		$this.css('opacity', '0.4');
		$csDiv.show();
	});
}

function getFormattedDate(dateObj) {
	var year	= dateObj.getFullYear();
	var month	= ((dateObj.getMonth() + 1) < 10) ?  '0' + (dateObj.getMonth() + 1) : dateObj.getMonth() + 1;
	var date	= dateObj.getDate();
	return year + '-' + month + '-' + date;
}

$(document).ready(function(){
	
	$(window).resize(showComingSoon);
	showComingSoon();
	
	
	$( ".date" ).attr('readonly', 'readonly')
	
	$('#date-range').change(function(){
		if ($(this).val() == '') {
			return false;
		}
		var endTime		= new Date();
		var startTime	= new Date(endTime.getTime() - (parseInt($(this).val(), 10) * 1000));
		
		$("[name='from_view']").val(getFormattedDate(startTime));
		$("[name='to_view']").val(getFormattedDate(endTime));
		
		$(this).parent().submit();
	});
	
	try {
		initViewByDateChart(); // overall app views by date
		initViewByDeviceChart(); // most popular devices
		initViewByAppChart(); // most popular products
		initViewByPlatformChart();//view by platforms
		initDownloadByAppChart(); // most popular downloads
		initAppViewComparison(); //shows chart that compares against multiple products
		initAppViewByChaps(); //shows breakdown by chap
	} catch(ex){clog(ex);}
	

	
	
	/**
	 * Shows a chart with the most popular products 
	 * and allows you to compare them agasint each other
	 */
	function initAppViewComparison() {
		if (typeof __DATA_TOP_APP_COMPARISON == 'undefined') {
			return false;
		}
		
	    var apps, date;
	    var count = 0;
	    var datasets	= {};
	    for (apps in __DATA_TOP_APP_COMPARISON) {
	    	var plots		= [];
			for (date in __DATA_TOP_APP_COMPARISON[apps]) {
				plots.push([date, __DATA_TOP_APP_COMPARISON[apps][date]]);
			}
			datasets[apps]			= {};
			datasets[apps]['label']	= apps;
			datasets[apps]['data']	= plots;
	    }
	    
	    var i = 0;
	    $.each(datasets, function(key, val) {
	        val.color = i;
	        ++i;
	    });
	    
	    var choiceContainer = $("#app_filter");
	    var $ul 			= $('<ul>')
	    $.each(datasets, function(key, val) {
	    	var $li = $('<li>');
	    	$li.html('<input type="checkbox" name="' + key +
                    '" checked="checked" id="id' + key + '">' +
                    '<label for="id' + key + '">'
                     + val.label + '</label>');
	    	$ul.append($li);
	    });
	    choiceContainer.append($ul);
	    choiceContainer.find("input").click(plotAccordingToChoices);
	    
	    var plot;
	    
	    function plotAccordingToChoices() {
	    	
		    var options = {
    			series: {
				    lines: { show: true, fill: false, fillColor: '#eee'},
				    points: { show: true }
			    },
			    crosshair: { show: "x" },
			    grid: { hoverable: true, clickable: true },
		        xaxis: {
		            mode: "time"
		        },
		        selection: { mode: "x" },
		        yaxis : {
		            min : 0
		        }
	        };
			    
	    	
	        var data = [];

	        choiceContainer.find("input:checked").each(function () {
	            var key = $(this).attr("name");
	            if (key && datasets[key])
	                data.push(datasets[key]);
	        });

	        if (data.length > 0) {
	        	plot	= $.plot($("#stats_view_app_comparison"), data, options);
	        	showOverview(plot, $("#stats_view_app_comparison"), data, $("#overview_comparison"), options);
	        }
	    }
	    plotAccordingToChoices();
	}
	
	/**
	 * Shows product views and downloads grouped by date
	 */
	function initViewByDateChart() {
		
		if (typeof __DATA_DATE == 'undefined' && typeof __DATA_DOWNLOADS == 'undefined') {return false;}
		
	    var options = {
		    series: {
			    lines: { show: true},
			    points: { show: true }
		    },
		    grid: { hoverable: true, clickable: true },
	        xaxis: {
	            mode: "time",
	        },
	        yaxis : {
	            min : 0
	        },
	        selection: { mode: "x" },
	        colors : ['#E37923']
	    };
	    var plots		= [],
			pointsView		= [],
			pointsDownload	= [];
	    
	    
		for (date in __DATA_DATE) {
			pointsView.push([date, __DATA_DATE[date]]);
		}
		plots.push({data: pointsView, label: 'Visits'});
	
		for (date in __DATA_DOWNLOADS) {
			pointsDownload.push([date, __DATA_DOWNLOADS[date]]);
		}
		plots.push({data: pointsDownload, label: 'Downloads'});
		
	    var plot	= $.plot($("#stats_view_by_date"), plots, options);
	    showOverview(plot, $("#stats_view_by_date"), plots, $("#overview_view_by_date"), options);
	    
	    
	    __DATA_DOWNLOADS = __DATA_DATE = pointsView = pointsDownload = null;
	}
	
	/**
	 * Shows product views grouped by device
	 */
	function initViewByDeviceChart() {  
		if (typeof __DATA_DEVICE == 'undefined') {return false;}
		showPieChart(__DATA_DEVICE, $("#stats_view_by_device"), true);
	}
	
	/**
	 * Shows a pie chart with the platform as slices
	 */
	function initViewByPlatformChart() {
		if (typeof __DATA_PLATFORMS == 'undefined') {return false;}
		showPieChart(__DATA_PLATFORMS, $("#stats_view_by_platform"));
	}
	
	/**
	 * Shows top products by chap
	 */
	function initAppViewByChaps() {
		if (typeof __DATA_CHAPS == 'undefined') {return false;}
		showPieChart(__DATA_CHAPS, $("#stats_apps_by_chap"));
	}
	
	/**
	 * Shows top products by view
	 */
	function initViewByAppChart() {
		if (typeof __DATA_APP == 'undefined') {return false;}
		showPieChart(__DATA_APP, $("#stats_view_by_app"));
	}
	
	
	
	/**
	 * Shows top products by view
	 */
	function initDownloadByAppChart() {
		if (typeof __DATA_TOP_DOWNLOAD == 'undefined') {return false;}
		showPieChart(__DATA_TOP_DOWNLOAD, $("#stats_downloads_by_app"));
	}

	
	var previousPoint = null;
	$("#stats_view_by_date, #stats_view_app_comparison").bind("plothover", function (event, pos, item) {
        if (item) { 
            if (previousPoint != item.datapoint) {
                previousPoint = item.datapoint;
                
                $("#tooltip").remove();
                var x = item.datapoint[0],
                    y = item.datapoint[1];
                showTooltip(item, 
                			y + ' event(s) near ' + getFormattedMonth(x));
            }
        }
        else {
            $("#tooltip").remove();
            previousPoint = null;            
        }
	});
	
}); //end of document ready

/**
 * Common method for pie chart creations
 * @param dataSource an associated array with the values
 * @param $container the jquery DOM object that the chart will be drawn in
 * @param splitOnHyphen For when you have an ID-NAME combined label and you want to split it
 */
function showPieChart(dataSource, $container, splitOnHyphen) {
	if (typeof dataSource == 'undefined') {return false;}
	if (typeof splitOnHyphen == 'undefined') {splitOnHyphen = false;}
	var data	= [];
	for (item in dataSource) {
		var slice	= {};
		slice.label	= ((!splitOnHyphen) ? item : item.substr(item.indexOf('-') + 1)) + ' (' + dataSource[item] + ') ';
		slice.data	= dataSource[item];
		data.push(slice)
	}
	
	if (!data.length) {return false;}
	
	$.plot($container, data, getPieOptions());
	dataSource	= data = null;
}

/**
 * Shows the zoomable overlay that is below the charts.
 * @param plot - Original chart object
 * @param $chartDom - jQuery DOM object that contains the original chart
 * @param plots - The datapoints shown on the chart
 * @param $overviewDom - jQuery DOM object container for the zoomable overview
 * @param chartOptions - Original chart options so we can reset  
 */
function showOverview(plot, $chartDom, plots, $overviewDom, chartOptions) {
	var overview = $.plot($overviewDom, plots, {
        series: {
            lines: { show: true, lineWidth: 1 },
            shadowSize: 0
        },
        xaxis: { ticks: [], mode: "time" },
        yaxis: { ticks: [], min: 0, autoscaleMargin: 0.1 },
        selection: { mode: "x" },
        legend: {show : false},
        colors : ['#E37923']
    });

	$chartDom.bind("plotselected", function (event, ranges) {
        // do the zooming
        plot = $.plot($chartDom, plots,
                      $.extend(true, {}, chartOptions, {
                          xaxis: { min: ranges.xaxis.from, max: ranges.xaxis.to }
                      }));

        
        // don't fire event on the overview to prevent eternal loop
        overview.setSelection(ranges, true);
    });
    
    $overviewDom.bind("plotselected", function (event, ranges) {
        plot.setSelection(ranges);
    });
    
    var points	= plots[0].data;
 
    //If the graph is large, zoom in just part of it at the start so it's not congested
    if (points.length > 30) {
    	var ranges	= {
	    	'xaxis': {
	    		'from' 	: points[0][0] - 1209600000, //14 days in milisecs :)
	    		'to'	: points[0][0]
	    	},
	    	'yaxis': {
	    		'from': 0,
	    		'to' : 1
	    	},
	    }
    	overview.setSelection(ranges);
    }
    return null;
}


/**
 * Common options object for the pie charts
 */
function getPieOptions() {
	return {
		    series: {
	        pie: {
	            show: true,
	            radius: 0.9,
	            tilt: 0.5,
	            label: {
                    show: true,
                    radius: 1,
                    formatter: function(label, series){
		
                        return '<div title="' + series.label + '" style="font-size:8pt;text-align:center;padding:2px;color:#000;">'
                          		+ Math.round(series.percent) + '% ('+ series.data[0][1] + ')</div>';
                    },
                    background: {
                        opacity: 0.5
                    }
                }
	        }
	    },
	    legend: {
	            show: true,
	            backgroundOpacity : 0.4
	    },
	    grid: {
            hoverable: true
        }
	}
}

/**
 * Utility function to get the formatted date for a timestamp,
 * @todo Extend to allow masks
 * @param timestamp
 * @returns
 */
function getFormattedMonth(timestamp) {
	var d = new Date(timestamp);
	return d.toLocaleString();
}

/**
 * Generates the tooltip that is shown in line charts 
 * @param item
 * @param contents
 */
function showTooltip(item, contents) {
    $('<div id="tooltip">' + contents + '</div>').css( {
        position: 'absolute',
        display: 'none',
        top: item.pageY + 5,
        left: item.pageX + 15,
        border: '1px solid #fff',
        padding: '8px',
        'background-color': item.series.color,
        color : '#fff',
        opacity: 1,
        borderRadius: '5px'
        
    }).appendTo("body").fadeIn(200);
}



/**
 * Autocomplete
 */
$(function() {
	var cache = {}, 
	lastXhr;

	$("#app_search").autocomplete({
		minLength: 3,
		source: function( request, response ) {
			var term = request.term;
			if ( term in cache ) {
				response( cache[ term ] );
				return;
			}

			lastXhr = $.getJSON("/analytics-app/search-json/q/" + term, request, function(data, status, xhr) {
				cache[term] = data;
				if ( xhr === lastXhr ) {
					response(data);
				}
			});
		},
		select: function( event, ui ) {
			var url	= new String(window.location);
			url		= url.replace(/\/pro_id\/[0-9]*/g, '');
			if (url[url.length - 1] == '/') {url += 'pro_id/' + ui.item.id}
			else {url += '/pro_id/' + ui.item.id}
			window.location	=  url;
			return false;
		},
		delay: 500 
	});
});

function clog(item) {
	if (console.log) {
		try {
			console.log(item);
		} catch (ex) {}
	}
}