function appscoreID(htmlId, android_id,top,left) {

	var appId = android_id;
    if (appId != undefined && document.getElementById(appId) == null) {
	    var appInfo = new XMLHttpRequest();
	    appInfo.open("GET", "https://www.appbackr.com/XchangeApi/Appscore?id="+ appId, true);
	    appInfo.timeout = 4000;
	    appInfo.send();
	    appInfo.onreadystatechange = function() {
	      if (this.readyState == 4) {
	        var element = document.createElement("font");
	        element.innerHTML = "New";
	        element.setAttribute("size", 2);
	        element.setAttribute("style", "position: relative; left: -1px");
	         try {
	          var jsonRes = JSON.parse(this.responseText);
	          }
	          catch(e) {
	            document.getElementById(appId).appendChild(element);
	          }
	           if (jsonRes["success"] === true) {
	            document.getElementById(appId).appendChild(document.createTextNode(jsonRes["appscore"]));
	          } else {
	            document.getElementById(appId).appendChild(element);
	          }
	        } 
	    }
	}

	  var arrow = $('<img>');
      arrow.attr("src", 'images/score-arrow.png');
      arrow.css("position", "absolute");
      arrow.css("top","-0.30em");
      arrow.css("height",".85em");
      arrow.css("width",".85em");
      arrow.css("left", "1.65em");
      arrow.css("z-index", "801");
      var divs = $('<div></div>');
      divs.css('cssText',"background-color: #f38a44;  background-image: -moz-linear-gradient(top, #f49758, #f17627);  background-image: -webkit-gradient(linear, 0 0, 0 100%, from(#f49758), to(#f17627)); background-image: -webkit-linear-gradient(top, #f49758, #f17627);  background-image: -o-linear-gradient(top, #f49758, #f17627);  background-image: linear-gradient(to bottom, #f49758, #f17627);  background-repeat: repeat-x;  filter: progid:DXImageTransform.Microsoft.gradient(startColorstr=\'#fff49758\', endColorstr=\'#fff17627\', GradientType=0);  text-align: center;  font-family: Arial, Helvetica, sans-serif;  font-weight: bold;  color: #ffffff;  position: relative;padding: 5px; position: absolute; color: white; left: 0px; top: 0px ; float:right;  z-index: 800 ;  font-size: 16px;  display: inline-block;  vertical-align: middle; width: 34px;  height: 34px; border-radius: 0.500em;");
      divs.attr("id",android_id);
      divs.css("left", left +'px');
      divs.css("top", top +"px");
      divs.css("overflow", "visible");


      divs.click(function(){
            var pos = $(this);
            //console.log(pos.html().split('>'));
            if (pos.html().split('>')[2] != "New</font") {

            jQuery(document).ready(function($) {
      			    $.fancybox.open([
      			        {
                        //top href goes to production website, Bottom goes to localhost with 
      			            href : 'https://www.appbackr.com/chromeExtension/getAppInfo/'+ pos.attr('id'),
                        //href : 'https://localhost/chromeExtension/GetAppInfo/'+ pos.attr('id'),
      			            type : 'iframe',
      			        	  openEffect: 'none',
                				closeEffect: 'none',
      	          			iframe: {
      	              			preload: false, // fixes issue with iframe and IE
      	              			 scrolling : 'yes'
      	          			}
      			        }
      			          
      			    ], {
      			        padding : 0,
      			        width   : 500,
         					  height  : 500,
         					afterShow : function(event, ui) {
         					 $(".fancybox-wrap").css('cssText',"position: absolute !important; top:"+ (pos.offset().top + 50) + "px !important; left:"+ (pos.offset().left -233) + "px !important");
         					 $(pos).css("z-index", "8800");
                  },
                  afterClose : function(event, ui) {
                    $(pos).css("z-index", "800");
                  },
         					helpers: {
      					    overlay: {
      					      locked: false
      					    }
      					  }      
      			    });
      			});
          }

            return false;
        });

        divs.append(arrow);
        $("#"+htmlId).append(divs);

}

function appscoreClass(htmlId, android_id,top,left) {

  var appId = android_id;
    if (appId != undefined && document.getElementById(appId) == null) {
      var appInfo = new XMLHttpRequest();
      appInfo.open("GET", "https://www.appbackr.com/XchangeApi/Appscore?id="+ appId, true);
      appInfo.timeout = 4000;
      appInfo.send();
      appInfo.onreadystatechange = function() {
        if (this.readyState == 4) {
          var element = document.createElement("font");
          element.innerHTML = "New";
          element.setAttribute("size", 2);
          element.setAttribute("style", "position: relative; left: -1px");
           try {
            var jsonRes = JSON.parse(this.responseText);
            }
            catch(e) {
              document.getElementById(appId).appendChild(element);
            }
             if (jsonRes["success"] === true) {
              document.getElementById(appId).appendChild(document.createTextNode(jsonRes["appscore"]));
            } else {
              document.getElementById(appId).appendChild(element);
            }
          } 
      }
  }

    var arrow = $('<img>');
      arrow.attr("src", 'images/score-arrow.png');
      arrow.css("position", "absolute");
      arrow.css("top","-0.30em");
      arrow.css("height",".85em");
      arrow.css("width",".85em");
      arrow.css("left", "1.65em");
      arrow.css("z-index", "801");
      var divs = $('<div></div>');
      divs.css('cssText',"background-color: #f38a44;  background-image: -moz-linear-gradient(top, #f49758, #f17627);  background-image: -webkit-gradient(linear, 0 0, 0 100%, from(#f49758), to(#f17627)); background-image: -webkit-linear-gradient(top, #f49758, #f17627);  background-image: -o-linear-gradient(top, #f49758, #f17627);  background-image: linear-gradient(to bottom, #f49758, #f17627);  background-repeat: repeat-x;  filter: progid:DXImageTransform.Microsoft.gradient(startColorstr=\'#fff49758\', endColorstr=\'#fff17627\', GradientType=0);  text-align: center;  font-family: Arial, Helvetica, sans-serif;  font-weight: bold;  color: #ffffff;  position: relative;padding: 5px; position: absolute; color: white; left: 0px; top: 0px ; float:right;  z-index: 800 ;  font-size: 16px;  display: inline-block;  vertical-align: middle; width: 34px;  height: 34px; border-radius: 0.500em;");
      divs.attr("id",android_id);
      divs.css("left", left +'px');
      divs.css("top", top +"px");
      divs.css("overflow", "visible");


      divs.click(function(){
            var pos = $(this);
            //console.log(pos.html().split('>'));
            if (pos.html().split('>')[2] != "New</font") {

            jQuery(document).ready(function($) {
                $.fancybox.open([
                    {
                        //top href goes to production website, Bottom goes to localhost with 
                        href : 'https://www.appbackr.com/chromeExtension/getAppInfo/'+ pos.attr('id'),
                        //href : 'https://localhost/chromeExtension/GetAppInfo/'+ pos.attr('id'),
                        type : 'iframe',
                        openEffect: 'none',
                        closeEffect: 'none',
                        iframe: {
                            preload: false, // fixes issue with iframe and IE
                             scrolling : 'yes'
                        }
                    }
                      
                ], {
                    padding : 0,
                    width   : 500,
                    height  : 500,
                  afterShow : function(event, ui) {
                   $(".fancybox-wrap").css('cssText',"position: absolute !important; top:"+ (pos.offset().top + 50) + "px !important; left:"+ (pos.offset().left -233) + "px !important");
                   $(pos).css("z-index", "8800");
                  },
                  afterClose : function(event, ui) {
                    $(pos).css("z-index", "800");
                  },
                  helpers: {
                    overlay: {
                      locked: false
                    }
                  }      
                });
            });
          }

            return false;
        });

        divs.append(arrow);
        $("."+htmlId).append(divs);

}

function appscoreBody(android_id,top,left) {

  var appId = android_id;
    if (appId != undefined && document.getElementById(appId) == null) {
      var appInfo = new XMLHttpRequest();
      appInfo.open("GET", "https://www.appbackr.com/XchangeApi/Appscore?id="+ appId, true);
      appInfo.timeout = 4000;
      appInfo.send();
      appInfo.onreadystatechange = function() {
        if (this.readyState == 4) {
          var element = document.createElement("font");
          element.innerHTML = "New";
          element.setAttribute("size", 2);
          element.setAttribute("style", "position: relative; left: -1px");
           try {
            var jsonRes = JSON.parse(this.responseText);
            }
            catch(e) {
              document.getElementById(appId).appendChild(element);
            }
             if (jsonRes["success"] === true) {
              document.getElementById(appId).appendChild(document.createTextNode(jsonRes["appscore"]));
            } else {
              document.getElementById(appId).appendChild(element);
            }
          } 
      }
  }

    var arrow = $('<img>');
      arrow.attr("src", 'images/score-arrow.png');
      arrow.css("position", "absolute");
      arrow.css("top","-0.30em");
      arrow.css("height",".85em");
      arrow.css("width",".85em");
      arrow.css("left", "1.65em");
      arrow.css("z-index", "801");
      var divs = $('<div></div>');
      divs.css('cssText',"background-color: #f38a44;  background-image: -moz-linear-gradient(top, #f49758, #f17627);  background-image: -webkit-gradient(linear, 0 0, 0 100%, from(#f49758), to(#f17627)); background-image: -webkit-linear-gradient(top, #f49758, #f17627);  background-image: -o-linear-gradient(top, #f49758, #f17627);  background-image: linear-gradient(to bottom, #f49758, #f17627);  background-repeat: repeat-x;  filter: progid:DXImageTransform.Microsoft.gradient(startColorstr=\'#fff49758\', endColorstr=\'#fff17627\', GradientType=0);  text-align: center;  font-family: Arial, Helvetica, sans-serif;  font-weight: bold;  color: #ffffff;  position: relative;padding: 5px; position: absolute; color: white; left: 0px; top: 0px ; float:right;  z-index: 800 ;  font-size: 16px;  display: inline-block;  vertical-align: middle; width: 34px;  height: 34px; border-radius: 0.500em;");
      divs.attr("id",android_id);
      divs.css("left", left +'px');
      divs.css("top", top +"px");
      divs.css("overflow", "visible");


      divs.click(function(){
            var pos = $(this);
            //console.log(pos.html().split('>'));
            if (pos.html().split('>')[2] != "New</font") {

            jQuery(document).ready(function($) {
                $.fancybox.open([
                    {
                        //top href goes to production website, Bottom goes to localhost with 
                        href : 'https://www.appbackr.com/chromeExtension/getAppInfo/'+ pos.attr('id'),
                        //href : 'https://localhost/chromeExtension/GetAppInfo/'+ pos.attr('id'),
                        type : 'iframe',
                        openEffect: 'none',
                        closeEffect: 'none',
                        iframe: {
                            preload: false, // fixes issue with iframe and IE
                             scrolling : 'yes'
                        }
                    }
                      
                ], {
                    padding : 0,
                    width   : 500,
                    height  : 500,
                  afterShow : function(event, ui) {
                   $(".fancybox-wrap").css('cssText',"position: absolute !important; top:"+ (pos.offset().top + 50) + "px !important; left:"+ (pos.offset().left -233) + "px !important");
                   $(pos).css("z-index", "8800");
                  },
                  afterClose : function(event, ui) {
                    $(pos).css("z-index", "800");
                  },
                  helpers: {
                    overlay: {
                      locked: false
                    }
                  }      
                });
            });
          }

            return false;
        });

        divs.append(arrow);
        $("body").append(divs);

}

