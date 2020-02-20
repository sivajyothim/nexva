$(document).ready(function(){
		
	// first example

$("#navigation").treeview({
		animated: "slow",
		collapsed: true,
		control: "#treecontrol"
	});
$("#navigation1").treeview({
		animated: "slow",
		collapsed: false,
		control: "#treecontrol"
	});
	$("#nav").treeview({
		collapsed: true,
		unique: true,
		persist: "location"
	});
	// second example
	$("#browser").treeview({
		animated:"normal",
		persist: "cookie"
	});

	$("#samplebutton").click(function(){
		var branches = $("<li><span class='folder'>New Sublist</span><ul>" + 
			"<li><span class='file'>Item1</span></li>" + 
			"<li><span class='file'>Item2</span></li></ul></li>").appendTo("#browser");
		$("#browser").treeview({
			add: branches
		});
	});


	// third example
	


});