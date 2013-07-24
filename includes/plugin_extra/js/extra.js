function extra_process_url(){
    var u = $(this).val().match(/https?:\/\/.*$/);
    if(u){
        $(this).parent().find('.extra_link_click').remove();
        $(this).after(' <a href="'+u+'" target="_blank" class="extra_link_click">open &raquo;</a>');
    }else{

    }
}
$(function(){
    $(document).on('change','.extra_value_input',extra_process_url);
    $('.extra_value_input').each(extra_process_url);
});