<?php 
/** 
  * Copyright: dtbaker 2012
  * Licence: Please check CodeCanyon.net for licence details. 
  * More licence clarification available here:  http://codecanyon.net/wiki/support/legal-terms/licensing-terms/ 
  * Deploy: 3053 c28b7e0e323fd2039bb168d857c941ee
  * Envato: 6b31bbe6-ead4-44a3-96e1-d5479d29505b
  * Package Date: 2013-02-27 19:09:56 
  * IP Address: 
  */


class module_template extends module_base{

    public $values = array();
    public $tags = array();
    public $content = '';
    public $description = '';
    public $wysiwyg = false;

    public $template_id;
    public $template_key;

    private static $_templates;
    public static function can_i($actions,$name=false,$category=false,$module=false){
        if(!$module)$module=__CLASS__;
        return parent::can_i($actions,$name,$category,$module);
    }
	public static function get_class() {
        return __CLASS__;
    }
	function init(){
		$this->links = array();
		$this->module_name = "template";
		$this->module_position = 28;

        $this->version = 2.235;
        //2.22 - wysiwyg edior error on creating new templates.
        //2.221 - perm fix
        //2.222 - sort by name instead of id
        //2.23 - editing templates from other settings pages in a popup
        //2.231 - new jquery version
        //2.232 - showing available template tags in template editor.
        //2.233 - speed improvements
        //2.234 - bug fix create new template
        //2.235 - support for basic arithmatic in template variables (+-) and dates (+1d-1m+2y)

		// the link within Admin > Settings > templates.
		if(module_security::has_feature_access(array(
				'name' => 'Settings',
				'module' => 'config',
				'category' => 'Config',
				'view' => 1,
				'description' => 'view',
		))){
			$this->links[] = array(
				"name"=>"Templates",
				"p"=>"template",
				"icon"=>"icon.png",
				"args"=>array('template_id'=>false),
				'holder_module' => 'config', // which parent module this link will sit under.
				'holder_module_page' => 'config_admin',  // which page this link will be automatically added to.
				'menu_include_parent' => 0,
			);
		}


	}

    private static function _load_all_templates(){
        self::$_templates = array();


        if(self::db_table_exists('template')){
            // load all templates into memory for quicker processing.
            foreach(self::get_templates() as $template){
                if($template['wysiwyg'] && stripos($template['content'],'<html')){
                    if(preg_match('#<body>(.*)</body>#imsU',$template['content'],$matches)){
                        $template['content'] = $matches[1];
                    }
                }
                self::$_templates[$template['template_key']] = $template;
            }
        }
    }


    public static function link_generate($template_id=false,$options=array(),$link_options=array()){

        $key = 'template_id';
        if($template_id === false && $link_options){
            foreach($link_options as $link_option){
                if(isset($link_option['data']) && isset($link_option['data'][$key])){
                    ${$key} = $link_option['data'][$key];
                    break;
                }
            }
            if(!${$key} && isset($_REQUEST[$key])){
                ${$key} = $_REQUEST[$key];
            }
        }
        $bubble_to_module = false;
        if(!isset($options['type']))$options['type']='template';
        $options['page'] = 'template_edit';
        if(!isset($options['arguments'])){
            $options['arguments'] = array();
        }
        $options['arguments']['template_id'] = $template_id;
        $options['module'] = 'template';
        $data = self::get_template($template_id);
        $options['data'] = $data;
        // what text should we display in this link?
        $options['text'] = (!isset($data['template_key'])||!trim($data['template_key'])) ? 'N/A' : htmlspecialchars($data['template_key']);
        //if(isset($data['template_id']) && $data['template_id']>0){
            $bubble_to_module = array(
                'module' => 'config',
                'argument' => 'template_id',
            );
       // }
        array_unshift($link_options,$options);

        if($bubble_to_module){
            global $plugins;
            return $plugins[$bubble_to_module['module']]->link_generate(false,array(),$link_options);
        }else{
            // return the link as-is, no more bubbling or anything.
            // pass this off to the global link_generate() function
            return link_generate($link_options);

        }
    }

	public static function link_open($template_id,$full=false){
        return self::link_generate($template_id,array('full'=>$full));
    }

    public static function link_open_popup($template_name){
        $template = self::get_template_by_key($template_name);
        $template_id = $template->template_id;
        $url = self::link_open($template_id);
        if(!preg_match('/display_mode/',$url)){
            $url .= (strpos($url,'?') ? '&' : '?').'display_mode=ajax';
        }
        $url .= (strpos($url,'?') ? '&' : '?').'return='.urlencode($_SERVER['REQUEST_URI']);
        ?>
        <p>
        <a href="#" onclick="$('#template_popup_<?php echo $template_id;?>').dialog('open'); return false;"><?php _e('Edit template: %s',htmlspecialchars($template->template_key));?></a>
            <em><?php echo htmlspecialchars($template->description);?></em>
        </p>
    <div id="template_popup_<?php echo $template_id;?>" title="">
        <div class="modal_inner"></div>
    </div>
    <script type="text/javascript">
        $(function(){
            $("#template_popup_<?php echo $template_id;?>").dialog({
                autoOpen: false,
                width: 700,
                height: 600,
                modal: true,
                buttons: {
                        /*Close: function() {
                            $(this).dialog('close');
                        }*/
                },
                open: function(){
                    var t = this;
                    $.ajax({
                        type: "GET",
                        url: '<?php echo $url;?>',
                        dataType: "html",
                        success: function(d){
                            $('.modal_inner',t).html(d);
                            $('input[name=_redirect]',t).val(window.location.href);
                            init_interface();
                        }
                    });
                },
                close: function() {
                    $('.modal_inner',this).html('');
                }
            });
        });
    </script>
        <?php
    }

    public function process(){
		if('save_template' == $_REQUEST['_process']){

            if(!module_config::can_i('edit','Settings')){
                die('No perms to edit Config > Settings');
            }
			$this->_handle_save_template();
		}

	}
		
	function delete($template_id){
		$template_id=(int)$template_id;
		$sql = "DELETE FROM "._DB_PREFIX."template WHERE template_id = '".$template_id."' LIMIT 1";
		$res = query($sql);
	}

    public static function add_tags($template_key,$tags_to_add){
        $template = self::get_template_by_key($template_key);
        $template_id = $template->template_id;
        if($template_id){
            if(!is_array($template->tags)){
                $template->tags = array();
            }
            foreach($tags_to_add as $key=>$val){
                if(strlen($val)>30){
                    $val = substr($val,0,30).'...';
                }
                unset($tags_to_add[$key]);
                $tags_to_add[strtoupper($key)] = $val;
            }
//            echo '<hr>';echo '<hr>';
//            print_r($tags_to_add);echo '<hr>';
//            print_r($template->tags);echo '<hr>';
            $new_tags = array_merge($tags_to_add,$template->tags);
//            print_r($new_tags);echo '<hr>';
            self::$_templates[$template_key]['tags'] = serialize($new_tags);
            update_insert('template_id',$template_id,'template',array('tags'=>serialize($new_tags)));
        }
    }

    public static function init_template($template_key,$content,$description,$type='text',$tags=array()){

        // todo - cache this, dont init on every page load
        if(!self::db_table_exists('template'))return;
        if(!count(self::$_templates)){
            self::_load_all_templates();
        }
        $template = false;

        if(isset(self::$_templates[$template_key])){
            $template = self::$_templates[$template_key];
        }else{
            $template = get_single("template","template_key",$template_key);
        }
        if(is_array($type) && !$tags){
            $tags = $type;
            $type = '';
        }
        //$template=get_single('template','template_key',$template_key);
        if(!$template || (!$template['content'] && $content)){
            $data = array(
                'template_key' => $template_key,
                'description' => $description,
                'content' => $content,
                'wysiwyg' => 1,
                'tags' => serialize($tags),
            );
            if($type=='text'){
                $data['content'] = nl2br($content);
            }else if($type=='code'){
                $data['wysiwyg'] = 0;
            }
            update_insert('template_id',($template&&$template['template_id'])?$template['template_id']:'new','template',$data);
            self::$_templates[$template_key] = $data;
        }else{
            // add new tags if any are given.
            if($tags && isset($template['tags']) && $template_key){
                // check these tags
                $new_tags = false;
                $existing_tags = @unserialize($template['tags']);
                foreach($tags as $key=>$val){
                    if(!isset($existing_tags[$key]))$new_tags=true;
                }
                if($new_tags){
                    self::add_tags($template_key,$tags);
                }
                /*$existing_tags = @unserialize($template['tags']);
                $tags = array_merge($existing_tags,$tags);
                update_insert('template_id',$template['template_id'],'template',array(
                    'tags'=>serialize($tags),
                ));*/
            }
        }
    }

    public static function &get_template_by_key($template_key){
        if(!count(self::$_templates)){
            self::_load_all_templates();
        }
        $template = new self();
        if(isset(self::$_templates[$template_key])){
            $data = self::$_templates[$template_key];
        }else if(self::db_table_exists('template')){
            $data = get_single("template","template_key",$template_key);
        }else{
            $data = array();
        }
        foreach($data as $key=>$val){
            if($key=='tags'){
                $template->{$key} = @unserialize($val);
            }else{
                $template->{$key} = $val;
            }
        }
        return $template;
    }
	public static function get_template($template_id){
        if(self::db_table_exists('template')){
            return get_single("template","template_id",$template_id);
        }else{
            return array();
        }
	}

	public static function get_templates($search=array()){
        if(self::db_table_exists('template')){
            // useto be sorted by template_id
            return get_multiple("template",$search,"template_id","exact","template_key ASC");
        }else{
            return array();
        }
	}



	private function _handle_save_template() {
		// handle post back for save template template.
		$template_id = (int)$_REQUEST['template_id'];
		$data = $_POST;
		// write header/footer html based on uploaded images.
		// pass uploaded images to the file manager plugin.
		$template_id = update_insert('template_id',$template_id,'template',$data);
		// redirect upon save.
		set_message('Template saved successfully!');
        if(isset($_REQUEST['return']) && $_REQUEST['return']){
            redirect_browser($_REQUEST['return']);
        }
		redirect_browser($this->link_open($template_id));
		exit;
	}

    public function assign_values($values){
        if(is_array($values)){
            foreach($values as $key=>$val){
                if(is_array($val))continue;
                $this->values[strtolower($key)] = $val;
            }
        }
    }
    public function replace_content(){
        $content = $this->content;
        $this->add_tags($this->template_key,$this->values);
        // add todays date values
        if(!isset($this->values['day'])){
            $this->values['day'] = date('d');
        }
        if(!isset($this->values['month'])){
            $this->values['month'] = date('m');
        }
        if(!isset($this->values['year'])){
            $this->values['year'] = date('y');
        }
        foreach($this->values as $key=>$val){
            if(is_array($val))continue;
            $content = str_replace('{'.strtoupper($key).'}',$val,$content);
            // we perform some basic arithmatic on some replace fields.
            if(preg_match_all('#'.preg_quote('{'.strtoupper($key),'#').'([+-])(\d+)\}#',$content,$matches)){
                foreach($matches[0] as $i=>$v){
                    if($matches[1][$i] == '-')
                        $mathval = $val - $matches[2][$i];
                    else if($matches[1][$i] == '+')
                        $mathval = $val + $matches[2][$i];
                    else
                        $mathval = $val;
                    $content = str_replace($v,$mathval,$content);
                }
            }
            // we perform some arithmatic on date fields.
            $matches = false;
            if(stripos($key,'date')!==false && $val && strlen($val)>6 && preg_match_all('#'.preg_quote('{'.strtoupper($key),'#').'((?>[+-]\d+[ymd])*)\}#',$content,$matches)){
                //$processed_date = (input_date($val)); $processed_date_timeo =
                $processed_date_time = strtotime(input_date($val));
                foreach($matches[0] as $i=>$v){
                    if(preg_match_all('#([+-])(\d+)([ymd])#',$matches[1][$i],$date_math)){
                        foreach($date_math[1] as $di => $dv){
                            $period = $date_math[3][$di];
                            $period = ($period=='d'?'day':($period=='m'?'month':($period=='y'?'year':'days')));
                            //echo $dv.$date_math[2][$di]." ".$period."\n";
                            $processed_date_time = strtotime($dv.$date_math[2][$di]." ".$period,$processed_date_time);
                        }
                        $content = str_replace($v,print_date($processed_date_time),$content);
                        //echo "Processing date: $val - $processed_date (time: $processed_date_timeo / ".print_date($processed_date_timeo).") with result of: ".print_date($processed_date_time); exit;
                    }
                }
            }
			//$val = str_replace(array('\\', '$'), array('\\\\', '\$'), $val);
			//$content = preg_replace('/\{'.strtoupper(preg_quote($key,'/')).'\}/',$val,$content);
        }
        return $content;
    }
    public function replace_description(){
        $content = $this->description;
        $this->add_tags($this->template_key,$this->values);
        foreach($this->values as $key=>$val){
            if(is_array($val))continue;
            $content = str_replace('{'.strtoupper($key).'}',$val,$content);
        }
        return $content;
    }
    public function render($type='html',$options=array()){
        ob_start();
        switch($type){
            case 'pretty_html':
                // header and footer so plain contnet can be rendered nicely.
                $display_mode = get_display_mode();
                ?>
                        <html>
                        <head>
                            <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
                            <title><?php echo $this->page_title ? $this->page_title : module_config::s('admin_system_name');?></title>
                            <?php $header_favicon = module_theme::get_config('theme_favicon','');
                            if($header_favicon){ ?>
                                <link rel="icon" href="<?php echo htmlspecialchars($header_favicon);?>">
                                <?php } ?>
                            <link rel="stylesheet" href="<?php echo _BASE_HREF;?>css/desktop.css" type="text/css">
                            <link rel="stylesheet" href="<?php echo _BASE_HREF;?>css/styles.css" type="text/css">
                            <link type="text/css" href="<?php echo _BASE_HREF;?>css/smoothness/jquery-ui-1.9.2.custom.min.css" rel="stylesheet" />
                            <?php module_config::print_css();?>
                            <style type="text/css">

                            </style>

                            <script type="text/javascript" src="<?php echo _BASE_HREF;?>js/jquery-1.8.3.min.js"></script>
                            <script type="text/javascript" src="<?php echo _BASE_HREF;?>js/jquery-ui-1.9.2.custom.min.js"></script>
                            <script type="text/javascript" src="<?php echo _BASE_HREF;?>js/timepicker.js"></script>
                            <script type="text/javascript" src="<?php echo _BASE_HREF;?>js/cookie.js"></script>
                            <script type="text/javascript" src="<?php echo _BASE_HREF;?>js/javascript.js?ver=2"></script>
                            <?php module_config::print_js();?>
                        </head>

                        <body>

                        <div style="" class="pretty_content_wrap">
                            <?php
                            $c = $this->replace_content();
                            if(!$this->wysiwyg){
                                //$c = nl2br($c);
                            }
                            echo $c;
                            ?>
                        </div>
                        </body>
                        </html>
                <?php
                break;
            case 'html':
            default:
                $c = $this->replace_content();
                if($this->wysiwyg){
                    //$c = nl2br($c);
                }
                echo $c;
                break;
        }
        return ob_get_clean();
    }

    public function get_install_sql(){
        ob_start();
        ?>

CREATE TABLE `<?php echo _DB_PREFIX; ?>template` (
  `template_id` int(11) NOT NULL auto_increment,
  `template_key` varchar(255) NOT NULL DEFAULT  '',
  `description` varchar(255) NOT NULL DEFAULT  '',
  `content` LONGTEXT NULL,
  `tags` TEXT NULL,
  `wysiwyg` CHAR( 1 ) NOT NULL DEFAULT  '1',
  `create_user_id` int(11) NOT NULL,
  `update_user_id` int(11) NULL,
  `date_created` date NOT NULL,
  `date_updated` date NULL,
  PRIMARY KEY  (`template_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


    <?php
        
        return ob_get_clean();
    }
    
}

