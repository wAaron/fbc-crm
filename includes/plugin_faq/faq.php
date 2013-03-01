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

class module_faq extends module_base{
	
	public $links;

    public $version = 2.08;
    // 2.04 - initial release
    // 2.05 - linking faq with support tickets
    // 2.06 - faq fixes
    // 2.07 - support for multiple ticket queues based on "products" (set advanced 'ticket_separate_product_queue' to 1)
    // 2.08 - bug fix for ticket faq drop down.

    public static function can_i($actions,$name=false,$category=false,$module=false){
        if(!$module)$module=__CLASS__;
        return parent::can_i($actions,$name,$category,$module);
    }
	public static function get_class() {
        return __CLASS__;
    }
	public function init(){
		$this->links = array();
		$this->module_name = "faq";
		$this->module_position = 30;

        if(class_exists('module_template',false)){
            module_template::init_template('faq_item','
<a href="{FAQ_BACK}">&laquo; Return to FAQ Database</a>
<h2>{QUESTION}</h2>
{ANSWER}<br/>
','Used when displaying an individual FAQ item to the public.','code');


            module_template::init_template('faq_list','
<h2>FAQ Database</h2>
{LISTING}<br/>
','Used when displaying the list of FAQ items to the public.','code');
        }
	}

    public function pre_menu(){

        if($this->is_installed() && self::can_i('edit','FAQ')){
            $this->links[] = array(
                "name"=>"FAQ",
                "p"=>"faq_settings",
                'holder_module' => 'config', // which parent module this link will sit under.
                'holder_module_page' => 'config_admin',  // which page this link will be automatically added to.
                'menu_include_parent' => 0,
            );
        }
    }
	public function handle_hook($hook){
        $args = func_get_args();
		switch($hook){

            case 'ticket_public_created':
                // ticket has been created, link which product it is related to.
                // same thing happens in the ticket sidebar below.
                $ticket_id = func_get_arg(1);
                if($ticket_id>0){
                    // check if they selected a product from the drop down listing.
                }
                break;
            case 'ticket_create':
                if(module_config::c('faq_ticket_show_product_selection',1)){
                    ob_start();
                    include('hooks/ticket_create.php');
                    echo ob_get_clean();
                    return false;
                }
                break;
        }
    }



    public static function link_generate($faq_id=false,$options=array(),$link_options=array()){

        $key = 'faq_id';
        if($faq_id === false && $link_options){
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
        if(!isset($options['type']))$options['type']='faq';
        if(!isset($options['page']))$options['page']='faq_settings';
        if(!isset($options['arguments'])){
            $options['arguments'] = array();
        }
        $options['arguments']['faq_id'] = $faq_id;
        $options['module'] = 'faq';
        // what text should we display in this link?
        if($options['page']=='faq_products'){
            if(isset($options['data']) && $options['data']){
                //$options['data'] = $options['data'];
            }else{
                $data = self::get_faq_product($faq_id);
                $options['data'] = $data;
            }
            $options['text'] = isset($options['data']['name']) ? $options['data']['name'] : '';
            array_unshift($link_options,$options);
            $options['page']='faq_settings';
            // bubble back onto ourselves for the link.
            return self::link_generate($faq_id,$options,$link_options);
        }else if($options['page']=='faq_questions'){
            if(isset($options['data']) && $options['data']){
                //$options['data'] = $options['data'];
            }else{
                $data = self::get_faq($faq_id);
                $options['data'] = $data;
            }
            $options['text'] = isset($options['data']['question']) ? $options['data']['question'] : '';
            array_unshift($link_options,$options);
            $options['page']='faq_settings';
            // bubble back onto ourselves for the link.
            return self::link_generate($faq_id,$options,$link_options);
        }else{
            if(isset($options['data']) && $options['data']){
                //$options['data'] = $options['data'];
            }else{
                $data = self::get_faq($faq_id);
                $options['data'] = $data;
                $options['class'] = 'error';
            }
            $options['text'] = isset($options['data']['question']) ? $options['data']['question'] : _l('N/A');
        }
        array_unshift($link_options,$options);
        if($options['page']=='faq_admin' && $options['data'] && isset($options['data']['status_id'])){
            // pick the class name for the error. or faq status
            $link_options['class'] = 'faq_status_'.$options['data']['status_id'];
        }
        if(self::can_i('edit','FAQ')){
            if($options['page']=='faq_settings'){
                $bubble_to_module = array(
                    'module' => 'config',
                );
            }
        }
        if($bubble_to_module){
            global $plugins;
            return $plugins[$bubble_to_module['module']]->link_generate(false,array(),$link_options);
        }else{
            // return the link as-is, no more bubbling or anything.
            // pass this off to the global link_generate() function
            return link_generate($link_options);

        }
    }
    public static function link_open($faq_id,$full=false){
        return self::link_generate($faq_id,array('page'=>'faq_questions','full'=>$full,'arguments'=>array('faq_id'=>$faq_id)));
    }
    public static function link_open_public($faq_id,$h=false){
        if($h){
            return md5('s3cret7hash for faq items '._UCM_FOLDER.' '.$faq_id);
        }
        return full_link(_EXTERNAL_TUNNEL_REWRITE.'m.faq/h.public/i.'.$faq_id.'/hash.'.self::link_open_public($faq_id,true));
    }
    public static function link_external_faq_ticket(){
        return full_link(_EXTERNAL_TUNNEL_REWRITE.'m.faq/h.ticket_list/');
    }
    // used in the popup on the ticket page:
    public static function link_open_list($faq_product_id,$full=false){
        return self::link_generate($faq_product_id,array('page'=>'faq_ticket','full'=>$full,'arguments'=>array('faq_product_id'=>$faq_product_id,'faq_id'=>false)));
    }
    public static function link_open_faq_product($faq_product_id,$full=false){
        return self::link_generate($faq_product_id,array('page'=>'faq_products','full'=>$full,'arguments'=>array('faq_product_id'=>$faq_product_id)));
    }


    public function process(){
        if('save_faq_product' == $_REQUEST['_process']){

            if(!module_config::can_i('edit','FAQ')){
                die('No perms to save faq.');
            }

            if(isset($_POST['envato_item_ids']))$_POST['envato_item_ids'] = implode('|',$_POST['envato_item_ids']);
            $faq_product_id = update_insert('faq_product_id',$_REQUEST['faq_product_id'],'faq_product',$_POST);
            if(isset($_REQUEST['butt_del'])){
                // deleting ticket type all together
                delete_from_db('faq_product','faq_product_id',$_REQUEST['faq_product_id']);
                set_message('FAQ Product deleted successfully.');
                redirect_browser($this->link_open_faq_product(false));
            }
            set_message('FAQ Product saved successfully');
            redirect_browser($this->link_open_faq_product($faq_product_id));


        }else if('save_faq' == $_REQUEST['_process']){

            if(!module_config::can_i('edit','FAQ')){
                die('No perms to save faq.');
            }

            if(isset($_REQUEST['new_product_name']) && strlen(trim($_REQUEST['new_product_name']))){
                $faq_product_id = update_insert('faq_product_id',false,'faq_product',array('name'=>trim($_REQUEST['new_product_name'])));
                if(!isset($_REQUEST['faq_product_ids']))$_REQUEST['faq_product_ids']=array();
                $_REQUEST['faq_product_ids'][] = $faq_product_id;
            }

            $faq_id = update_insert('faq_id',$_REQUEST['faq_id'],'faq',$_POST);
            delete_from_db('faq_product_rel','faq_id',$faq_id);
            if(isset($_REQUEST['faq_product_ids']) && is_array($_REQUEST['faq_product_ids'])){
                foreach($_REQUEST['faq_product_ids'] as $faq_product_id){
                    if((int)$faq_product_id>0){
                        $sql = "INSERT INTO `"._DB_PREFIX."faq_product_rel` SET faq_id = ".(int)$faq_id.", faq_product_id = ".(int)$faq_product_id;
                        query($sql);
                    }
                }
            }
            if(isset($_REQUEST['butt_del'])){
                // deleting ticket type all together
                delete_from_db('faq','faq_id',$_REQUEST['faq_id']);
                set_message('FAQ deleted successfully.');
                redirect_browser($this->link_open(false));
            }
            set_message('FAQ saved successfully');
            redirect_browser($this->link_open($faq_id));


        }
    }


    
    public static function get_faq_products() {
        return get_multiple('faq_product',array(),'faq_product_id','exact','name');
    }
    public static function get_faq_products_rel() {
        $all_products_rel = array();
        foreach(self::get_faq_products() as $product){
            $all_products_rel[$product['faq_product_id']] = $product['name'];
        }
        return $all_products_rel;
    }

    public static function get_faq_product($faq_product_id) {
        return get_single('faq_product','faq_product_id',$faq_product_id);
    }

    public static function get_faqs($search=array()) {
        //return get_multiple('faq',array(),'faq_id','exact','question');
        $sql = "SELECT f.*, f.faq_id AS `id` FROM `"._DB_PREFIX."faq_product_rel` r LEFT JOIN `"._DB_PREFIX."faq` f";
        $sql .= " ON r.faq_id = f.faq_id ";
        $sql .= " WHERE 1 ";
        if(isset($search['question']) && $search['question']){
            $sql .= " AND f.question LIKE '%".mysql_real_escape_string($search['question'])."%'";
        }
        if(isset($search['faq_product_id']) && $search['faq_product_id']){
            $sql .= " AND r.faq_product_id = ".(int)$search['faq_product_id'];
        }
        $sql .= " GROUP BY f.faq_id";
        return qa($sql);
    }
    public static function get_faq($faq_id) {
        $faq = get_single('faq','faq_id',$faq_id);
        // get linked ids
        $faq['faq_product_ids'] = array();
        foreach(get_multiple('faq_product_rel',array('faq_id'=>$faq_id)) as $product){
            $faq['faq_product_ids'][$product['faq_product_id']] =$product['faq_product_id'];
        }
        return $faq;
    }


    public function external_hook($hook){

        switch($hook){
            case 'public':
                $faq_id = (isset($_REQUEST['i'])) ? (int)$_REQUEST['i'] : false;
                $hash = (isset($_REQUEST['hash'])) ? trim($_REQUEST['hash']) : false;
                if($faq_id && $hash){
                    $correct_hash = $this->link_open_public($faq_id,true);
                    if($correct_hash == $hash){
                        $faq = array();
                        if($faq_id>0){
                            $faq = $this->get_faq($faq_id);
                        }
                        if($faq){
                            $template = module_template::get_template_by_key('faq_item');
                            $faq['answer'] = forum_text($faq['answer']);
                            $faq['faq_back'] = $this->link_open_public(-1).(isset($_REQUEST['faq_product_id']) ? '&faq_product_id='.(int)$_REQUEST['faq_product_id'] : '');
                            $template->assign_values($faq);
                            $template->page_title = $faq['question'];
                            echo $template->render('pretty_html');
                        }else{
                            $template = module_template::get_template_by_key('faq_list');
                            $data = array();
                            ob_start();
                            include('public/faq_listing.php');
                            $data['listing'] = ob_get_clean();
                            $template->assign_values($data);
                            $template->page_title = _l('FAQ');
                            echo $template->render('pretty_html');
                        }
                    }
                }
                break;
            case 'ticket_list':
                $faq_product_id = (isset($_REQUEST['faq_product_id'])) ? (int)$_REQUEST['faq_product_id'] : false;
                @ob_end_clean();
                header("Content-type: text/javascript");
                if($faq_product_id){
                    $product = $this->get_faq_product($faq_product_id);
                    // find the faq items that match this product id.
                    if($product && $product['faq_product_id']==$faq_product_id){
                        $faqs = $this->get_faqs(array('faq_product_id'=>$faq_product_id));
                        ob_start();
                        ?>
                        <tr>
                            <th>
                                <?php _e('FAQ');?>
                            </th>
                            <td>
                                <?php _e('Please read through the below FAQ to see if the question has already been answered'); ?>

                                <ul><?php
                                    foreach($faqs as $data){
                                        $faq = module_faq::get_faq($data['faq_id']);
                                        ?>
                                        <li>
                                            <a href="<?php echo module_faq::link_open_public($data['faq_id'],false);?>" target="_blank"><?php echo htmlspecialchars($faq['question']); ?></a>
                                        </li>
                                        <?php } ?>
                                </ul>
                            </td>
                        </tr>
                        <?php
                        $html = preg_replace('#\s+#',' ',ob_get_clean());
                        ?>
                        $('#faq_product_area').html('<?php echo addcslashes($html,"'");?>');
                        <?php if($product['default_type_id']){ ?>
                            $('#ticket_type_id').val(<?php echo (int)$product['default_type_id'];?>);
                        <?php }
                        // and now we have to set the ticket position.
                        if(module_config::c('ticket_show_position',1)){
                            $new_position = module_ticket::ticket_position(false,$faq_product_id);
                            ?>
                            $('#ticket_position_field').html('<?php echo addcslashes(_l('%s out of %s other support tickets',ordinal($new_position['current']+1),$new_position['total']+1),"'"); ?>');
                            <?php
                            if(module_config::c('ticket_allow_priority',0)){
                                $c = module_ticket::get_priority_ticket_count($faq_product_id);
                                ?>
                                $('#priority_ticket_position').html('<?php _e('%s out of %s',ordinal($c[0]+1),$new_position['total']+1); ?>');
                                <?php
                            }
                        }
                        exit;
                    }
                }
                ?>  $('#faq_product_area').html(''); <?php
                if(module_config::c('ticket_show_position',1)){
                    $new_position = module_ticket::ticket_position();
                    ?>
                    $('#ticket_position_field').html('<?php echo addcslashes(_l('%s out of %s other support tickets',ordinal($new_position['current']+1),$new_position['total']+1),"'"); ?>');
                    <?php
                    if(module_config::c('ticket_allow_priority',0)){
                        ?>
                        $('#priority_ticket_position').html('<?php _e('%s out of %s',ordinal(module_ticket::ticket_count('priority')+1),$new_position['total']+1); ?>');
                        <?php
                    }
                }
                break;
        }
    }

    // find which faq products are related to a particular ticket.
    /*public static function get_products_by_ticket($ticket_id){
        $products = array();
        foreach(get_multiple('faq_ticket',array('ticket_id'=>$ticket_id)) as $faq_ticket){
            if($faq_ticket['faq_product_id'] > 0){
                $faq_product = self::get_faq_product($faq_ticket['faq_product_id']);
                $faq_product = array_merge($faq_ticket, $faq_product);
                $products[$faq_ticket['faq_product_id']] = $faq_product;
            }
        }
        return $products;
    }*/


    public function get_upgrade_sql(){

        $upgrade_sql = '';
        $fields = get_fields('faq_product');
        if(!isset($fields['default_type_id'])){
            $upgrade_sql .= "ALTER TABLE  `"._DB_PREFIX."faq_product` ADD  `default_type_id` INT(11) NOT NULL DEFAULT '0' AFTER  `faq_product_id`;";
        }
        if(!isset($fields['envato_item_ids'])){
            $upgrade_sql .= "ALTER TABLE  `"._DB_PREFIX."faq_product` ADD  `envato_item_ids` varchar(255) NOT NULL DEFAULT '' AFTER  `faq_product_id`;";
        }
        return $upgrade_sql;
    }

    public function get_install_sql(){
        ob_start();
        ?>


    CREATE TABLE IF NOT EXISTS `<?php echo _DB_PREFIX; ?>faq` (
    `faq_id` int(11) NOT NULL AUTO_INCREMENT,
    `question` varchar(255) NOT NULL DEFAULT '',
    `answer` TEXT NOT NULL DEFAULT '',
    `date_created` datetime NOT NULL,
    `date_updated` datetime NOT NULL,
    PRIMARY KEY (`faq_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    CREATE TABLE IF NOT EXISTS `<?php echo _DB_PREFIX; ?>faq_product` (
    `faq_product_id` int(11) NOT NULL AUTO_INCREMENT,
    `envato_item_ids` varchar(255) NOT NULL DEFAULT '',
    `default_type_id` int(11) NOT NULL DEFAULT '0',
    `name` varchar(60) NOT NULL DEFAULT '',
    `date_created` datetime NOT NULL,
    `date_updated` datetime NOT NULL,
    PRIMARY KEY (`faq_product_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    CREATE TABLE IF NOT EXISTS `<?php echo _DB_PREFIX; ?>faq_product_rel` (
    `faq_product_id` int(11) NOT NULL,
    `faq_id` int(11) NOT NULL,
    PRIMARY KEY (`faq_product_id`, `faq_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;



    <?php

        $sql =  ob_get_clean();
        return $sql;
        /* CREATE TABLE IF NOT EXISTS `<?php echo _DB_PREFIX; ?>faq_ticket` (
    `faq_ticket_id` int(11) NOT NULL AUTO_INCREMENT,
    `ticket_id` int(11) NOT NULL,
    `faq_product_id` int(11) NOT NULL DEFAULT '0',
    `date_created` date NOT NULL,
    `date_updated` date NULL,
    PRIMARY KEY (`faq_ticket_id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;
*/
    }

}


