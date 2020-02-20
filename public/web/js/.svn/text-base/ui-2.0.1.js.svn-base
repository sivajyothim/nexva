// event loader
function addLoadEvent(func) {
  var oldonload = window.onload;
  if (typeof window.onload != 'function') {
    window.onload = func;
  } else {
    window.onload = function() {
      oldonload();
      func();
    }
  }
}

// jquery tooltip by Alen Grakalic (http://cssglobe.com/post/1695)
// changed a.tooltip to .tooltip to allow for abbr
this.tooltip = function(){  
  /* CONFIG */    
    xOffset = 10;
    yOffset = 20;    
    // these 2 variable determine popup's distance from the cursor
    // you might want to adjust to get the right result    
  /* END CONFIG */    
  $(".tooltip").hover(function(e){                        
    this.t = this.title;
    this.title = "";                    
    $("body").append("<p id='tooltip'>"+ this.t +"</p>");
    $("#tooltip")
      .css("top",(e.pageY - xOffset) + "px")
      .css("left",(e.pageX + yOffset) + "px")
      .fadeIn("fast");    
    },
  function(){
    this.title = this.t;    
    $("#tooltip").remove();
    });  
  $(".tooltip").mousemove(function(e){
    $("#tooltip")
      .css("top",(e.pageY - xOffset) + "px")
      .css("left",(e.pageX + yOffset) + "px");
  });      
};

// jquery show/hide for Page Updates
this.updates = function() {
  $('#updates').before('<p class="inline"> <a href="#" class="trigger">Page details…</a></p>');
  $('#updates').hide();
  $('#footer a.trigger').toggle (
    function () {
      $(this.parentNode.nextSibling).slideDown('fast');
      $(this).html('…Hide details');
    },
    function () {
      $(this.parentNode.nextSibling).slideUp('fast');
      $(this).html('Page details…');
    });
};

// load these
addLoadEvent(tooltip);
addLoadEvent(updates);