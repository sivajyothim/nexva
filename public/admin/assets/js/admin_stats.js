$(document).ready(function(){
    var img = "<img src='/admin/assets/img/global/loading_gray.gif' style='margin-top:0px;' />";
    var text;
    var obje;


    var objDeviceArray = new Array();
    var objFreeContentArray = new Array();
    var  objPreeContentArray = new Array();
    var  objActualDownloadsArray = new Array();
    
    objDeviceArray[0]   = $('#dayselecter1 div:nth-child(1)');
    objDeviceArray[1]   = $('#dayselecter1 div:nth-child(2)');
    objDeviceArray[2]   = $('#dayselecter1 div:nth-child(3)');
    objDeviceArray[3]   = $('#dayselecter1 div:nth-child(4)');

    objFreeContentArray[0]   = $('#dayselecter2 div:nth-child(1)');
    objFreeContentArray[1]   = $('#dayselecter2 div:nth-child(2)');
    objFreeContentArray[2]   = $('#dayselecter2 div:nth-child(3)');
    objFreeContentArray[3]   = $('#dayselecter2 div:nth-child(4)');

    objPreeContentArray[4]   = $('#dayselecter3 div:nth-child(1)');
    objPreeContentArray[5]   = $('#dayselecter3 div:nth-child(2)');
    objPreeContentArray[6]   = $('#dayselecter3 div:nth-child(3)');
    objPreeContentArray[7]   = $('#dayselecter3 div:nth-child(4)');

    objActualDownloadsArray[8]   = $('#dayselecter4 div:nth-child(1)');
    objActualDownloadsArray[9]   = $('#dayselecter4 div:nth-child(2)');
    objActualDownloadsArray[10]  = $('#dayselecter4 div:nth-child(3)');
    objActualDownloadsArray[11]  = $('#dayselecter4 div:nth-child(4)');

    stats(objDeviceArray,'device');
    stats(objFreeContentArray,'freeContent');
    stats(objPreeContentArray,'premiumContent');
    stats(objActualDownloadsArray,'actualdownloads');
    // stats(month);
    // stats(year);


    function stats(objArray,type){
                
        for(i=0;i<objArray.length;i++){

               
            $(objArray[i]).click(function(){
                
                objname = $(this).text().toLowerCase();
                obje = $(this);
                text = $(this).html();
                $(this).html(img);
                
                getStats(objname,type,$(this));
                $(this).parent().children().css('background-color','#E5E5E5');
                
            });
        }
        
    }
    
  

     function getStats(periodtime,statsType){
     
                
               
        $.getJSON(
            '/statistic/fetchstats',
            {
                period:periodtime,
                type:statsType
            },
            parse_stats
            );

       
    }


    function parse_stats(data, status){
        obje.html(text);
        $(obje).css('background-color','#808080');
       
        var html = '';

        for (var key in data) {
            html +="<tr><td style='width:85%' class='alt'>"+data[key].name+"</td>"+"<td class='alt'>"+data[key].hits+"</td><td><a href='/statistic/graph/period/year/device/"+data[key].device_id+"' title='View graph' alt='View graph'><img src='/admin/assets/img/global/iconGraphGreen.gif' /></a></td></tr>";

 
        }
        if(data.length<=0){
            html ="<i> No data available</i>";
        }
        $(obje).parent().parent().parent().find('.tbody').html(html);
      
    }
});
