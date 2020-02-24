/*
	author: shehal joseph (shehal@gmail.com)
	date: 01 april 2010
	version: 2.0
	project: neXva.com
*/

/* UI Settings */
var app='app.php'; 		// neXva application interface
var nRowsHome=4;		// number of rows in the home page product listings
var nRowsList=1;		// number of rows in the list page product listings
var paddingHome=3; 	// padding in px for home page product listings
var paddingList=15;	// padding in px for home page product listings
var tOut=0;					// timeout

/* Functions */
$(document).ready(function() {
	
    // initiate the ketch-up validations
    //$("#q").keyup(searchSg);
    //$('#signup').ketchup();
	

    $('#searchSg').mouseleave(searchSgHide);
    $('#searchSg a').mousedown (searchSgR);
    $('.nextPage').click (scrollProdListUp);
    $('.prevPage').click (scrollProdListDown);
    $('.featApp').click (loadFeatApp);
    $('#langSelect li a').click(selectLang);
    $('#curSelect li a').click(selectCur);
    $('#usr_phone').click(selectUserPhone);
    $("#q_phone").keyup(searchSgPhone);
    $("#q_phone").keydown(searchSgPhone);
    $("#searchBtnPhone").click(searchSgPhone);
    // submit on user register form
    $("#submit").click(function(){
            $(this).parents('form').submit();
    });
    // reset button on register form
    $("#reset").click(function(){
        $(this).parents('form').get(0).reset();
    });
    //user login submit
    // submit on user register form
    $("#usr_login").click(function(){
        $(this).parents('form').submit();
    });
    
    $("#submit").click(function(){
          $(this).parents('form').submit();
    });
    
    // non claim user registration 
    $("#submit_unclaim").click(function(){
//      systemMsg('Register Error', 'Error Found in User Register', true);
          $(this).parents('form').submit();
  });
    
		$("#closeNotice").click(closeNotices);		
		$(".prodView .description table tr td").click(function() { expCompatibleDevice($(this)); });
		colCompatibleDevices();		
		$(".reviews .review").click(function() { expReview($(this)); });
		colReviews();
		
	    $('#search').submit(function() {
	        
	      $('#search').attr('action', project_base_path + 'search/index/q/'+$('#q').val());
	      return true;
	    });
		
		
});

function showNoDeviceSelectedNotice() {
	$('#selDeviceCont').show().click(function(){$(this).fadeOut()});
	setTimeout(function(){blinkItem('#selDeviceCont')}, 1000);
	setTimeout(function(){$('#selDeviceCont').fadeOut()}, 6000);
}

function blinkItem(selector) {
	$(selector)
    .animate({marginTop: '-=5px'}, 100)
       .animate({marginTop: '+=5px'}, 100)
       .animate({marginTop: '-=5px'}, 100)
       .animate({marginTop: '+=5px'}, 100);
}

function closeNotices () {
	$('#notices').fadeOut(500, function () {
		$('#notices').css('visibility', 'hidden');
		$('#notices').css('display', 'none');
	});
}

function colCompatibleDevices () {
	var phones = '';
	var textPh = '';
	var htmlPh = '';
	var charPerLine = 70;
	var d = $('.prodView .description table tr');
	if (d) {
    $.each(d, function(index){
        phones = ($(this).find('td').html());
				if (phones.length > charPerLine) {
					textPh = phones.substring(0,charPerLine-15)+"&nbsp;<a href='#comment_"+index+"' class='more'>more...</a>";
					htmlPh = '<div class="visible">' + textPh + '</div>';
					htmlPh = htmlPh + '<div class="hidden"><a name="comment_'+index+'"></a>' + phones + '</div>';
					$(this).find('td').html(htmlPh);
				}
    });
	}
}

function expCompatibleDevice (cellObj) {
	var visibleObj = cellObj.find('.visible');
	var hiddenTxt = cellObj.find('.hidden').text();
	visibleObj.text(hiddenTxt);
	setBackground ();
}

function colReviews () {
	var comments = '';
	var textCom = '';
	var htmlCom = '';
	var charPerLine = 200;
	var d = $('.reviews');
	if (d) {
    $.each(d, function(index){
        comments = $(this).find('.comments').html();
				if (comments && comments.length > charPerLine) {
					textCom = comments.substring(0,charPerLine-15)+"&nbsp;<a href='#review_"+index+"' class='more'>more...</a>";
					htmlCom = '<div class="visible">' + textCom + '</div>';
					htmlCom = htmlCom + '<div class="hidden"><a name="review_'+index+'"></a>' + comments + '</div>';
					$(this).find('.comments').html(htmlCom);
				}
    });
	}
}

function expReview (cellObj) {
	var visibleObj = cellObj.find('.visible');
	var hiddenTxt = cellObj.find('.hidden').html();
	visibleObj.html(hiddenTxt);
	setBackground ();
}

/* function loadPhoneSelectTabs () {
	var m='getUserPhones';
	var sid=$.cookie('sID');
	var h='';
	var q='';
	$.ajax({ url: app, dataType: 'json', data: 'm='+m+'&sid='+sid, async: false, success: function (data) {
		$.each(data.breadcrumbs, function(i,bc){
			h+='<li><a href="javascript:loadProdList(\''+bc.pgid+'\', \'prod_list\');">'+bc.title+'</a></li>';
		});
		$('#breadcrumbs').html(h);
		loadProdList (data.cat, q, 'prodListPage');
	} });
	
	var o='';
	var h=$('#usrPhones .usrPhoneBlk').height();
	
	$.each(data.phones, function(i,phone){
		if (phone.id!='null_phone') {
			p_id=phone.id;
			p_ph=phone.phone;
						
			o += '<li><a href="javascript:removeUserPhone(\''+p_id+'\');"></a><a class="phoneInfo"><img src="'+ p_img +'" />'+ p_ph +'</a></li>';
			
			$('#usrPhones').height(h*(i/2));
		}
	});
	$('#usrPhones').html(o);	
	$("#usr_phone img").attr('src','images/add_phone.png');
	
} */

function loadPage (pgid, page) {
    $.cookie('pgID', page, {
        expires: 7
    });
    document.location=page+'.html';
}

function initiatePage () {																													
    var m='getPageTitle';
    var sid=$.cookie('sID');
    var pgid=$.cookie('pgID');
    var h='';
    var q='';
    $.ajax({
        url: app,
        dataType: 'json',
        data: 'm='+m+'&pgid='+pgid+'&sid='+sid,
        async: false,
        success: function (data) {
            $('#pageTitle').html(data.pageTitle);
            $.each(data.breadcrumbs, function(i,bc){
                h+='<li><a href="javascript:loadPage(\''+bc.pgid+'\', \'prod_list\');">'+bc.title+'</a></li>';
            });
            $('#breadcrumbs').html(h);
            loadProdList (data.cat, q, 'prodListPage');
        }
    });
}

function alignPage () {
	return; //done through css now so removing js 
    var pw=$("#page").width();
    var dw=$(document).width();
    var lm=(dw-pw)/2;
    $('#page').css("margin-left",lm);
    $('#search').css("left",lm+711);
}

function updateUserPhones (data) {
    var o='';
    var h=$('#usrPhones .usrPhoneBlk').height();
	
    $.each(data.phones, function(i,phone){
        if (phone.id=='null_phone') {
            $("#usr_phone img").attr('src',phone.img);
            $("#usr_phone_str img").text(phone.phone);
        } else {
            p_id=phone.id;
            p_img=phone.img;
            p_ph=phone.phone;
						
            o += '<li class="usrPhoneBlk"><a class="closeBtn" href="javascript:removeUserPhone(\''+p_id+'\');"></a><a class="phoneInfo"><img src="'+ p_img +'" />'+ p_ph +'</a></li>';
			
            $('#usrPhones').height(h*(i/2));
        }
    });
    $('#usrPhones').html(o);
    $("#usr_phone img").attr('src','images/add_phone.png');
/* 
	s_title=data.systemMsg.title;
	s_msg=data.systemMsg.msg;
	if (s_title !='') { systemMsg (s_title, s_msg, true); } 
	*/
}
function viewQRCode (wTitle,imgSrc){
    $('#systemMsg').dialog('open');
    wWidth=261;
    wHeight=100;
    wX=350;
    wY=100;
    viewImage (wTitle, imgSrc, wWidth, wHeight, wX, wY);
}
function viewDownload (wTitle,wID){
	var pw=$("#page").width();
	$('#systemMsg').dialog('open');
	wWidth=500;
	wX=(pw-wWidth)/2;
	wY=100;
	eval('wHtmlObj="#'+wID+'"');
	wHtml=$(wHtmlObj).html();
	viewHtml (wTitle, wHtml, wWidth, wX, wY);
}
function viewHtml (wTitle, wHtml, wWidth, wX, wY) {
	var pw=$("#page").width();
	var dw=$(document).width();
	var lm=wX + ((dw-pw)/2);
	$('#systemMsg').html(wHtml);
	$('#systemMsg').css("visibility","visible");
	$('#systemMsg').dialog({
			autoOpen: false,
			title: wTitle,
			show: 'blind',
			hide: 'blind',
			resizable: false,
			width: wWidth,
			modal:true,
			position: [lm,wY]
	});
	$('#systemMsg').dialog('open');
}
function viewScreenShot (imgSrc){ 
    $('#prod_poster').effect('fadeOut',{},300, function (){
        $('#prod_poster').attr('src', imgSrc);
        $("#prod_poster").removeAttr('style').hide().fadeIn();
    });
}
function viewImage (wTitle, imgSrc, wWidth, wHeight, wX, wY) {
    var pw=$("#page").width();
    var dw=$(document).width();
    var lm=wX + ((dw-pw)/2);
    $('#systemMsg').html('<div align="center"><img src="'+imgSrc+'" /></div>');
    $('#systemMsg').css("visibility","visible");
    $('#systemMsg').dialog({
        autoOpen: false,
        title: wTitle,
        show: 'blind',
        hide: 'blind',
        resizable: false,
        width: wWidth,
        height: wHeight,
        modal:false,
        position: [lm,wY]
    });
    $('#systemMsg').dialog('open');
}
function hideOverlays () {
    var v='visibility';
    var u='hidden';
    $('#phoneSelect').css(v,u);
    $('#systemMsg').css(v,u);
}
function selectUserPhone (){
    var pw=$("#page").width();
    var dw=$(document).width();
    var lm=20 + ((dw-pw)/2);
    $('#phoneSelect').css('visibility', 'visible');
/*    $('#phoneSelect').dialog({
        autoOpen: false,
        title:'Select your device',
        show: 'slide',
        hide: 'slide',
        resizable: false,
        width: 261,
        position: [lm,160]
    });*/
    
    var dialogWidth	= 420;
    $('#phoneSelect').dialog({
        autoOpen: false,
        title:'Select your device',
        resizable: false,
        width: dialogWidth,
        modal: true,
        draggable : false,
        buttons: {
			"I'll select a device later" : function() {
				$( this ).dialog( "close" );
				
			}
		},
		close : function(event, u) {
					$('html, body').animate({scrollTop:0}, 'slow', 
						function(){
							$('#selDeviceCont').show().click(function(){$(this).fadeOut()});
							setTimeout(function(){$('#selDeviceCont').fadeOut()}, 10000);
							blinkItem('#selDeviceCont');
						}
					);
				},
		position: [(dw / 2) - (dialogWidth / 2), 160]
    });
    $('#phoneSelect').dialog('open');
    if ($('#phoneSelect').dialog('isOpen')==true) {
        $('#q_phone').focus();
    }
    return false;
}
function searchSgPhoneHide (e) {
    $('#searchSgPhone').css("visibility","hidden");
}
function removeUserPhone (pid) {
    document.location = '/device/remove/id/'+pid;
}
function setUserPhone (pid) {
    document.location =  '/device/set/id/'+pid;
}

function searchSgPhone (e) {
	clearTimeout(tOut);
	tOut = setTimeout('getPhones()', 600);
	if (e.which == 13) {return false;} //grabbing enter
}

function getPhones () {

	$('#seach_loading').removeClass('display_none');
	var q = $("#q_phone").val();
	var m = 'searchSgPhone'; 
	var urlSg = '/device/search/q/'+q;
	$.ajax({ url: urlSg, dataType: 'json', async: true, success: function (phones) {
		var o=''; 
		$('#searchSgPhone').html(o);
		$('body').data ('searchSgPhoneList', phones);
		var i=0;
		$.each(phones, function(i,result){
			s_id  	= result.id;
			s_name	= result.phone;
			s_img		= result.img;
			
			if (s_id=='error') { 
				o='<div class="searchSgPhoneResult">'+ s_name +'</div>'; 
			} else {
				o += '<div class="searchSgPhoneResult"><a href="javascript:setUserPhone(\''+s_id+'\');">';
				o += '<img src="'+ s_img +'" align="left" />'+ s_name +'</a></div>';
			}
		});
		$('#seach_loading').addClass('display_none');
		$('#searchSgPhone').html(o);
		$('#searchSgPhone').css("visibility","visible");
		
	} });
}

function systemMsg (msgTitle, msgBody, modalStatus) {
    $('#systemMsg').html('');
    $('#systemMsg').html('<p>'+msgBody+'</p>');
    $('#systemMsg').css("visibility","visible");
    $('#systemMsg').dialog({
        autoOpen: false,
        title:msgTitle,
        modal:modalStatus,
        show: 'blind',
        hide: 'blind',
        resizable: false
    });
    $('#systemMsg').dialog('open');
}

function userLogin () {
    var m='userLogin';
    var sid=$.cookie('sID');
    var str=$("#userForm").serialize() + '&m='+m + '&sid='+sid;
    $.ajax({
        url: 'user/login',
        dataType: 'POST',
        data: str,
        async: false,
        success: function (response) {
            document.userForm.reset();
            var obj=jQuery.parseJSON(response);
            if (obj.status == 'ok') {
                $('#usrGreeting').html(obj.userGreeting);
                $('#userForm').css('visibility', 'hidden');
                $('#userForm').css('display', 'none');
                $.cookie('sID', obj.sID, {
                    expires: 7
                });
            } else {
                systemMsg (obj.systemMsg.title, obj.systemMsg.msg, true);
            }
        }
    });
}

function selectCur () {
    var m='setUserCurrency';
    var sid=$.cookie('sID');
    var cur=$(this).attr('rel');
    var aObj=$(this);
    var x=$('#curSelect li');
	
    x.map(function () {
        $(this).find('a').css('font-weight', 'normal');
    });
    $.ajax({
        url: app,
        dataType: 'json',
        data: 'm='+m+'&sid='+sid+'&cur='+cur,
        async: false,
        success: function (data) {
            aObj.css('font-weight', 'bold');
            systemMsg (data.systemMsg.title, data.systemMsg.msg, true);
        }
    });
}

function selectLang () {
    var m='setUserLanguage';
    var sid=$.cookie('sID');
    var lang=$(this).attr('rel');
    var aObj=$(this);
    var x=$('#langSelect li');
	
    x.map(function () {
        $(this).find('a').css('font-weight', 'normal');
    });
    $.ajax({
        url: app,
        dataType: 'json',
        data: 'm='+m+'&sid='+sid+'&lang='+lang,
        async: false,
        success: function (data) {
            aObj.css('font-weight', 'bold');
            systemMsg (data.systemMsg.title, data.systemMsg.msg, true);
        }
    });
}

function getUserGreeting (sid) {
    var m='getUserGreeting';
    $.ajax({
        url: app,
        dataType: 'json',
        data: 'm='+m+'&sid='+sid,
        async: false,
        success: function (data) {
            $('#usrGreeting').html(data.msg);
        }
    });
}

function getUserPhones (sid) {
    var m='getUserPhones';
    $.ajax({
        url: app,
        dataType: 'json',
        data: 'm='+m+'&sid='+sid,
        async: false,
        success: function (data) {
            updateUserPhones(data);
        }
    });
}

function startSession () {
    var sid='';
    var m='startSession';
    if ($.cookie('sID') != '') {
        sid=$.cookie('sID');
    }
    $.ajax({
        url: app,
        dataType: 'json',
        data: 'm='+m+'&sid='+sid,
        async: false,
        success: function (data) {
            $.cookie('sID', data.sID, {
                expires: 7
            });
            getUserGreeting (sid);
            getUserPhones (sid);
        }
    });
}

function loadMenuList (block) {
    var m='getMenuList';
    var h='';
    var menu=new Array();
    ;
    var mid='';
    var sid=$.cookie('sID');
	
    if (block=='left') {
        var p=$('.leftBlock');
    }
    else if (block=='right') {
        var p=$('.rightBlock');
    }
	
    $.ajax({
        url: app,
        dataType: 'json',
        data: 'm='+m+'&sid='+sid+'&blk='+block,
        async: false,
        success: function (data) {
            $.each(data, function(i,mdata){
                blockStub=p.clone();
                menu[i]=mdata.id;
                menuTable=blockStub.find(".block");
                menuTable.attr('id', block+'_menuListBlk_'+menu[i]);
                menuTitle=mdata.title.replace('neXva', '<img src="images/nexva_logo_small.png" class="neXvaLogo" />');
                menuTable.find("h2").html(menuTitle);
			
                h+=blockStub.html();
            });
            p.html(h);
            setBackground ();
		
            for (j=0;j<menu.length;j++) {
                loadMenu (menu[j], block);
            }
        }
    });
}

function loadMenu (menu, block) {
    var m='getMenu';
    var h='';
    var mid='';
    var i=0;
    var done=false;
	
    if (block=='left') {
        var menuBlk=$('.leftBlock');
    }
    else if (block=='right') {
        var menuBlk=$('.rightBlock');
    }
	
    eval('mid="#'+block+'_menuListBlk_'+menu+'"');
    menuTable=menuBlk.find(mid);
	
    $.ajax({
        url: app,
        dataType: 'json',
        data: 'm='+m+'&mid='+menu,
        async: false,
        success: function (data) {
            $.each(data, function(i,mdata){
                h+='<a class="menuLink" href="javascript:loadPage(\''+mdata.id+'\', \'prod_list\');" id="menu_item_'+mdata.id+'">'+mdata.title+'</a>';
            });
            menuTable.find('.blockContent').html(h);
            setBackground ();
            done=true;
        }
    });
}

function loadCatList () {
    var m='getCatList';
    var p=$('.prodListBlk');
    var h='';
    var cat=new Array();
    ;
    var cid='';
    var q='';
	
    $.ajax({
        url: app,
        dataType: 'json',
        data: 'm='+m,
        async: false,
        success: function (data) {
            $.each(data, function(i,app){
                prodListBlkStubCopy=p.clone();
                cat[i]=app.id;
                catListTable=prodListBlkStubCopy.find(".container");
                catListTable.attr('id', 'prodListBlk_'+cat[i]);
                blockHead=prodListBlkStubCopy.find(".blkHead");
                blockHead.map(function () {
                    q=$(this).find('h2');
                    q.text(app.name);
                    q=$(this).find('.moreapps');
                    q.attr('href', '#moreapps_'	+app.id);
                });
                h+=prodListBlkStubCopy.html();
            });
            p.html(h);
            setBackground ();
		
            for (j=0;j<cat.length;j++) {
                loadProdList (cat[j], q, 'prodListBlk');
            }
        }
    });
}

function setBackground () {
	return; // issue fixed through CSS - JP
    var w=$(document).width() - 20;
    var h=$(document).height() - $("#searchSg").height() - $("#systemMsg").height() - $("#phoneSelect").height() + 40;
	
    $("#background").height(h);
    if ($.browser.msie) {
        $("#background").height(h-4);
    } else {
        $("#background").height(h);
    }
}

function loadProdList (cat, q, block) {
    var m='getAppList';
    var h='';
    var cid='';
    var n=0;
    var done=false;
	
    if (block=='prodListBlk') {
        eval('cid="#prodListBlk_'+cat+'"');
        prodListBlk=$('.prodListBlk');
        prodListCat=prodListBlk.find(cid);
        blockList=prodListCat.find(".blkList");
        blockList.map(function () {
            p=$(this).find('.prodListItem');
        });
    }
    else if (block=='prodListPage') {
        prodListBlk=$('prodListBlk');
        p=$('.prodListPage').find('.prodListItem');
    }

    $.ajax({
        url: app,
        dataType: 'json',
        data: 'm='+m+'&cat='+cat+'&q='+q,
        async: false,
        success: function (data) {
            $.each(data, function(i,app){
                pCopy=p.clone();
			
                specList=pCopy.find(".speclist div");
                specList.map(function () {
                    fld='price';
                    displayAppListItem ($(this),fld,app);
                    fld='size';
                    displayAppListItem ($(this),fld,app);
                    fld='platform';
                    displayAppListItem ($(this),fld,app);
                    fld='downloads';
                    displayAppListItem ($(this),fld,app);
				
                    stars=ratingStars(app.rating);
                    f=$(this).find('.prod_rating');
                    t=f.text();
                    if (t.length > 0) {
                        f.html(stars);
                    }
                });
			
                blockHead=pCopy.find(".blkHead");
                blockHead.map(function () {
                    q=$(this).find('img');
                    q.attr('src', app.poster);
                    q=$(this).find('h3');
                    q.text(app.name+app.name);
                    q=$(this).find('p');
                    q.text(app.desc+app.desc);
                });
			
                btnLinks=pCopy.find(".btnLinks");
                btnLinks.map(function () {
                    q=$(this).find('.moreinfo');
                    q.attr('href', '#moreinfo_'	+app.id);
                    q=$(this).find('.download');
                    q.attr('href', '#download_'	+app.id);
                    q=$(this).find('.buy');
                    q.attr('href', '#buy_'		+app.id);
                });
			
                h+=pCopy.html();
                n++;
            });
            p.html(h);
            setBlock(p,n,block);

            setBackground ();
            done=true;
        }
    });
}

function setBlock(blkObj,n,block) {
    var hblk=blkObj.find('.block').height();
    var itemsPerRow=1;
    var padding=15;
	
    if (block=='prodListBlk') {
        itemsPerRow=nRowsHome;
        padding=paddingHome;
    }
    else if (block=='prodListPage') {
        itemsPerRow=nRowsList;
        padding=paddingList;

        var a=p.find('.blkHead').height();
        var b=p.find('.prodListSpecsheet').height();
		
        if (a>b) {
            h=a;
        } else {
            h=b;
        }
        p.find('.blkContent').height(h);
    }
	
    var rows=Math.ceil(n/itemsPerRow);
    h=hblk * rows;
    blkObj.height(h+padding);
}

function displayAppListItem (d,fld,data) {
    o='.prod_'+fld;
    eval("m=data."+fld);
    f=d.find(o);
    t=f.text();
    if (t.length > 0) {
        f.text(m)
    };
}
function loadFeatAppList () {
    var m='getFeatAppList';
    var o='';
    $.ajax({
        url: '/featured',
        dataType: 'json',
        data: '',
        async: true,
        success: function (data) {
            $.each(data, function(i,app){
                a_id  = app.id;
              
                a_val = app.name;
                if (i==0) {
                    loadFeatApp(app.id);
                }
                o += '<a class="featApp" id="prod_list_'+i+'" rel="'+a_id+'" href="javascript:loadFeatApp(\''+a_id+'\');">'+ a_val +'</a>';
            });
            $('.items').html(o);
        }
    });
    

}

function loadFeatApp (a) {
    $.getJSON('/featured/'+a, displayFeatApp);
}

function displayFeatApp (data) {
    var e1='slide';
    var e2='slide';
	
    var o={};
    var u=2000;
    fld='title';
    slen= 25;
    displayFeatAppFld (data,fld,slen);
    v=u * (Math.random()/10);
    c="#prod_"+fld;
    $(c).effect(e1,o,v);
    fld='desc';
    slen=300;
    displayFeatAppFld (data,fld,slen);
    v=u * (Math.random()/10);
    c="#prod_"+fld;
    $(c).effect(e1,o,v);
    fld='size';
    slen=  0;
    displayFeatAppFld (data,fld,slen);
    v=u * (Math.random()/10);
    c="#prod_"+fld;
    $(c).effect(e2,o,v);
    fld='price';
    slen=  0;
    displayFeatAppFld (data,fld,slen);
    v=u * (Math.random()/10);
    c="#prod_"+fld;
    $(c).effect(e2,o,v);
    fld='platform';
    slen=  0;
    displayFeatAppFld (data,fld,slen);
    v=u * (Math.random()/10);
    c="#prod_"+fld;
    $(c).effect(e2,o,v);
    fld='vendor';
    slen=  0;
    displayFeatAppFld (data,fld,slen);
    v=u * (Math.random()/10);
    c="#prod_"+fld;
    $(c).effect(e2,o,v);
    fld='downloads';
    slen=  0;
    displayFeatAppFld (data,fld,slen);
    v=u * (Math.random()/10);
    c="#prod_"+fld;
    $(c).effect(e2,o,v);
		
    $('#prod_poster').attr('src', data.poster);
    $('#prod_more').attr('href', '/app/featured.'+ data.id + ".en");
    $('#prod_poster').effect(e1,o,v);
    $('#prod_vendorlink').attr('href', '/cp/'+ data.linkname + "." + data.userid+ ".en");
 
	
    o=ratingStars(data.rating);
    $('#prod_rating').html (o);
}

function ratingStars (r) {
    var o='';
    for (i=0; i<r; i++) {
        o += '<img src="/web/images/star.gif" alt="star" width="11" height="12" />';
    }
    return o;
}

function displayFeatAppFld (d,fld,slen) {
    c="#prod_"+fld;
    eval("o=d."+fld);
    if (slen > 0) {
        t=o.substring(0,slen);
    } else {
        t=o;
    }
    $(c).html(t);
}

function scrollProdListUp () {
    var x=$('.items').position();
    var y=0;
    var v=600;
    if ((x.top) > -180) {
        y=-27;
    }
    $('.items').animate({
        "top": "+="+y+"px"
    }, {
        queue:false
    }, v);
}

function scrollProdListDown () {
    var x = $('.items').position();
    var y=0;
    var v=600;
    if ((x.top) < 0) {
        y=27;
    }
    $('.items').animate({
        "top": "+="+y+"px"
    }, {
        queue:false
    }, v);
}

function searchSgHide () {
    $('#searchSg').css("visibility","hidden");
}

function searchSgR(id) {
    var d = $('body').data('searchSgList');
    $.each(d, function(i,result){
        s_id  = result.id;
        s_val = result.val;
        if (id == s_id) {
            $("#q").val(s_val);
        }
    });
}

function searchSg () {
	
    var q = $("#q").val().trim();
    var m = 'searchSg';
    if (q.length > 2) {
        $.getJSON('/search/suggest/q/' + q, {}, searchSgList);
    }
}

function searchSgList(d) {
    var o='';
    var pw=$("#page").width();
    var dw=$(document).width();
    var l=718+((dw-pw)/2);
	
    $('body').data ('searchSgList', d);
    $.each(d, function(i,result){
        s_id  = result.id;
        s_val = result.val;
        o += '<a href="javascript:searchSgR(\''+s_id+'\');">'+ s_val +'</a>';
    });
    $('#searchSg').html(o);
    $('#searchSg').css("display","block");
    $('#searchSg').css("left",l);
}

function print_r(theObj){
    if(theObj.constructor == Array || theObj.constructor == Object){
        document.write("<ul>")
        for(var p in theObj) {
            if(theObj[p].constructor == Array|| theObj[p].constructor == Object){
                document.write("<li>["+p+"] => "+typeof(theObj)+"</li>");
                document.write("<ul>")
                print_r(theObj[p]);
                document.write("</ul>")
            } else {
                document.write("<li>["+p+"] => "+theObj[p]+"</li>");
            }
        }
        document.write("</ul>")
    }
}

/**
 * Cookie plugin
 *
 * Copyright (c) 2006 Klaus Hartl (stilbuero.de)
 * Dual licensed under the MIT and GPL licenses:
 * http://www.opensource.org/licenses/mit-license.php
 * http://www.gnu.org/licenses/gpl.html
 *
 */

/**
 * Create a cookie with the given name and value and other optional parameters.
 *
 * @example $.cookie('the_cookie', 'the_value');
 * @desc Set the value of a cookie.
 * @example $.cookie('the_cookie', 'the_value', { expires: 7, path: '/', domain: 'jquery.com', secure: true });
 * @desc Create a cookie with all available options.
 * @example $.cookie('the_cookie', 'the_value');
 * @desc Create a session cookie.
 * @example $.cookie('the_cookie', null);
 * @desc Delete a cookie by passing null as value. Keep in mind that you have to use the same path and domain
 *       used when the cookie was set.
 *
 * @param String name The name of the cookie.
 * @param String value The value of the cookie.
 * @param Object options An object literal containing key/value pairs to provide optional cookie attributes.
 * @option Number|Date expires Either an integer specifying the expiration date from now on in days or a Date object.
 *                             If a negative value is specified (e.g. a date in the past), the cookie will be deleted.
 *                             If set to null or omitted, the cookie will be a session cookie and will not be retained
 *                             when the the browser exits.
 * @option String path The value of the path atribute of the cookie (default: path of page that created the cookie).
 * @option String domain The value of the domain attribute of the cookie (default: domain of page that created the cookie).
 * @option Boolean secure If true, the secure attribute of the cookie will be set and the cookie transmission will
 *                        require a secure protocol (like HTTPS).
 * @type undefined
 *
 * @name $.cookie
 * @cat Plugins/Cookie
 * @author Klaus Hartl/klaus.hartl@stilbuero.de
 */

/**
 * Get the value of a cookie with the given name.
 *
 * @example $.cookie('the_cookie');
 * @desc Get the value of a cookie.
 *
 * @param String name The name of the cookie.
 * @return The value of the cookie.
 * @type String
 *
 * @name $.cookie
 * @cat Plugins/Cookie
 * @author Klaus Hartl/klaus.hartl@stilbuero.de
 */
jQuery.cookie = function(name, value, options) {
    if (typeof value != 'undefined') { // name and value given, set cookie
        options = options || {};
        if (value === null) {
            value = '';
            options.expires = -1;
        }
        var expires = '';
        if (options.expires && (typeof options.expires == 'number' || options.expires.toUTCString)) {
            var date;
            if (typeof options.expires == 'number') {
                date = new Date();
                date.setTime(date.getTime() + (options.expires * 24 * 60 * 60 * 1000));
            } else {
                date = options.expires;
            }
            expires = '; expires=' + date.toUTCString(); // use expires attribute, max-age is not supported by IE
        }
        // CAUTION: Needed to parenthesize options.path and options.domain
        // in the following expressions, otherwise they evaluate to undefined
        // in the packed version for some reason...
        var path = options.path ? '; path=' + (options.path) : '';
        var domain = options.domain ? '; domain=' + (options.domain) : '';
        var secure = options.secure ? '; secure' : '';
        document.cookie = [name, '=', encodeURIComponent(value), expires, path, domain, secure].join('');
    } else { // only name given, get cookie
        var cookieValue = null;
        if (document.cookie && document.cookie != '') {
            var cookies = document.cookie.split(';');
            for (var i = 0; i < cookies.length; i++) {
                var cookie = jQuery.trim(cookies[i]);
                // Does this cookie string begin with the name we want?
                if (cookie.substring(0, name.length + 1) == (name + '=')) {
                    cookieValue = decodeURIComponent(cookie.substring(name.length + 1));
                    break;
                }
            }
        }
        return cookieValue;
    }
};
