/*
 * upload file.js
 * admin jQuery
 * author : Heshan (heshan at nexva dot com)
 * package : neXva V 2.0
 */

// document ready function
$(document).ready(function(){

    /**
 * Ajax Upload library
 *
 **/
    //    var build_id;
    //    if($("#build_id").val()){
    //        build_id = $("#build_id").val();
    //    }
    /*
     * replacing this with uploadify library
    var prod_id = $("#id").val();
    var build_name = $("#build_name").val();
    new AjaxUpload('file', {
        // Location of the server-side upload script
        // NOTE: You are not allowed to upload files to another domain
        action: '/async/upload',
        // File upload name
        name: 'file',
        // Additional data to send
        data: {
            prod_id : prod_id,
            build_id : $("#build_id").val(),
            build_name : build_name
        },
        // Submit file after selection
        autoSubmit: true,
        // The type of data that you're expecting back from the server.
        // HTML (text) and XML are detected automatically.
        // Useful when you are using JSON data as a response, set to "json" in that case.
        // Also set server response type to text/html, otherwise it will not work in IE6
        responseType: false,
        // Fired after the file is selected
        // Useful when autoSubmit is disabled
        // You can return false to cancel upload
        // @param file basename of uploaded file
        // @param extension of that file
        onChange: function(file, extension){},
        // Fired before the file is uploaded
        // You can return false to cancel upload
        // @param file basename of uploaded file
        // @param extension of that file
        onSubmit: function(file, extension) {
            $('.progress').append('<p align="left"><img src="<?php echo ADMIN_PROJECT_BASEPATH;?>assets/img/global/ajax-loader.gif" width="220" height="19" /></p>');
        },
        // Fired when file upload is completed
        // WARNING! DO NOT USE "FALSE" STRING AS A RESPONSE!
        // @param file basename of uploaded file
        // @param response server response
        onComplete: function(file, response) {
            //            alert(file);
            //            alert('response : ' + response);
            var jsonObject = eval('(' + response + ')');
            //            alert(jsonObject.build_id);
            $('.progress').empty();
            var html = '<li class="recend_upload">' + file + ' <a onclick="return confirm(\'Are you sure you want to delete?\')" href="/build/filedelete/pid/' + jsonObject.product_id + '/bid/' + jsonObject.build_id + '/id/' + jsonObject.file_id + '">' + '<img alt="Delete" src="<?php echo ADMIN_PROJECT_BASEPATH;?>assets/img/global/icons/delete.png" title="Delete"></a></li>';
            $('.files').append(html);
            $('.recend_upload').css({
                'background-color' : 'yellow'
            });
            $('.recend_upload').fadeTo(5000, 1, function () {
                $('.recend_upload').css({
                    'background-color' : ''
                });
                $('.recend_upload').removeClass('recend_upload');
            });
            //set the build id to the hidden value
            // set the global build id to the new value
            //            alert('b4 assing the values :' + build_id);
            $("#build_id").val(jsonObject.build_id);
            //set the data parameter
            this.setData({
                'build_id': jsonObject.build_id,
                'prod_id': jsonObject.product_id,
                'build_name' : ''
            });
        }
    });

    */
    var prod_id = $("#id").val();
    var build_id = $("#build_id").val();
    var build_name = $("#build_name").val();

    $('#file').uploadify({

        'uploader'  : '/common/js/jquery/plugins/uploadify/uploadify.swf',
        'script'    : '/async/upload',
        'cancelImg' : '/common/js/jquery/plugins/uploadify/cancel.png',
        'folder'    : '/files',
        'multi'     : true,
        'displayData' : 'percentage',
        'removeCompleted' : false,
        'queueSizeLimit' : 20,
        'fileExt'     : '*.jad;*.cod;*.sis;*.sisx;*.apk;*.cab;*.mp3;*.jar;*.ipk;*.wgz;*.prc;*.jpg;*.jpeg;*.gif;*.png;*.bar;*.txt;*.mp4;',
        'fileDesc'    : 'App Files',
        'scriptData'  : {
            'prod_id':prod_id,
            'build_id':build_id,
            'build_name' : build_name
        },
        'onOpen'      : function(event,ID,fileObj) {
            $('.device_submit').attr("disabled", true);
            $('.device_submit').val('Please wait.. Upload in progress');
        },
        'onComplete'  : function(event, ID, fileObj, response, data) {
        //            var jsonObject = eval('(' + response + ')');
        //            alert(jsonObject);
        //            $("#build_id").val(jsonObject.build_id);
        //            alert(jsonObject.build_id);
        //                       alert ('event:' + event + ' Id:' + ID + ' response:' + response + ' data:' + data);
        //            alert('There are ' + data.fileCount + ' files remaining in the queue.');
        },
        'onAllComplete' : function(event,data) {
            $('.device_submit').removeAttr("disabled");
            $('.device_submit').val('Save Build');

        },
        'onError'     : function (event,ID,fileObj,errorObj) {
            //            var error = eval('(' + errorObj + ')');
            //            alert(error);
            console.log(event);
            console.log(errorObj);
            alert(errorObj.type + ' Error occured, please contact site administrator if this error persist : ' + errorObj.info);
        }

    });

});