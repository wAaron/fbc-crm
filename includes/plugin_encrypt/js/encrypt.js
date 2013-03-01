$(function(){
    $('span.encrypt_create').each(function(){
        var r = $(this);
        r.hide();
        r.parent('td').hover(function(){r.show();},function(){r.hide();});
    });
    $('span.encrypt_popup').each(function(){
        var r = $(this);
        r.hide();
        r.parent('td').hover(function(){r.show();},function(){r.hide();});
    });
});