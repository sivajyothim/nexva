/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

$(document).ready(function() {
  check_white_label();
});

var switcher;
var oldval='';

function check_white_label(){
  

$('#white_label').mouseout(function() {

    if((switcher == '') || (switcher != 'processing')){
        switcher = 'processing';
        if(oldval == ''){

        
        oldval   = $("#white_label").val();
        }else{
            
            if(oldval == $("#white_label").val()){
                switcher = 'done';
                return false;
            }
        }
    }else{
        oldval   = $("#white_label").val();
        return false;
    }
    whiteLabelValue  =$("#white_label").val();
    whiteLabelLength    =   whiteLabelValue.length;
    
    if(whiteLabelLength>5){
       loadinggif  =  "<img src='/cp/assets/img/global/nexva_loading.gif' />";
         $('#white_label_check_res_holder').html(loadinggif);
    }else{
        return false;
    }
        
         

  $.ajax({

  
  url: '/user/checkwhitelabelurl/url/'+$("#white_label").val(),
   
  success: function(data) {
      var $that = $(this);
       var submitButton = $that.find("input[type='submit']");
       $(submitButton).attr("disabled", "true");
      
      if(data == '0'){
        msg  =   " <i>This whitelabel URL is already exists</i>";
        $(submitButton).attr("disabled", "true");
      }else{

        msg  =   '';
         $that.find("input[type='submit']").removeAttr('disabled');
      }
      switcher = 'done';
      
    $('#white_label_check_res_holder').html(msg);
   
  }
});

}
);

}