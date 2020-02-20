var jqXHR='';
var tpl='';
//$(function(){

    var ul = $('#preview-upload');

    /*$('#drop').click(function(){
        alert('Hoooo');
    });*/

    $('#drop a').click(function(){
        // Simulate a click on the file input button
        // to show the file browser dialog

        $(this).parent().find('input').click();
    });

    // Initialize the jQuery File Upload plugin
    $('#build').fileupload({

        url: '/async/upload-files',
        dataType: 'json',
        async: true,
        // This element will accept file drag/drop uploading
        dropZone: $('#drop'),

        // This function is called when a file is added to the queue;
        // either via the browse button, or via drag/drop:
        add: function (e, data) {

             tpl = $('<li class="working"><input type="text" value="0" data-width="48" data-height="48"'+
                ' data-fgColor="#0788a5" data-readOnly="1" data-bgColor="#3e4043" /><p></p><span></span></li>');

            // Append the file name and file size
            tpl.find('p').text(data.files[0].name)
                         .append('<i>' + formatFileSize(data.files[0].size) + '</i>');

            // Add the HTML to the UL element
            data.context = tpl.appendTo(ul);

            // Initialize the knob plugin
            tpl.find('input').knob();

            // Listen for clicks on the cancel icon
            tpl.find('span').click(function(){

                if(tpl.hasClass('working')){
                    jqXHR.abort();
                }

                tpl.fadeOut(function(){
                    tpl.remove();
                });

            });

            // Automatically upload the file once it is added to the queue
             jqXHR = data.submit();

        },

        progress: function(e, data){
            $('.device_submit').val('Please wait.. File upload in progress');
            $('#submit').attr("disabled","disabled");
            // Calculate the completion percentage of the upload
            var progress = parseInt(data.loaded / data.total * 100, 10);

            // Update the hidden input field and trigger a change
            // so that the jQuery knob plugin knows to update the dial
            data.context.find('input').val(progress).change();

            if(progress == 100){
//                data.context.removeClass('working');
//                //alert('fin');
//                $('.device_submit').removeAttr("disabled");

            }
        },

        fail:function(e, data){
            // Something has gone wrong!
            data.context.addClass('error');
        },
        done:function(e,data ){
            $('.device_submit').val('Save Build');
            var jSonObj=jQuery.parseJSON(jqXHR.responseText);
            if(jSonObj.status==='error'){  
                        if(tpl.hasClass('working')){
                            jqXHR.abort();
                        }
                        tpl.fadeOut(function(){
                            tpl.remove();
                        });

                $('#error-message').css('display','block'); 
                $('#error-message').html();
                $('#error-message').html("<p>Can't upload this file. Your content should not exceed 50MB .</p>");
            }else if(jSonObj.status==='file format error'){
                          if(tpl.hasClass('working')){
                            jqXHR.abort();
                        }
                        tpl.fadeOut(function(){
                            tpl.remove();
                        });

                $('#error-message').css('display','block');
                $('#error-message').html();
                $('#error-message').html("<p>System does not allow to upload '.php', '.pl', '.sh', '.zip', '.rar', '.txt', '.wgz', '.prc' files</p>");
            }else{
                data.context.removeClass('working');
                $('.device_submit').removeAttr("disabled");
                $('#submit').removeAttr("disabled");
                $('#error-message').css('display','none');                
            }
        },

    });

 // Initialize the jQuery File Upload plugin
    $('#upload-phone-number').fileupload({
        
        url: '/utility/upload-phone-numbers',
        dataType: 'json',
        async: true,
        // This element will accept file drag/drop uploading
        dropZone: $('#drop'),
        
        // This function is called when a file is added to the queue;
        // either via the browse button, or via drag/drop:
        add: function (e, data) {
        $('#preview-upload li').remove();
             tpl = $('<li class="working"><input type="text" value="0" data-width="48" data-height="48"'+
                ' data-fgColor="#0788a5" data-readOnly="1" data-bgColor="#3e4043" /><p></p><span></span></li>');

            // Append the file name and file size
            tpl.find('p').text(data.files[0].name)
                         .append('<i>' + formatFileSize(data.files[0].size) + '</i>');

            // Add the HTML to the UL element
            data.context = tpl.appendTo(ul);

            // Initialize the knob plugin
            tpl.find('input').knob();

            // Listen for clicks on the cancel icon
            tpl.find('span').click(function(){

                if(tpl.hasClass('working')){
                    jqXHR.abort();
                }

                tpl.fadeOut(function(){
                    tpl.remove();
                });

            });

            // Automatically upload the file once it is added to the queue
             jqXHR = data.submit();
           
        },

        progress: function(e, data){
            $('.device_submit').val('Please wait.. File upload in progress');
            $('#submit').attr("disabled","disabled");
            // Calculate the completion percentage of the upload
            var progress = parseInt(data.loaded / data.total * 100, 10);

            // Update the hidden input field and trigger a change
            // so that the jQuery knob plugin knows to update the dial
            data.context.find('input').val(progress).change();
        },

        fail:function(e, data){           
            data.context.addClass('error');
        },
        done:function(e,data ){
            var jSonObj=jQuery.parseJSON(jqXHR.responseText);
            if(jSonObj.status==='error'){  
                        if(tpl.hasClass('working')){
                            jqXHR.abort();
                        }
                        tpl.fadeOut(function(){
                            tpl.remove();
                        });

                $('#error-message').css('display','block'); 
                $('#error-message').html();
                $('#error-message').html("<p>Can't upload this file. Your content should not exceed 50MB .</p>");
            }else if(jSonObj.status==='file format error'){
                          if(tpl.hasClass('working')){
                            jqXHR.abort();
                        }
                        tpl.fadeOut(function(){
                            tpl.remove();
                        });

                $('#error-message').css('display','block');
                $('#error-message').html();
                $('#error-message').html("<p>System allow to upload '.csv' files only.</p>");            
            }else if(jSonObj.status==='upload error'){
                        if(tpl.hasClass('working')){
                            jqXHR.abort();
                        }
                        tpl.fadeOut(function(){
                            tpl.remove();
                        });

                $('#error-message').css('display','block');
                $('#error-message').html();
                $('#error-message').html("<p>Can't upload file into your server..</p>");
            }else{
                data.context.removeClass('working');
                $('.device_submit').removeAttr("disabled");
                $('#submit').removeAttr("disabled");
                $('#error-message').css('display','none');                
            }
        },
        
    });

    // Prevent the default action when a file is dropped on the window
    $(document).on('drop dragover', function (e) {
        e.preventDefault();
    });

    // Helper function that formats the file sizes
    function formatFileSize(bytes) {
        if (typeof bytes !== 'number') {
            return '';
        }

        if (bytes >= 1000000000) {
            return (bytes / 1000000000).toFixed(2) + ' GB';
        }

        if (bytes >= 1000000) {
            return (bytes / 1000000).toFixed(2) + ' MB';
        }

        return (bytes / 1000).toFixed(2) + ' KB';
    }
   
//});