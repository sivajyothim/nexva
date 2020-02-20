//__DATA_REGIONS defined in the view

google.load('visualization', '1', {'packages': ['geochart']});
if (typeof __DATA_REGIONS != 'undefined') {
	//I've done it like this so we can call the method later on. Mostly for ajax
    google.setOnLoadCallback(drawRegionsMap);
}
    
        function drawRegionsMap() {
			var data = new google.visualization.DataTable();
			
			data.addRows(__DATA_REGIONS_COUNT);
			data.addColumn('string', 'Country');
			data.addColumn('number', 'Hits');
          
          	var  count  = 0; 
			for (region in __DATA_REGIONS) {
				var label	= region.substr(0, region.indexOf('-'));
				if ($.trim(label) == '') continue;
				data.setValue(count, 0, label);
				data.setValue(count, 1, __DATA_REGIONS[region]);
			    count++; 
        	} 
          
			var options = {'height' : 380};
			var container = document.getElementById('map_canvas');
			var geochart = new google.visualization.GeoChart(container);
			geochart.draw(data, options);
      };