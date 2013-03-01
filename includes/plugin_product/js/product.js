ucm.product = {

    product_name_search: '',
    text_box: false,
    task_id: '',
    selected_product_id: 0,

    init: function(){
        var t = this;
        $('.edit_task_description').live('change',function(){
            t.text_change(this);
        }).live('keyup',function(){
            t.text_change(this);
        });
    },
    text_change: function(txtbox){
        // look up a product based on this value
        this.text_box = txtbox;
        if(txtbox.value.length > 2 && txtbox.value != this.product_name_search){
            // grep the id out of this text box.
            this.task_id = $(this.text_box).attr('id').replace(/task_desc_/,'');
            //console.log(this.task_id);
            // search!

            // TODO: don't keep hitting ajax if the previous subset string search didn't return any results.

            this.product_name_search = txtbox.value;
            // search for results via ajax, if any are found we show them.
            try{ this.ajax_job.abort();}catch(err){}
            this.ajax_job = $.ajax({
                type: "POST",
                url: window.location.href,
                data: {
                    'product_name': this.product_name_search,
                    '_products_ajax': 'products_ajax_search'
                },
                success: function(result){
                    if(result != ''){
                        $(ucm.product.text_box).attr('autocomplete','off');
                        ucm.product.show_dropdown(result);
                    }else{
                        ucm.product.hide_dropdown();
                    }
                }
            });
        }
    },
    /* called when the dropdown button is clicked */
    do_dropdown: function(task_id,btn){
        this.task_id = task_id;
        if($('.product_select_dropdown').length>=1){
            this.hide_dropdown();
            return false;
        }
        this.text_box = $(btn).parent().parent().find('.edit_task_description');
        this.product_name_search = ''; // so we show everyting
        this.show_dropdown();
        return false;
    },
    show_dropdown: function(products){
        var t = this;
        if($('.product_select_dropdown').length>=1){
            t.hide_dropdown();
        }
        if(t.text_box){
            $(t.text_box).before('<div class="product_select_dropdown">Loading...</div>');
            if(typeof products != 'undefined'){
                $('.product_select_dropdown').html(products);
            }else{
                // todo - clean this ajax up into a single external call
                try{ this.ajax_job.abort();}catch(err){}
                this.ajax_job = $.ajax({
                    type: "POST",
                    url: window.location.href,
                    data: {
                        'product_name': '',
                        '_products_ajax': 'products_ajax_search'
                    },
                    success: function(result){
                        if(result != ''){
                            $(ucm.product.text_box).attr('autocomplete','off');
                            $('.product_select_dropdown').html(result);
                        }else{
                            ucm.product.hide_dropdown();
                        }
                    }
                });
            }
            if(!t.clickbound){
                setTimeout(function(){$('body').bind('click', t.hide_dropdown);},150);
                t.clickbound=true;
            }
        }
    },
    clickbound: false,
    hide_dropdown: function(){
        if($('.product_select_dropdown').length>=1){
            $('.product_select_dropdown').remove();
        }
        $('body').unbind('click', this.hide_dropdown);
        ucm.product.clickbound=false;
    },
    select_product: function(product_id){
        $.ajax({
            type: "POST",
            url: window.location.href,
            data: {
                'product_id': product_id,
                '_products_ajax': 'products_ajax_get'
            },
            dataType: 'json',
            success: function(product_data){
                //console.debug(product_data);
                /*amount: "1.00"
                currency_id: "1"
                date_created: "2013-01-28"
                date_updated: "2013-01-28"
                description: "asdfasdf"
                name: "test product"
                product_category_id: "0"
                product_id: "1"
                quantity: "4.00"*/
                if(product_data && product_data.product_id){
                    $('#task_product_id_'+ucm.product.task_id).val(product_data.product_id);
                    if(typeof product_data.name != 'undefined'){
                        $('#task_desc_'+ucm.product.task_id).val(product_data.name);
                    }
                    if(typeof product_data.quantity != 'undefined' && parseFloat(product_data.quantity)>0){
                        $('#task_hours_'+ucm.product.task_id).val(product_data.quantity);
                    }else{
                        $('#task_hours_'+ucm.product.task_id).val('');
                    }
                    if(typeof product_data.amount != 'undefined' && parseFloat(product_data.amount)>0){
                        $('#'+ucm.product.task_id+'taskamount').val(product_data.amount);
                    }else{
                        $('#'+ucm.product.task_id+'taskamount').val('');
                    }
                    if(typeof product_data.description != 'undefined'){
                        $('#task_long_desc_'+ucm.product.task_id).val(product_data.description);
                        if(product_data.description.length > 0){
                            $(ucm.product.text_box).parent().find('.task_long_description').slideDown();
                        }
                    }else{
                        $('#task_long_desc_'+ucm.product.task_id).val('');
                    }
                }
            }
        });
        return false;
    }
};

$(function(){
    ucm.product.init();
});