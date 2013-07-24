<?php 
/** 
  * Copyright: dtbaker 2012
  * Licence: Please check CodeCanyon.net for licence details. 
  * More licence clarification available here:  http://codecanyon.net/wiki/support/legal-terms/licensing-terms/ 
  * Deploy: 3053 c28b7e0e323fd2039bb168d857c941ee
  * Envato: 6b31bbe6-ead4-44a3-96e1-d5479d29505b
  * Package Date: 2013-02-27 19:23:35 
  * IP Address: 210.14.75.228
  */

/*
 * todo: don't do all sorting via PHP. this WILL BE SLOW for large data sets. oh well.
 */

function table_sort_asc($a,$b){
    return strnatcmp($a,$b);
}
function table_sort_desc($a,$b){
    return strnatcmp($b,$a);
}

// look into http://au.php.net/manual/en/function.array-multisort.php

class module_table_sort extends module_base{

    public static function can_i($actions,$name=false,$category=false,$module=false){
        if(!$module)$module=__CLASS__;
        return parent::can_i($actions,$name,$category,$module);
    }
	public static function get_class() {
        return __CLASS__;
    }
	public function init(){
		$this->module_name = "table_sort";
		$this->module_position = 0;
        $this->version = 2.21;
        // 2.21 - 2013-07-17 - fix for per-page dropdown
        // 2.2 - 2013-07-17 - memory improvements

        module_config::register_css('table_sort','table_sort.css');
	}

    public static $table_sort_options = array();
    public static $sortables = array();

    public static function enable_pagination_hook($options=array()) {
        if(get_display_mode()!='mobile')
            self::$table_sort_options=$options;
    }

    public static function run_pagination_hook(&$rows,&$per_page){
        if(self::$table_sort_options){

            $new_sort_column = $new_sort_direction = $new_sort_per_page = false;
            if(isset($_SESSION['_table_sort']) && isset($_SESSION['_table_sort'][self::$table_sort_options['table_id']]) && isset($_SESSION['_table_sort'][self::$table_sort_options['table_id']][0])){
                $new_sort_column = $_SESSION['_table_sort'][self::$table_sort_options['table_id']][0];
            }
            if(isset($_SESSION['_table_sort']) && isset($_SESSION['_table_sort'][self::$table_sort_options['table_id']]) && isset($_SESSION['_table_sort'][self::$table_sort_options['table_id']][1])){
                $new_sort_direction = $_SESSION['_table_sort'][self::$table_sort_options['table_id']][1];
            }
            if(isset($_SESSION['_table_sort']) && isset($_SESSION['_table_sort'][self::$table_sort_options['table_id']]) && isset($_SESSION['_table_sort'][self::$table_sort_options['table_id']][2])){
                $new_sort_per_page = $_SESSION['_table_sort'][self::$table_sort_options['table_id']][2];
            }
            if(isset($_REQUEST['table_sort_per_page']) && $_REQUEST['table_sort_per_page']){
                $new_sort_per_page = $_REQUEST['table_sort_per_page'];
                if(!isset($_SESSION['_table_sort']))$_SESSION['_table_sort']=array();
                if(!isset($_SESSION['_table_sort'][self::$table_sort_options['table_id']]))
                    $_SESSION['_table_sort'][self::$table_sort_options['table_id']] = array();
                $_SESSION['_table_sort'][self::$table_sort_options['table_id']][2] = $new_sort_per_page;
            }
            if(isset($_REQUEST['table_sort_column']) && isset($_REQUEST['table_sort_column']) && $_REQUEST['table_sort_column'] && $_REQUEST['table_sort_direction']){
                $new_sort_column = $_REQUEST['table_sort_column'];
                $new_sort_direction = $_REQUEST['table_sort_direction'];
                if(!isset($_SESSION['_table_sort']))$_SESSION['_table_sort']=array();
                if(!isset($_SESSION['_table_sort'][self::$table_sort_options['table_id']]))
                    $_SESSION['_table_sort'][self::$table_sort_options['table_id']] = array();
                $_SESSION['_table_sort'][self::$table_sort_options['table_id']][0] = $new_sort_column;
                $_SESSION['_table_sort'][self::$table_sort_options['table_id']][1] = $new_sort_direction;
            }
            // count how many results for the "per page" drop down below.
            self::$table_sort_options['row_count']= is_resource($rows) ? mysql_num_rows($rows) : count($rows);


            if(!isset($_SESSION['_table_sort']) || !isset($_SESSION['_table_sort'][self::$table_sort_options['table_id']])){
                return;
            }

            if($new_sort_column&&$new_sort_direction){
                // clear defaults! time for a user defined one.
                foreach(self::$table_sort_options['sortable'] as $column_id => $options){
                    if(isset($options['current'])){
                        unset(self::$table_sort_options['sortable'][$column_id]['current']);
                    }
                    if($column_id == $new_sort_column){
                        self::$table_sort_options['sortable'][$column_id]['current'] = $new_sort_direction;
                    }
                }
            }
            if($new_sort_per_page>=1){
                $per_page = $new_sort_per_page;
            }else if($new_sort_per_page==-2){
                // special flag for "all"
                $per_page = false;
            }


            // sort results by selected option.
            if(is_resource($rows)){
                $new_rows = array();
                while($row = mysql_fetch_assoc($rows)){
                    $new_rows[]=$row;
                }
                mysql_free_result($rows);
                $rows = $new_rows;
            }else{
                // rows stays the same.
            }
            if(is_array($rows) && count($rows)){

                foreach(self::$table_sort_options['sortable'] as $column_id => $options){
                    if(isset($options['current'])){
                        // we have a sortable key! yay!
                        // is this a special "group sort" ?
                        if(isset($options['group_sort']) && $options['group_sort'] && $options['owner_table'] && $options['owner_id']){
                            // find the group(s) for EVERY row in the result set.
                            // this is super slow, but only way to sort.
                            // we also sort multiple groups in the same order that is selected here.
                            if(class_exists('module_group',false)){

                                foreach($rows as $row_id=>$row){
                                    if(!isset($row[$options['owner_id']]) || !$row[$options['owner_id']])continue;
                                    // find the groups for this customer.
                                    $groups = module_group::get_groups_search(array(
                                        'owner_table' => $options['owner_table'],
                                        'owner_id' => $row[$options['owner_id']],
                                    ));
                                    $g=array();
                                    foreach($groups as $group){
                                        $g[] = $group['name'];
                                    }
                                    natcasesort($g);
                                    if($options['current'] == 1){
                                        // ascendine
                                    }else{
                                        // descenting
                                        $g = array_reverse($g);
                                    }
                                    $rows[$row_id]['group_sort_'.$options['owner_table']] = implode($g,', ');

                                }
                                self::$sortables['group_sort_'.$options['owner_table']] = $options['current'];
                            }
                        }else{
                            // nope! yay! normal sort.
                            self::$sortables[$options['field']] = $options['current'];
                        }
                    }
                }
                uasort($rows,array('module_table_sort','dosort'));

            }


            // set the 'per page' value based on session setting.
        }

    }
    public static function dosort($a,$b){
        // $a and $b are rows in our database.
        // what field are we sorting on?

        // for now we grab the first sortable. look at multisort later.
        $sort_key = key(self::$sortables);
        $sort_direction = current(self::$sortables);

        if(!isset($a[$sort_key])){
            $a[$sort_key] = '';
        }
        if(!isset($b[$sort_key])){
            $b[$sort_key] = '';
        }

        //if(isset($a[$sort_key]) && isset($b[$sort_key])){
            if($sort_direction==1) return strnatcasecmp($a[$sort_key],$b[$sort_key]);
            else return strnatcasecmp($b[$sort_key],$a[$sort_key]);
       // }else{
            //echo "No $sort_key";
       // }

        //return 0;

    }

    public static function display_pagination_hook($per_page){
        if(self::$table_sort_options){ // && self::$table_sort_options['row_count'] > 10
            if(isset(self::$table_sort_options['row_count'])){
                $per_page_increment = module_config::c('table_sort_per_page_increment',10);
            ?>
                <span class="table_sort_per_page">
                <label for="table_sort_per_page"><?php _e('Per Page:');?></label><select name="table_sort_per_page" id="table_sort_per_page">
                    <option value="-2"><?php _e('All');?></option>
                    <?php
                    $found_current = false;
                    for($x=$per_page_increment; $x<=self::$table_sort_options['row_count']; $x+=$per_page_increment){ ?>
                        <option value="<?php echo $x;?>"<?php if($per_page == $x){ $found_current = true; echo ' selected'; } ?>><?php echo $x;?></option>
                    <?php }
                    if(!$found_current && $per_page>0 && $per_page >= self::$table_sort_options['row_count']){
                        ?>
                        <option value="<?php echo $per_page;?>" selected="selected"><?php echo $per_page;?></option>
                        <?php
                    }
                    ?>
                </select>
                </span>
            <?php } ?>
            <script type="text/javascript">
                $(function(){
                    <?php foreach(self::$table_sort_options['sortable'] as $column_id => $options){
                        ?> 
                        $('th#<?php echo $column_id;?>').append('<a href="#" class="table_sort_btn table_sort_desc <?php if(isset($options['current']) && $options['current'] == 2){ echo 'current'; } ;?>">Desc</a>');
                        $('th#<?php echo $column_id;?>').append('<a href="#" class="table_sort_btn table_sort_asc <?php if(isset($options['current']) && $options['current'] == 1){ echo 'current'; } ;?>">Asc</a>');
                        <?php
                    } ?>
                    $('.table_sort_asc').click(function(){
                        var sort_column = $(this).parent().attr('id');
                        var sort_direction = 1;
                        table_sort_go(sort_column,sort_direction);
                        return false;
                    });
                    $('.table_sort_desc').click(function(){
                        var sort_column = $(this).parent().attr('id');
                        var sort_direction = 2;
                        table_sort_go(sort_column,sort_direction);
                        return false;
                    });
                    $('#table_sort_per_page').change(function(){
                        table_sort_per_page_go($(this).val());
                    });
                });
                function table_sort_per_page_go(per_page){
                    // see if there's a search bar to post.
                    var search_form = false;
                    search_form = $('.search_form')[0]
                    $('.search_bar').each(function(){
                        var form = $(this).parents('form');
                        if(typeof form != 'undefined'){
                            search_form = form;
                        }
                    });
                    if(typeof search_form == 'object'){
                        $(search_form).append('<input type="hidden" name="table_sort_per_page" value="'+per_page+'">');
                        search_form = search_form[0];
                        if(typeof search_form.submit == 'function'){
                            search_form.submit();
                        }else{
                            $('[name=submit]',search_form).click();
                        }
                        return false;
                    }
                }
                function table_sort_go(column,direction){
                    // see if there's a search bar to post.
                    var search_form = false;
                    search_form = $('.search_form')[0]
                    $('.search_bar').each(function(){
                        var form = $(this).parents('form');
                        if(typeof form != 'undefined'){
                            search_form = form;
                        }
                    });
                    if(typeof search_form == 'object'){
                        $(search_form).append('<input type="hidden" name="table_sort_column" value="'+column+'">');
                        $(search_form).append('<input type="hidden" name="table_sort_direction" value="'+direction+'">');
                        search_form = search_form[0];
                        if(typeof search_form.submit == 'function'){
                            search_form.submit();
                        }else{
                            $('[name=submit]',search_form).click();
                        }
                        return false;
                    }
                }
            </script>
        <?php
        }
    }


}