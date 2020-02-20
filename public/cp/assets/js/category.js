
$(document).ready(function(){
    get_sub_categories();
    set_selected_sub_category();
    // change the category, on change load the sub categories
    $('#category_parent').change(get_sub_categories);

    /*$('#category_parent').change(function(){
        $('#submit').removeAttr("disabled");
    });*/

    /*$('#submit').click(function(){
        var subCategory = $('#category_sub').val();
        if(3 == subCategory){
            alert(subCategory);
            $('#submit').attr("disabled",true);
            //location.reload();
        }
    });*/

    /*$('#submit').click(function(){
        var subCategory = $('#category_sub').val();
        if(null == subCategory){
            $('#submit').attr("disabled",true);
        }
    });*/

    /*$('#submit').on('submie', function(){
        alert('asd');
        var subCategory = $('#category_sub').val();
        alert(subCategory);
    });*/

});


function get_sub_categories(){
    var value = $('#category_parent').val();
    //    alert(value);
    var prod_id = $('#id').val();
    $.getJSON('/async/getcategories', {
        id:value,
        product_id:prod_id
    }, process_sub_categories);
}

function process_sub_categories(data, textStatus){
    //    alert(data);
    //    $('#category_parent').parent().html(value);
    //    alert (parent);
    if($(".subcategories"))
        $(".subcategories").remove();
    var html = '<select class="validate(required) select-input subcategories" id="category_sub" name="category_sub">';
    var selectedSubVal	= parseInt($('#subcategory').val());
    for(var i in data){
        //        console.log(data[i]);
    	var selected	= (selectedSubVal == i) ? ' selected="selected" ' : ''; 
        html += '<option ' + selected + ' label="'+data[i]+'" value="'+i+'">'+data[i]+'</option>';
    }
    html += '</select>';
    $('#category_parent').parent().append(html);
}

function set_selected_sub_category(){
    var value = $('#category_parent').val();
    //    alert(value);
    var prod_id = $('#id').val();
    $.getJSON('/async/getselectedsubcat', {
        id:value,
        product_id:prod_id
    }, process_sub_selected_category);
    
}

function process_sub_selected_category(data, textStatus){
    $('.subcategories').val(data);
}