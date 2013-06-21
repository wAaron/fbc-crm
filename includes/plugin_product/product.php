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


class module_product extends module_base{

	public $links;
	public $product_types;
    public $product_id;

    public static function can_i($actions,$name=false,$category=false,$module=false){
        if(!$module)$module=__CLASS__;
        return parent::can_i($actions,$name,$category,$module);
    }
	public static function get_class() {
        return __CLASS__;
    }
    public function init(){
		$this->links = array();
		$this->product_types = array();
		$this->module_name = "product";
		$this->module_position = 31;
        $this->version = 2.14;
        // 2.11 - initial release
        // 2.12 - product permissions
        // 2.13 - permission fix
        // 2.14 - product import via CSV

        if(module_security::is_logged_in() && self::can_i('view','Products')){

            module_config::register_css('product','product.css');
            module_config::register_js('product','product.js');

            if(isset($_REQUEST['_products_ajax'])){
                switch($_REQUEST['_products_ajax']){
                    case 'products_ajax_search':

                        if(self::$_product_count===false){
                            self::$_product_count = count(self::get_products());
                        }
                        $product_name = isset($_REQUEST['product_name']) ? $_REQUEST['product_name'] :'';
                        if(self::$_product_count>0){


                            $search = array();
                            if(strlen($product_name)>2){
                                $search['name'] = $product_name;
                            }
                            $products = self::get_products($search);
                            if(count($products)>0){
                                ?>
                                <ul>
                                    <?php foreach($products as $product){ ?>
                                    <li>
                                        <a href="#" onclick="return ucm.product.select_product(<?php echo $product['product_id'];?>);"><?php echo htmlspecialchars($product['name']);?></a>
                                    </li>
                                    <?php } ?>
                                </ul>
                                <?php
                            }
                        }else if(!strlen($product_name)){
                            _e('Pleae create Products first by going to Settings > Products');
                        }

                        exit;
                    case 'products_ajax_get':
                        $product_id = (int)$_REQUEST['product_id'];
                        if($product_id){
                            $product = self::get_product($product_id);
                        }else{
                            $product = array();
                        }
                        echo json_encode($product);
                        exit;
                }
            }
        }


	}

    public function pre_menu(){

		if($this->can_i('view','Products') && $this->can_i('edit','Products') && module_config::can_i('view','Settings')){

            // how many products are there?
            $link_name = _l('Products');

			$this->links['products'] = array(
				"name"=>$link_name,
				"p"=>"product_admin",
				"args"=>array('product_id'=>false),
                'holder_module' => 'config', // which parent module this link will sit under.
                'holder_module_page' => 'config_admin',  // which page this link will be automatically added to.
                'menu_include_parent' => 0,
			);
		}

    }

    /** static stuff */

    
     public static function link_generate($product_id=false,$options=array(),$link_options=array()){
        // we accept link options from a bubbled link call.
        // so we have to prepent our options to the start of the link_options array incase
        // anything bubbled up to this method.
        // build our options into the $options variable and array_unshift this onto the link_options at the end.
        $key = 'product_id'; // the key we look for in data arrays, on in _REQUEST variables. for sub link building.

        // we check if we're bubbling from a sub link, and find the item id from a sub link
        if(${$key} === false && $link_options){
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
        // grab the data for this particular link, so that any parent bubbled link_generate() methods
        // can access data from a sub item (eg: an id)

        if(isset($options['full']) && $options['full']){
            // only hit database if we need to print a full link with the name in it.
            if(!isset($options['data']) || !$options['data']){
                if((int)$product_id>0){
                    $data = self::get_product($product_id);
                }else{
                    $data = array();
                    return _l('N/A');
                }
                $options['data'] = $data;
            }else{
                $data = $options['data'];
            }
            // what text should we display in this link?
            $options['text'] = $data['name'];
        }
        $options['text'] = isset($options['text']) ? htmlspecialchars($options['text']) : '';
        // generate the arguments for this link
        $options['arguments'] = array(
            'product_id' => $product_id,
        );
        // generate the path (module & page) for this link
        $options['page'] = 'product_admin';
        $options['module'] = 'product';

        // append this to our link options array, which is eventually passed to the
        // global link generate function which takes all these arguments and builds a link out of them.

         if(!self::can_i('view','Products')){
            if(!isset($options['full']) || !$options['full']){
                return '#';
            }else{
                return isset($options['text']) ? $options['text'] : _l('N/A');
            }
        }

        // optionally bubble this link up to a parent link_generate() method, so we can nest modules easily
        // change this variable to the one we are going to bubble up to:
        $bubble_to_module = false;
        $bubble_to_module = array(
            'module' => 'config',
            'argument' => 'product_id',
        );
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


	public static function link_open($product_id,$full=false,$data=array()){
		return self::link_generate($product_id,array('full'=>$full,'data'=>$data));
	}



	public static function get_products($search=array()){

		return get_multiple("product",$search,"product_id","fuzzy","name");
	}


	public static function get_product($product_id){
        $product = get_single('product','product_id',$product_id);
        if(!$product){
            $product = array(
                'name'=>'',
                'amount'=>'',
                'quantity'=>'',
                'currency_id'=>'',
                'description'=>'',
            );
        }
        return $product;
	}


    
	public function process(){
		if(isset($_REQUEST['butt_del']) && $_REQUEST['butt_del'] && $_REQUEST['product_id']){
			$data = self::get_product($_REQUEST['product_id']);
            if(module_form::confirm_delete('product_id',"Really delete product: ".$data['name'],self::link_open($_REQUEST['product_id']))){
                $this->delete_product($_REQUEST['product_id']);
                set_message("Product deleted successfully");
                redirect_browser(self::link_open(false));
            }
		}else if("save_product" == $_REQUEST['_process']){
			$product_id = $this->save_product($_REQUEST['product_id'],$_POST);
			set_message("Product saved successfully");
			redirect_browser(self::link_open($product_id));
		}
	}


	public function save_product($product_id,$data){
		$product_id = update_insert("product_id",$product_id,"product",$data);
        module_extra::save_extras('product','product_id',$product_id);
		return $product_id;
	}


	public function delete_product($product_id){
		$product_id=(int)$product_id;
        $product = self::get_product($product_id);
        if($product && $product['product_id'] == $product_id){
            $sql = "DELETE FROM "._DB_PREFIX."product WHERE product_id = '".$product_id."' LIMIT 1";
            query($sql);
            module_extra::delete_extras('product','product_id',$product_id);
        }
	}

    private static $_product_count = false;
    public static function print_job_task_dropdown($task_id=false,$task_data=array()){
        if(self::can_i('view','Products')){
        ?>
        <span style="margin: 0 0 0 -23px; width: 20px; padding: 0; display: inline-block">
            <a href="#" onclick="return ucm.product.do_dropdown('<?php echo $task_id;?>',this);" class="ui-icon ui-icon-arrowthick-1-s">Products</a>
            <input type="hidden" name="job_task[<?php echo $task_id;?>][product_id]" id="task_product_id_<?php echo $task_id;?>" class="no_permissions" value="<?php echo isset($task_data['product_id']) ? (int)$task_data['product_id'] : '0';?>">
        </span>
        <?php
        }
    }
    
    public static function handle_import($data,$add_to_group){

        // woo! we're doing an import.

        // our first loop we go through and find matching products by their "product_name" (required field)
        // and then we assign that product_id to the import data.
        // our second loop through if there is a product_id we overwrite that existing product with the import data (ignoring blanks).
        // if there is no product id we create a new product record :) awesome.

        foreach($data as $rowid => $row){
            if(!isset($row['name']) || !trim($row['name'])){
                unset($data[$rowid]);
                continue;
            }
            if(!isset($row['product_id']) || !$row['product_id']){
                $data[$rowid]['product_id'] = 0;
            }
        }

        // now save the data.
        $count = 0;
        foreach($data as $rowid => $row){
            $product_id = update_insert('product_id',$row['product_id'],'product',$row);
            if($product_id){
                $count++;
            }
        }
        return $count;

    }



    public function get_upgrade_sql(){
        $sql = '';
        $fields = get_fields('task');
        if(!isset($fields['product_id'])){
            $sql .= 'ALTER TABLE `'._DB_PREFIX.'task` ADD `product_id` INT(11) NOT NULL DEFAULT \'0\' AFTER `task_order`;';
        }
        return $sql;
    }
    public function get_install_sql(){
        ob_start();
        ?>

CREATE TABLE `<?php echo _DB_PREFIX; ?>product` (
  `product_id` int(11) NOT NULL auto_increment,
  `product_category_id` int(11) NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL DEFAULT '',
  `description` TEXT NOT NULL DEFAULT '',
  `quantity` double(10,2) NOT NULL DEFAULT '0',
  `amount` double(10,2) NOT NULL DEFAULT '0',
  `currency_id` INT NOT NULL DEFAULT '1',
  `date_created` date NOT NULL,
  `date_updated` date NULL,
  PRIMARY KEY  (`product_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;


<?php
        return ob_get_clean();
    }


}
