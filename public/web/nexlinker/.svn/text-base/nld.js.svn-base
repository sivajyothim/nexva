/**
 * @deprecated
 * DO NOT CHANGE THIS FILE. This is the old version and is not in use. 
 * Use the nl.js file instead
 * @author John
 */
var _NXL_ADDED	= _NXL_ADDED || false ; //have we already loaded? 
var _NXL_INITED	= _NXL_INITED || false; //the DOM hook has already been called. don't call twice
var _NXL_LINKS	= [];

function nxlInit() {
	if (_NXL_INITED) return; //all done, no need to run again if event fires twice
	
	_NXL_INITED	= true;
	var _nx;
	while (_nx = _nxs.pop()) {
		nxlLoad(_nx);
	}

	Shadowbox.init({}, function(){
		var prolink;
		while (prolink = _NXL_LINKS.pop()) {
			prolink.href	= prolink.href + '/1'; //indicating it's an iframe request 
			prolink.style.display	= 'inline';
		}
	});
}

function nxlLoad(_nx) {
	
	var cont	= document.getElementById('nexlinker_badge_' + _nx.code);
	var prolink	= this.document.createElement("a");
	//using dom manip here because using object notation fails in IE for some attributes
	//prolink.style.display	= 'none';
	prolink.style.border	= 'none';
	prolink.target			= '_blank';
	prolink.style.textDecoration = 'none';
	prolink.setAttribute('rel', 'shadowbox;height=500;width=600'); //
	prolink.setAttribute('href', 'http://' + _nx.h + '/nld/' + _nx.code);
	//prolink.setAttribute('title', 'neXva.com - neXlinker - Share this app');
	_NXL_LINKS.push(prolink);
	
	var burl	= document.location.protocol + '//' + _nx.h + '/promotion-code/view/id/' + _nx.code;
	var badge	= document.createElement('img');
	badge.style.border	= 'none';
	badge.setAttribute('src', burl);
	
	prolink.appendChild(badge)
	cont.appendChild(prolink);

}

function nxlSB() {
	if (document.readyState == 'complete') { //damn script loads so fast that the event never fires
		nxlInit();
		return;
	}
	
	if (window.addEventListener) {
		document.addEventListener("DOMContentLoaded", nxlInit, false);
		window.addEventListener("load", nxlInit, false);
	} else if (window.attachEvent) {
		document.attachEvent("onreadystatechange",nxlInit);
		window.attachEvent("onload",nxlInit);
	} else {
		window.onload = function() {nxlInit();}
	}
}

(function(_nxs){
	if (_NXL_ADDED) {
		return;
	}
	_NXL_ADDED	= true;
	
	var _nx	= _nxs[0]; //just getting the first object to get host info
	
	var nc = document.createElement("link"); nc.rel = "stylesheet"; nc.id = 'nexlinker_css_link' 
	nc.href = ("https:" == document.location.protocol ? "https://www." : "http://") + _nx.h + "/web/nexlinker/shadowbox/shadowbox.css";
	var c = document.getElementsByTagName("script")[0]; c.parentNode.insertBefore(nc, c);
	var ns = document.createElement("script"); ns.type = "text/javascript"; ns.async = true;
	ns.src = ("https:" == document.location.protocol ? "https://www." : "http://") + _nx.h + "/web/nexlinker/shadowbox/shadowbox.js";
	var s = document.getElementsByTagName("script")[0]; s.parentNode.insertBefore(ns, s);
	
	if (ns.readyState){  //IE
		ns.onreadystatechange = function(){
            if (ns.readyState == "loaded" || ns.readyState == "complete"){
            	ns.onreadystatechange = null;
            	nxlSB();
            }
        };
    } else {  //Others
    	ns.onload = function(){
    		nxlSB();
        };
    }
})(_nxs);

function l(item) {
	try {
		console.log(item);
	} catch(e) {alert('no log : ' + item)}
}