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

define('_FILE_UPLOAD_ALERT_STRING','Receive File Upload Alerts');
define('_FILE_COMMENT_ALERT_STRING','Receive File Comment Alerts');
define('_FILE_NOTIFICATION_TYPE_UPLOADED',1);
define('_FILE_NOTIFICATION_TYPE_UPDATED',2);
define('_FILE_NOTIFICATION_TYPE_COMMENTED',3);

define('_FILE_UPLOAD_PATH','includes/plugin_file/upload/');

class module_file extends module_base{
	
	var $links;

    public static function can_i($actions,$name=false,$category=false,$module=false){
        if(!$module)$module=__CLASS__;
        return parent::can_i($actions,$name,$category,$module);
    }
	public static function get_class() {
        return __CLASS__;
    }
	function init(){
		$this->links = array();
		$this->module_name = "file";
		$this->module_position = 21;

        $this->version = 2.539;
        // fix for files linked to multiple jobs
        // 2.42 - extra protection for assigning files to different customers.
        // 2.421 - job name displaying. htmlspecialchars removing.
        // 2.422 - bug fix.
        // 2.423 - bug fix creating new file under a customer.
        // 2.424 - files shared between customer accounts
        // 2.425 - bug fix for output appreaing before downloading images.
        // 2.5 - file previews.
        // 2.51 - fix ob error when no ob is present.
        // 2.511 - Delete file comments working
        // 2.512 - mime type fix for download (changed from old pdf to dynamic)
        // 2.513 - short open tags.
        // 2.514 - moves files tab to main menu, with a configuration variable. also put perms on the commenting system.
        // 2.52 - better file comment perms
        // 2.521 - file in menu perms.
        // 2.522 - file comment create perm bug fix
        // 2.523 - link fix for no customer perms
        // 2.524 - fix for mobile layout
        // 2.525 - adding files by url
        // 2.526 - swap to url bug fix
        // 2.527 - when new files are added an alert is sent (email/dashboard). alert will stay in place until that user views file.
        // 2.528 - customer contact alert email fix
        // 2.529 - newsletter system fixes
        // 2.53 - sql bug fix
        // 2.531 - email file notification fix
        // 2.532 - extra fields update - show in main listing option
        // 2.533 - better document support
        // 2.534 - improved quick search
        // 2.535 - 2013-04-10 - new customer permissions
        // 2.536 - 2013-05-11 - file upload progress indicator (swap back with 'file_upload_old' setting)
        // 2.537 - 2013-06-07 - file saving fix
        // 2.538 - 2013-06-21 - permission update
        // 2.539 - 2013-07-17 - progress bar

        if(class_exists('module_template',false)){
            module_template::init_template('file_upload_alert_email','Dear {TO_NAME},<br>
<br>
A file has been uploaded/updated by {FROM_NAME} called {FILE_NAME}.<br><br>
View this file by going here: {FILE_LINK}<br><br>
Customer: {CUSTOMER_NAME}<br/>
Description: {DESCRIPTION}
','File Updated: {FILE_NAME} {CUSTOMER_NAME}',array(
            'to_name' => 'Recipient name',
            'from_name' => 'Uploader name',
            'file_link'=>'Link to file',
            'customer_name'=>'Customer Name',
            'description'=>'File notes',
                ));
            module_template::init_template('file_comment_alert_email','Dear {TO_NAME},<br>
<br>
A file has been commented on by {FROM_NAME} called {FILE_NAME}.<br><br>
View this file by going here: {FILE_LINK}<br><br>
Customer: {CUSTOMER_NAME}<br/>
Comment: {COMMENT}
','File Comment: {FILE_NAME} {CUSTOMER_NAME}',array(
            'to_name' => 'Recipient name',
            'from_name' => 'Uploader name',
            'file_link'=>'Link to file',
            'customer_name'=>'Customer Name',
            'comment'=>'File comment',
                ));
        }

	}

    public function pre_menu(){

        if($this->can_i('edit','Files') || $this->can_i('view','Files')){
            /*$this->ajax_search_keys = array(
                _DB_PREFIX.'file' => array(
                    'plugin' => 'file',
                    'search_fields' => array(
                        'file_name',
                        'description',
                    ),
                    'key' => 'file_id',
                    'title' => _l('File: '),
                ),
            );*/

            // only display if a customer has been created.
            if(isset($_REQUEST['customer_id']) && $_REQUEST['customer_id'] && $_REQUEST['customer_id']!='new'){
                // how many files?
                $files = $this->get_files(array('customer_id'=>$_REQUEST['customer_id']));
                $name = _l('Files');
                if(count($files)){
                    $name .= " <span class='menu_label'>".count($files)."</span> ";
                }
                $this->links[] = array(
                    "name"=>$name,
                    "p"=>"file_admin",
                    'args'=>array('file_id'=>false),
                    'holder_module' => 'customer', // which parent module this link will sit under.
                    'holder_module_page' => 'customer_admin_open',  // which page this link will be automatically added to.
                    'menu_include_parent' => 0,
                );
            }
            /*$this->links[] = array(
                "name"=>"Files",
                "p"=>"file_admin",
                'args'=>array('file_id'=>false),
            );*/

        }

        if(module_config::c('files_on_main_menu',1) && ($this->can_i('edit','Files') || $this->can_i('view','Files'))){
            // find out how many for this contact.
            $files = $this->get_files();
            $this->links[] = array(
                "name"=>_l('Files')." <span class='menu_label'>".count($files)."</span> ",
                "p"=>"file_admin",
                'args'=>array('file_id'=>false),
            );

            /*$customer_ids = module_security::get_customer_restrictions();
            if($customer_ids){
                $files = array();
                foreach($customer_ids as $customer_id){
                    $files = $files + $this->get_files(array('customer_id'=>$customer_id));
                }
                $this->links[] = array(
                    "name"=>_l('Files')." <span class='menu_label'>".count($files)."</span> ",
                    "p"=>"file_admin",
                    'args'=>array('file_id'=>false),
                );
            }*/
        }
    }
    
    public function ajax_search($search_key){
        // return results based on an ajax search.
        $ajax_results = array();
        $search_key = trim($search_key);
        if(strlen($search_key) > module_config::c('search_ajax_min_length',2)){
            //$sql = "SELECT * FROM `"._DB_PREFIX."file` c WHERE ";
            //$sql .= " c.`file_name` LIKE %$search_key%";
            //$results = qa($sql);
            $results = $this->get_files(array('generic'=>$search_key));
            if(count($results)){
                foreach($results as $result){
                    // what part of this matched?
                    /*if(
                        preg_match('#'.preg_quote($search_key,'#').'#i',$result['name']) ||
                        preg_match('#'.preg_quote($search_key,'#').'#i',$result['last_name']) ||
                        preg_match('#'.preg_quote($search_key,'#').'#i',$result['phone'])
                    ){
                        // we matched the file contact details.
                        $match_string = _l('File Contact: ');
                        $match_string .= _shl($result['file_name'],$search_key);
                        $match_string .= ' - ';
                        $match_string .= _shl($result['name'],$search_key);
                        // hack
                        $_REQUEST['file_id'] = $result['file_id'];
                        $ajax_results [] = '<a href="'.module_user::link_open_contact($result['user_id']) . '">' . $match_string . '</a>';
                    }else{*/
                        $match_string = _l('File: ');
                        $match_string .= _shl($result['file_name'],$search_key);
                        $ajax_results [] = '<a href="'.$this->link_open($result['file_id']) . '">' . $match_string . '</a>';
                        //$ajax_results [] = $this->link_open($result['file_id'],true);
                    /*}*/
                }
            }
        }
        return $ajax_results;
    }

    
    public static function link_generate($file_id=false,$options=array(),$link_options=array()){

        $key = 'file_id';
        if($file_id === false && $link_options){
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
        if(!isset($options['type']))$options['type']='file';
        $options['page'] = 'file_admin';
        if(!isset($options['arguments'])){
            $options['arguments'] = array();
        }
        $options['arguments']['file_id'] = $file_id;
        $options['module'] = 'file';
        if(isset($options['data'])){
            $data = $options['data'];
        }else{
            $data = array();
            if($file_id>0){
                $data = self::get_file($file_id);
            }
            $options['data'] = $data;
        }
        if(!isset($data['customer_id'])&&isset($_REQUEST['customer_id']) && (int)$_REQUEST['customer_id']){
            $data['customer_id']=(int)$_REQUEST['customer_id'];
        }
        // what text should we display in this link?
        $options['text'] = (!isset($data['file_name'])||!trim($data['file_name'])) ? 'N/A' : $data['file_name'];
        if(isset($data['customer_id']) && $data['customer_id']>0){
            $bubble_to_module = array(
                'module' => 'customer',
                'argument' => 'customer_id',
            );
        }
        array_unshift($link_options,$options);

        if(!module_security::has_feature_access(array(
            'name' => 'Customers',
            'module' => 'customer',
            'category' => 'Customer',
            'view' => 1,
            'description' => 'view',
        ))
        ){
            $bubble_to_module = false;
            /*if(!isset($options['full']) || !$options['full']){
                return '#';
            }else{
                return isset($options['text']) ? $options['text'] : 'N/A';
            }*/

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

	public static function link_open($file_id,$full=false){
        return self::link_generate($file_id,array('full'=>$full));
    }
	
	
	function handle_hook($hook,&$calling_module=false,$owner_table=false,$key_name=false,$key_value=false){
		switch($hook){
            case 'home_alerts':
                $alerts = array();
                if(self::can_i('view','Files') && module_config::c('file_upload_alerts',1) &&
                    (
                        module_security::can_user(module_security::get_loggedin_id(),_FILE_UPLOAD_ALERT_STRING) ||
                        module_security::can_user(module_security::get_loggedin_id(),_FILE_COMMENT_ALERT_STRING)
                    )
                ){
                    $sql = "SELECT * FROM `"._DB_PREFIX."file_notification` fn ";
                    $sql .= " WHERE fn.user_id = ".(int)module_security::get_loggedin_id()." AND fn.view_time = 0";
                    $sql .= " GROUP BY fn.file_id";
                    $files = qa($sql);
                    foreach($files as $file){
                        $file_data = self::get_file($file['file_id']);
                        $customer_data = array();
                        if($file_data['customer_id']){
                            $customer_data = module_customer::get_customer($file_data['customer_id']);
                            if(!$customer_data || $customer_data['customer_id'] != $file_data['customer_id']){
                                continue;// current user doesn't have permission to view this customer.
                            }
                        }
                        switch($file['notification_type']){
                            case _FILE_NOTIFICATION_TYPE_COMMENTED:
                                $status = _l('File Comment');
                                break;
                            case _FILE_NOTIFICATION_TYPE_UPDATED:
                                $status = _l('File Updated');
                                break;
                            case _FILE_NOTIFICATION_TYPE_UPLOADED:
                                $status = _l('New File Uploaded');
                                break;
                            default:
                                $status = _l('File');
                        }
                        $alert_res = process_alert($file['date_created'], $status);
                        if($alert_res){
                            $alert_res['link'] = $this->link_open($file['file_id'],false,$file);
                            $alert_res['name'] = '';
                            if($customer_data){
                                $alert_res['name'] .= $customer_data['customer_name'] . ' ';
                            }
                            $alert_res['name'] .= _l('(File: %s)',$file_data['file_name']);
                            $alerts['file'.$file['file_id']] = $alert_res;
                        }
                    }
                }
                return $alerts;
                break;
            case 'file_list':
            case 'file_delete':
                // find the key we are saving this address against.
                $owner_id = (int)$key_value;
                if(!$owner_id || $owner_id == 'new'){
                    // find one in the post data.
                    if(isset($_REQUEST[$key_name])){
                        $owner_id = $_REQUEST[$key_name];
                    }
                }
                $file_hash = md5($owner_id.'|'.$owner_table); // just for posting unique arrays.
                break;
        }
		switch($hook){
			case "file_list":
				if($owner_id && $owner_id != 'new'){

					$file_items = $this->get_files(array("owner_table"=>$owner_table,"owner_id"=>$owner_id));
					foreach($file_items as &$file_item){
						// do it in loop here because of $this issues in static method below.
						// instead of include file below.
						$file_item['html'] = $this->print_file($file_item['file_id']);
					}
					include("pages/file_list.php");
				}else{
					echo 'Please save first before creating files.';
				}
				break;
			case "file_delete":

				if($owner_table && $owner_id){
                    $this->delete_files($owner_table,$owner_id);
				}
				break;
			
		}
	}

	public static function display_files($options){
        
		$owner_id = (isset($options['owner_id']) && $options['owner_id']) ? (int)$options['owner_id'] : false;
		$owner_table = (isset($options['owner_table']) && $options['owner_table']) ? $options['owner_table'] : false;
		if($owner_id && $owner_table){
			// we have all that we need to display some files!! yey!!
			// do we display a summary or not?
			global $plugins;
			$file_items = $plugins['file']->get_files(array('owner_table'=>$owner_table,'owner_id'=>$owner_id));
			if(isset($options['summary_owners']) && is_array($options['summary_owners'])){
				// generate a list of other files we have to display int eh list.
				foreach($options['summary_owners'] as $summary_owner_table => $summary_owner_ids){
					if(is_array($summary_owner_ids)){
						foreach($summary_owner_ids as $summary_owner_id){
							$file_items = array_merge($file_items,$plugins['file']->get_files(array('owner_table'=>$summary_owner_table,'owner_id'=>$summary_owner_id)));
						}
					}
				}
			}
			$layout_type = (isset($options['layout']) && $options['layout']) ?$options['layout'] : 'gallery';
			$editable = (!isset($options['editable']) || $options['editable']);
			foreach($file_items as &$file_item){
				$file_item['html'] = $plugins['file']->print_file($file_item['file_id'],$layout_type,$editable,$options);
			}

            if(get_display_mode()=='mobile')$editable=false;
			$title = (isset($options['title']) && $options['title']) ?$options['title'] :false;
            if(!@include('pages/file_list_'.basename($layout_type).'.php')){
                include("pages/file_list.php");
            }
		}
	}

	public function print_file($file_id,$layout_type='gallery',$editable=true,$options=array()){
		$file_item = $this->get_file($file_id);
		ob_start();
		switch($layout_type){
			case 'gallery':
			?>

			<div class="file_<?php echo $file_item['file_id'];?>" style="float:left; width:110px; margin:3px; border:1px solid #CCC; text-align:center;">
				<div style="width:110px; min-height:40px; ">
                    <?php
                    $link = $this->link('',array('_process'=>'download','file_id'=>$file_id),'file',false);
                    if(isset($options['click_callback'])){
                        $link = 'javascript:'.$options['click_callback'].'('.$file_id.',\''.htmlspecialchars($this->link_public_view($file_id)).'\',\''.htmlspecialchars(addcslashes($file_item['file_name'],"'")).'\')';
                    }
                    ?>
					<a href="<?php echo $link;?>">
					<?php
					// /display a thumb if its supported.
					if(preg_match('/\.(\w\w\w\w?)$/',$file_item['file_name'],$matches)){
						switch(strtolower($matches[1])){
							case 'jpg':
							case 'jpeg':
							case 'gif':
							case 'png':
								?>
                                    <img src="<?php
                                    // echo _BASE_HREF . nl2br(htmlspecialchars($file_item['file_path']));
                                    echo $this->link_public_view($file_id);
                                    ?>" width="100" alt="download" border="0">
								<?php
								break;
							default:
                                ?>
                                <img src="<?php echo full_link('includes/plugin_file/images/file_icon.png');?>" width="100" alt="<?php _e('Download');?>">
                                <?php
								//echo 'Download';
						}
					}
					?>
					</a>
				</div>
				<?php if($editable){ ?>
				    <a href="#" class="file_edit<?php echo $file_item['owner_table'];?>_<?php echo $file_item['owner_id'];?>" rel="<?php echo $file_item['file_id'];?>"><?php echo nl2br(wordwrap(htmlspecialchars($file_item['file_name']),15,'<wbr>',true));?></a>
				<?php }else{ ?>
				    <a href="<?php echo $this->link('',array('_process'=>'download','file_id'=>$file_item['file_id']),'file',false);?>"><?php echo nl2br(wordwrap(htmlspecialchars($file_item['file_name']),15,'<wbr>',true));?></a>
				<?php } ?>
			</div>
			<?php
			break;
			case 'list':
			?>
			<span class="file_<?php echo $file_item['file_id'];?>">
				<?php if($editable){ ?>
					<a href="#" class="file_edit<?php echo $file_item['owner_table'];?>_<?php echo $file_item['owner_id'];?>" rel="<?php echo $file_item['file_id'];?>"><?php echo nl2br(htmlspecialchars($file_item['file_name']));?></a>
				<?php }else{ ?>
					<a href="<?php echo $this->link('',array('_process'=>'download','file_id'=>$file_item['file_id']),'file',false);?>"><?php echo nl2br(htmlspecialchars($file_item['file_name']));?></a>
				<?php } ?>
			</span>
			<?php
			break;
		}
		return ob_get_clean();
	}
	function process(){
		if('plupload' == $_REQUEST['_process']){

            @ob_end_clean();

            // HTTP headers for no cache etc
            header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
            header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
            header("Cache-Control: no-store, no-cache, must-revalidate");
            header("Cache-Control: post-check=0, pre-check=0", false);
            header("Pragma: no-cache");

            // Settings
            $targetDir = _FILE_UPLOAD_PATH . "plupload";
            //$targetDir = 'uploads';

            $cleanupTargetDir = true; // Remove old files
            $maxFileAge = 5 * 3600; // Temp file age in seconds

            // 5 minutes execution time
            @set_time_limit(5 * 60);

            // Uncomment this one to fake upload time
            // usleep(5000);

            // Get parameters
            $chunk = isset($_REQUEST["chunk"]) ? intval($_REQUEST["chunk"]) : 0;
            $chunks = isset($_REQUEST["chunks"]) ? intval($_REQUEST["chunks"]) : 0;
            $fileName = isset($_REQUEST["plupload_key"]) ? $_REQUEST["plupload_key"] : '';
            $fileName = preg_replace('/[^a-zA-Z0-9]+/', '', $fileName);
            if(!$fileName){
                die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "No plupload_key defined."}, "id" : "id"}');
            }

            // Make sure the fileName is unique but only if chunking is disabled
            if ($chunks < 2 && file_exists($targetDir . DIRECTORY_SEPARATOR . $fileName)) {
                $ext = strrpos($fileName, '.');
                $fileName_a = substr($fileName, 0, $ext);
                $fileName_b = substr($fileName, $ext);

                $count = 1;
                while (file_exists($targetDir . DIRECTORY_SEPARATOR . $fileName_a . '_' . $count . $fileName_b))
                    $count++;

                $fileName = $fileName_a . '_' . $count . $fileName_b;
            }

            $filePath = $targetDir . DIRECTORY_SEPARATOR . $fileName;

            // Create target dir
            if (!file_exists($targetDir))
                @mkdir($targetDir);

            // Remove old temp files
            if ($cleanupTargetDir) {
                if (is_dir($targetDir) && ($dir = opendir($targetDir))) {
                    while (($file = readdir($dir)) !== false) {
                        $tmpfilePath = $targetDir . DIRECTORY_SEPARATOR . $file;

                        // Remove temp file if it is older than the max age and is not the current file
                        if (preg_match('/\.part$/', $file) && (filemtime($tmpfilePath) < time() - $maxFileAge) && ($tmpfilePath != "{$filePath}.part")) {
                            @unlink($tmpfilePath);
                        }
                    }
                    closedir($dir);
                } else {
                    die('{"jsonrpc" : "2.0", "error" : {"code": 100, "message": "Failed to open temp directory."}, "id" : "id"}');
                }
            }

            // Look for the content type header
            $contentType = '';
            if (isset($_SERVER["HTTP_CONTENT_TYPE"]))
                $contentType = $_SERVER["HTTP_CONTENT_TYPE"];

            if (isset($_SERVER["CONTENT_TYPE"]))
                $contentType = $_SERVER["CONTENT_TYPE"];

            // Handle non multipart uploads older WebKit versions didn't support multipart in HTML5
            if (strpos($contentType, "multipart") !== false) {
                if (isset($_FILES['file']['tmp_name']) && is_uploaded_file($_FILES['file']['tmp_name'])) {
                    // Open temp file
                    $out = @fopen("{$filePath}.part", $chunk == 0 ? "wb" : "ab");
                    if ($out) {
                        // Read binary input stream and append it to temp file
                        $in = @fopen($_FILES['file']['tmp_name'], "rb");

                        if ($in) {
                            while ($buff = fread($in, 4096))
                                fwrite($out, $buff);
                        } else
                            die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
                        @fclose($in);
                        @fclose($out);
                        @unlink($_FILES['file']['tmp_name']);
                    } else
                        die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
                } else
                    die('{"jsonrpc" : "2.0", "error" : {"code": 103, "message": "Failed to move uploaded file."}, "id" : "id"}');
            } else {
                // Open temp file
                $out = @fopen("{$filePath}.part", $chunk == 0 ? "wb" : "ab");
                if ($out) {
                    // Read binary input stream and append it to temp file
                    $in = @fopen("php://input", "rb");

                    if ($in) {
                        while ($buff = fread($in, 4096))
                            fwrite($out, $buff);
                    } else
                        die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');

                    @fclose($in);
                    @fclose($out);
                } else
                    die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
            }

            // Check if file has been uploaded
            if (!$chunks || $chunk == $chunks - 1) {
                // Strip the temp .part suffix off
                rename("{$filePath}.part", $filePath);
            }

            die('{"jsonrpc" : "2.0", "result" : null, "id" : "id"}');


        }else if('download' == $_REQUEST['_process']){
            @ob_end_clean();
			$file_id = (int)$_REQUEST['file_id'];
			$file_data = $this->get_file($file_id);
			if(is_file($file_data['file_path'])){
				header("Pragma: public");
				header("Expires: 0");
				header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
				header("Cache-Control: private",false);
				//header("Content-Type: application/pdf");
                header("Content-type: ".dtbaker_mime_type($file_data['file_name'],$file_data['file_path']));
				header("Content-Disposition: attachment; filename=\"".$file_data['file_name']."\";");
				header("Content-Transfer-Encoding: binary");
				header("Content-Length: ".filesize($file_data['file_path']));
				readfile($file_data['file_path']);
			}else{
				echo 'Not found';
			}
			exit;
		}else if('save_file_popup' == $_REQUEST['_process']){
			$file_id = $_REQUEST['file_id'];

			$file_path = false;
			$file_name = false;

            $options = unserialize(base64_decode($_REQUEST['options']));

			// have we uploaded anything
			if(isset($_FILES['file_upload']) && is_uploaded_file($_FILES['file_upload']['tmp_name'])){
				// copy to file area.
				$file_name = basename($_FILES['file_upload']['name']);
				if($file_name){
					$file_path = _FILE_UPLOAD_PATH.md5(time().$file_name);
					if(move_uploaded_file($_FILES['file_upload']['tmp_name'],$file_path)){
						// it worked. umm.. do something.
					}else{
                        ?>
                    <script type="text/javascript">
                        alert('Unable to save file. Please check permissions.');
                    </script>
                    <?php
						// it didnt work. todo: display error.
                        $file_path = false;
                        $file_name = false;
                        //set_error('Unable to save file');
					}
				}
			}

			if(isset($_REQUEST['file_name']) && $_REQUEST['file_name']){
				$file_name = $_REQUEST['file_name'];
			}

            if(!$file_path && !$file_name){
                return false;
            }

			if(!$file_id || $file_id == 'new'){
				$file_data = array(
					'file_id' => $file_id,
					'owner_id' => (int)$_REQUEST['owner_id'],
					'owner_table' => $_REQUEST['owner_table'],
					'file_time' => time(), // allow UI to set a file time? nah.
					'file_name' => $file_name,
					'file_path' => $file_path,
				);
			}else{
				// some fields we dont want to overwrite on existing files:
				$file_data = array(
					'file_id' => $file_id,
					'file_path' => $file_path,
					'file_name' => $file_name,
				);
			}
			// make sure we're saving a file we have access too.
			module_security::sanatise_data('file',$file_data);
			$file_id = update_insert('file_id',$file_id,'file',$file_data);
			$file_data = $this->get_file($file_id);
			// we've updated from a popup.
			// this means we have to replace an existing file id with the updated output.
			// or if none exists on the page, we add a new one to the holder.
			$layout_type = (isset($_REQUEST['layout']) && $_REQUEST['layout']) ?$_REQUEST['layout'] : 'gallery';
			?>
			<script type="text/javascript">
				// check if it exists in parent window
				var new_html = '<?php echo addcslashes(preg_replace('/\s+/',' ',$this->print_file($file_id,$layout_type,true,$options)),"'");?>';
				parent.new_file_added<?php echo $file_data['owner_table'];?>_<?php echo $file_data['owner_id'];?>(<?php echo $file_id;?>,'<?php echo $file_data['owner_table'];?>',<?php echo $file_data['owner_id'];?>,new_html);
			</script>
			<?php
			exit;
		}else if('save_file' == $_REQUEST['_process']){
			$file_id = (int)$_REQUEST['file_id'];

			$file_path = false;
			$file_name = false;
			$file_url = '';

            if(isset($_REQUEST['butt_del']) && self::can_i('delete','Files')){
                if(module_form::confirm_delete('file_id','Really delete this file?')){
                    $file_data = $this->get_file($file_id);
                    if($file_data && $file_data['file_id'] == $file_id){ //module_security::can_access_data('file',$file_data,$file_id)){
                        // delete the physical file.
                        if($file_data['file_path'] && is_file($file_data['file_path'])){
                            unlink($file_data['file_path']);
                        }
                        // delete the db entry.
                        delete_from_db('file','file_id',$file_id);
                        // delete any comments.
                        delete_from_db('file_comment','file_id',$file_id);
                        set_message('File removed successfully');
                    }
                }
                redirect_browser(module_file::link_open(false));
            }else{

                // todo: stop people changing the "file_id" to another file they don't own.
                if(self::can_i('edit','Files') || self::can_i('create','Files')){
                    // have we uploaded anything
                    $file_changed = false;
                    if(isset($_REQUEST['plupload_key'])&&strlen(preg_replace('/[^a-zA-Z0-9]+/', '', basename($_REQUEST['plupload_key'])))){
                        $plupload_key =  preg_replace('/[^a-zA-Z0-9]+/', '', basename($_REQUEST['plupload_key']));
                        if($plupload_key && is_file(_FILE_UPLOAD_PATH.'plupload'.DIRECTORY_SEPARATOR.$plupload_key)){
                            $file_name = basename($_REQUEST['plupload_file_name']);
                            if($file_name){
                                $file_path = _FILE_UPLOAD_PATH.md5(time().$file_name);
                                if(rename(_FILE_UPLOAD_PATH.'plupload'.DIRECTORY_SEPARATOR.$plupload_key,$file_path)){
                                    // it worked. umm.. do something.
                                    $file_changed = true;
                                }else{
                                    // it didnt work. todo: display error.
                                    $file_path = false;
                                    $file_name = false;
                                    set_error('Unable to save file via plupload.');
                                }
                            }
                        }
                    }
                    if(!$file_changed && isset($_FILES['file_upload']) && is_uploaded_file($_FILES['file_upload']['tmp_name'])){
                        // copy to file area.
                        $file_name = basename($_FILES['file_upload']['name']);
                        if($file_name){
                            $file_path = _FILE_UPLOAD_PATH.md5(time().$file_name);
                            if(move_uploaded_file($_FILES['file_upload']['tmp_name'],$file_path)){
                                // it worked. umm.. do something.
                                $file_changed = true;
                            }else{
                                // it didnt work. todo: display error.
                                $file_path = false;
                                $file_name = false;
                                set_error('Unable to save file');
                            }
                        }
                    }
                    if(!$file_path && isset($_REQUEST['file_url']) && isset($_REQUEST['file_name'])){
                        $file_name = $_REQUEST['file_name'];
                        $file_url = $_REQUEST['file_url'];
                        $file_path = '';// remove old file path
                    }

                    // make sure we have a valid customer_id and job_id selected.
                    $possible_customers = $possible_jobs = array();
                    if(class_exists('module_customer',false)){
                        $possible_customers = module_customer::get_customers();
                    }
                    if(class_exists('module_job',false)){
                        $possible_jobs = module_job::get_jobs();
                    }

                    $original_file_data = array();
                    if($file_id>0){
                        $original_file_data = self::get_file($file_id);
                    }

                    $new_file = false;
                    if(!$file_id || $file_id == 'new'){
                        $file_data = array(
                            'file_id' => $file_id,
                            'customer_id' => isset($_REQUEST['customer_id']) ? (int)$_REQUEST['customer_id'] : false,
                            'job_id' => isset($_REQUEST['job_id']) ? (int)$_REQUEST['job_id'] : false,
                            'website_id' => isset($_REQUEST['website_id']) ? (int)$_REQUEST['website_id'] : false,
                            'status' => isset($_REQUEST['status']) ? $_REQUEST['status'] : false,
                            'pointers' => isset($_REQUEST['pointers']) ? $_REQUEST['pointers'] : false,
                            'description' => isset($_REQUEST['description']) ? $_REQUEST['description'] : false,
                            'file_time' => time(), // allow UI to set a file time? nah.
                            'file_name' => $file_name,
                            'file_path' => $file_path,
                            'file_url' => $file_url,
                        );
                        if(!isset($possible_customers[$file_data['customer_id']])){
                            $file_data['customer_id'] = 0;
                        }
                        if(!isset($possible_jobs[$file_data['job_id']])){
                            $file_data['job_id'] = 0;
                        }
                        $new_file = true;
                    }else{
                        // some fields we dont want to overwrite on existing files:
                        $file_data = array(
                            'file_id' => $file_id,
                            'file_path' => $file_path,
                            'file_name' => $file_name,
                            'file_url' => $file_url,
                            'pointers' => isset($_REQUEST['pointers']) ? $_REQUEST['pointers'] : false,
                            'customer_id' => isset($_REQUEST['customer_id']) ? (int)$_REQUEST['customer_id'] : false,
                            'job_id' => isset($_REQUEST['job_id']) ? (int)$_REQUEST['job_id'] : false,
                            'website_id' => isset($_REQUEST['website_id']) ? (int)$_REQUEST['website_id'] : false,
                            'status' => isset($_REQUEST['status']) ? $_REQUEST['status'] : false,
                            'description' => isset($_REQUEST['description']) ? $_REQUEST['description'] : false,
                        );
                        if(!isset($possible_customers[$file_data['customer_id']]) && $file_data['customer_id'] != $original_file_data['customer_id']){
                            $file_data['customer_id'] = $original_file_data['customer_id'];
                        }
                        if(!isset($possible_jobs[$file_data['job_id']]) && $file_data['job_id'] != $original_file_data['job_id']){
                            $file_data['job_id'] = $original_file_data['job_id'];
                        }
                    }



                    // make sure we're saving a file we have access too.
                    module_security::sanatise_data('file',$file_data);
                    $file_id = update_insert('file_id',$file_id,'file',$file_data);

                    module_extra::save_extras('file','file_id',$file_id);

                    if($file_changed){
                        // do we schedule an alert for this file upload?
                        if(module_security::can_user(module_security::get_loggedin_id(),_FILE_UPLOAD_ALERT_STRING)){
                            // the current user is one who receives file alerts.
                            // so for now we don't schedule this alert.
                            // hmm - this might not work with a team environment, we'll send alerts no matter what :)
                        }
                        $alert_users = module_user::get_users_by_permission(
                            array(
                                'category' => _LABEL_USER_SPECIFIC,
                                'name' =>  _FILE_UPLOAD_ALERT_STRING,
                                'module' => 'config',
                                'view' => 1,
                            )
                        );
                        if(isset($alert_users[1])){
                            unset($alert_users[1]); // skip admin for now until we can control that option
                        }
                        $file_data['customer_name'] = '';
                        $file_data['customer_link'] = '';
                        if(isset($file_data['customer_id']) && $file_data['customer_id']){
                            $customer_data = module_customer::get_customer($file_data['customer_id']);
                            $file_data['customer_name'] = $customer_data['customer_name'];
                            $file_data['customer_link'] = module_customer::link_open($file_data['customer_id']);
                        }
                        $file_data['file_link'] = self::link_open($file_id);
                        foreach($alert_users as $alert_user){
                            if(isset($alert_user['customer_id']) && $alert_user['customer_id'] > 0){
                                // only send this user an alert of the file is from this customer account.
                                if(!isset($file_data['customer_id']) || $file_data['customer_id'] != $alert_user['customer_id']){
                                    continue; // skip this user
                                }
                            }
                            $notification_data = array(
                                'email_id' => 0,
                                'view_time' => 0,
                                'notification_type' => $new_file ? _FILE_NOTIFICATION_TYPE_UPLOADED : _FILE_NOTIFICATION_TYPE_UPDATED,
                                'file_id' => $file_id,
                                'user_id'=>$alert_user['user_id'],
                            );

                            $template = module_template::get_template_by_key('file_upload_alert_email');
                            $template->assign_values($file_data);
                            $html = $template->render('html');
                            // send an email to this user.
                            $email = module_email::new_email();
                            $email->replace_values = $file_data;
                            $email->set_to('user',$alert_user['user_id']);
                            $email->set_from('user',module_security::get_loggedin_id());
                            $email->set_subject($template->description);
                            // do we send images inline?
                            $email->set_html($html);

                            if($email->send()){
                                // it worked successfully!!
                                // sweet.
                                $notification_data['email_id'] = $email->email_id;
                            }else{
                                /// log err?
                                set_error('Failed to send notification email to user id '.$alert_user['user_id']);
                            }

                            update_insert('file_notification_id','new','file_notification',$notification_data);
                        }
                    }// file changed


                }

                if(module_file::can_i('create','File Comments')){
                    if(isset($_REQUEST['new_comment_text']) && strlen($_REQUEST['new_comment_text'])){
                        $item_data = array(
                            "file_id"=>$file_id,
                            "create_user_id"=>module_security::get_loggedin_id(),
                            "comment"=>$_REQUEST['new_comment_text'],
                        );
                        update_insert("file_comment_id","new","file_comment",$item_data);

                        $file_data['comment'] = $_REQUEST['new_comment_text'];


                        // do we schedule an alert for this file upload?
                        if(module_security::can_user(module_security::get_loggedin_id(),_FILE_COMMENT_ALERT_STRING)){
                            // the current user is one who receives file alerts.
                            // so for now we don't schedule this alert.
                            // hmm - this might not work with a team environment, we'll send alerts no matter what :)
                        }
                        $alert_users = module_user::get_users_by_permission(
                            array(
                                'category' => _LABEL_USER_SPECIFIC,
                                'name' =>  _FILE_COMMENT_ALERT_STRING,
                                'module' => 'config',
                                'view' => 1,
                            )
                        );
                        if(isset($alert_users[1])){
                            unset($alert_users[1]); // skip admin for now until we can control that option
                        }
                        $file_data['customer_name'] = '';
                        $file_data['customer_link'] = '';
                        if(isset($file_data['customer_id']) && $file_data['customer_id']){
                            $customer_data = module_customer::get_customer($file_data['customer_id']);
                            $file_data['customer_name'] = $customer_data['customer_name'];
                            $file_data['customer_link'] = module_customer::link_open($file_data['customer_id']);
                        }
                        $file_data['file_link'] = self::link_open($file_id);
                        foreach($alert_users as $alert_user){

                            if(isset($alert_user['customer_id']) && $alert_user['customer_id'] > 0){
                                // only send this user an alert of the file is from this customer account.
                                if(!isset($file_data['customer_id']) || $file_data['customer_id'] != $alert_user['customer_id']){
                                    continue; // skip this user
                                }
                            }

                            $notification_data = array(
                                'email_id' => 0,
                                'view_time' => 0,
                                'notification_type' => _FILE_NOTIFICATION_TYPE_COMMENTED,
                                'file_id' => $file_id,
                                'user_id'=>$alert_user['user_id'],
                            );

                            $template = module_template::get_template_by_key('file_comment_alert_email');
                            $template->assign_values($file_data);
                            $html = $template->render('html');
                            // send an email to this user.
                            $email = module_email::new_email();
                            $email->replace_values = $file_data;
                            $email->set_to('user',$alert_user['user_id']);
                            $email->set_from('user',module_security::get_loggedin_id());
                            $email->set_subject($template->description);
                            // do we send images inline?
                            $email->set_html($html);

                            if($email->send()){
                                // it worked successfully!!
                                // sweet.
                                $notification_data['email_id'] = $email->email_id;
                            }else{
                                /// log err?
                                set_error('Failed to send notification email to user id '.$alert_users['user_id']);
                            }

                            update_insert('file_notification_id','new','file_notification',$notification_data);
                        }
                    }
                }

                if(isset($_REQUEST['delete_file_comment_id']) && $_REQUEST['delete_file_comment_id']){
                    $file_comment_id = (int)$_REQUEST['delete_file_comment_id'];
                    $comment = get_single('file_comment','file_comment_id',$file_comment_id);
                    if($comment['create_user_id'] == module_security::get_loggedin_id() || module_file::can_i('delete','File Comments')){
                        $sql = "DELETE FROM `"._DB_PREFIX."file_comment` WHERE file_id = '$file_id' AND file_comment_id = '$file_comment_id' ";
                        $sql .= " LIMIT 1";
                        query($sql);
                    }
                }
                set_message('File saved successfully');
                redirect_browser($this->link_open($file_id));
            }
		}else if('delete_file_popup' == $_REQUEST['_process']){
			$file_id = (int)$_REQUEST['file_id'];

			if(!$file_id || $file_id == 'new'){
				// cant delete a new file.. do nothing.
			}else{
				$file_data = $this->get_file($file_id);
				if(true){ //module_security::can_access_data('file',$file_data,$file_id)){
					// delete the physical file.
					if(is_file($file_data['file_path'])){
						unlink($file_data['file_path']);
					}
					// delete the db entry.
                    delete_from_db('file','file_id',$file_id);
					// update ui with changes.
					?>
					<script type="text/javascript">
						var new_html = '';
						parent.new_file_added<?php echo $file_data['owner_table'];?>_<?php echo $file_data['owner_id'];?>(<?php echo $file_id;?>,'<?php echo $file_data['owner_table'];?>',<?php echo $file_data['owner_id'];?>,new_html);
					</script>
					<?php
				}
			}
			exit;
		}

	}
	
	function save(){
		
	}

    public static function get_file_comments($file_id){
        $search = array("file_id"=>$file_id);
        $items = get_multiple("file_comment",$search,'file_comment_id','exact','date_created DESC');
        return $items;
    }

	function delete($file_id){
		$file_id=(int)$file_id;
		$sql = "DELETE FROM "._DB_PREFIX."file WHERE file_id = '".$file_id."' LIMIT 1";
		$res = query($sql);
	}

	public static function get_file($file_id){
		$file = get_single("file","file_id",$file_id);
        // check user has permissions to view this file.
        // for now we just base this on the customer id check
        if($file && $file['customer_id']){
            $customer_permission_check = module_customer::get_customer($file['customer_id']);
            if($customer_permission_check['customer_id'] != $file['customer_id']){
                $file = false;
            }
        }
        if(!$file){
            $file = array(
                'file_id' => 'new',
                'customer_id' => isset($_REQUEST['customer_id']) ? $_REQUEST['customer_id'] : 0,
                'job_id' => isset($_REQUEST['job_id']) ? $_REQUEST['job_id'] : 0,
                'description' => '',
                'status' => module_config::c('file_default_status','Uploaded'),
                'file_name' => '',
                'file_url' => '',
            );
        }
		if($file){
			// optional processing here later on.
			
		}
		return $file;
	}

	public static function get_files($search=false,$skip_permissions=false){

        // build up a custom search sql query based on the provided search fields
        $sql = "SELECT f.* ";
        $from = " FROM `"._DB_PREFIX."file` f ";
        if(class_exists('module_customer',false)){
            $from .= " LEFT JOIN `"._DB_PREFIX."customer` c USING (customer_id)";
        }
        $where = " WHERE 1 ";
        if(isset($search['generic']) && $search['generic']){
            $str = mysql_real_escape_string($search['generic']);
            $where .= " AND ( ";
            $where .= " f.file_name LIKE '%$str%' ";
            //$where .= "OR  u.url LIKE '%$str%'  ";
            $where .= ' ) ';
        }
        /*if(isset($search['job']) && $search['job']){
            $str = mysql_real_escape_string($search['job']);
            $from .= " LEFT JOIN `"._DB_PREFIX."job` j USING (job_id)";
            $where .= " AND ( ";
            $where .= " j.name LIKE '%$str%' ";
            $where .= ' ) ';
        }*/
        // tricky job searching, by name or by job id.
        // but we don't want to restrict it to customer if they are searching for a job.
        /*
         * this is the logic we have to follow:
         *
        $customer_access = module_customer::get_customer($file['customer_id']);
        $job_access = module_job::get_job($file['job_id']);
        if(
            ($customer_access && $customer_access['customer_id'] == $file['customer_id']) ||
            ($job_access && $job_access['job_id'] == $file['job_id'])
        ){
         */
        foreach(array('file_id','owner_id','owner_table','status') as $key){
            if(isset($search[$key]) && $search[$key] !== ''&& $search[$key] !== false){
                $str = mysql_real_escape_string($search[$key]);
                $where .= " AND f.`$key` = '$str'";
            }
        }

        // permissions from customer module.
        // tie in with customer permissions to only get jobs from customers we can access.
        if(!$skip_permissions && class_exists('module_customer',false)){ //added for compat in newsletter system that doesn't have customer module
            switch(module_customer::get_customer_data_access()){
                case _CUSTOMER_ACCESS_ALL:
                    // all customers! so this means all files!
                    break;
                case _CUSTOMER_ACCESS_ALL_COMPANY:
                case _CUSTOMER_ACCESS_CONTACTS:
                case _CUSTOMER_ACCESS_TASKS:
                case _CUSTOMER_ACCESS_STAFF:
                    $valid_customer_ids = module_security::get_customer_restrictions();
                    if(count($valid_customer_ids)){
                        $where .= " AND ( ";
                        foreach($valid_customer_ids as $valid_customer_id){
                            if(isset($search['owner_table'])){
                                $where .= " (f.owner_table = 'customer' AND f.owner_id = '".(int)$valid_customer_id."') OR ";
                            }else{
                                $where .= " (f.customer_id = '".(int)$valid_customer_id."') OR ";
                                if(isset($search['customer_id']) && $search['customer_id'] && $search['customer_id'] == $valid_customer_id){
                                    unset($search['customer_id']);
                                }
                            }
                        }
                        $where = rtrim($where,'OR ');
                        $where .= ' ) ';
                    }
                    break;

            }
        }


        if(class_exists('module_job',false)){
            if(isset($search['job_id']) && (int)$search['job_id']>0){
                // check if we have permissions to view this job.
                $job = module_job::get_job($search['job_id']);
                if(!$job || $job['job_id'] != $search['job_id']){
                    $search['job_id'] = false;
                }
            }
        }
        if(isset($search['job_id']) && (int)$search['job_id']>0){
            $where .= " AND f.job_id = ".(int)$search['job_id'];
        }else if(isset($search['customer_id']) && (int)$search['customer_id']){
            $where .= " AND f.customer_id = ".(int)$search['customer_id'];
        }


        $group_order = ' GROUP BY f.file_id ORDER BY f.file_name'; // stop when multiple company sites have same region
        $sql = $sql . $from . $where . $group_order;
        //echo $sql;
        $result = qa($sql);
        //module_security::filter_data_set("invoice",$result);
        return $result;
		//return get_multiple("file",$search,"file_id","exact","file_id");
	}


    public static function format_bytes($size) {
        $units = array(' B', ' KB', ' MB', ' GB', ' TB');
        for ($i = 0; $size >= 1024 && $i < 4; $i++) $size /= 1024;
        return round($size, 2).$units[$i];
    }


    public static function get_statuses() {

        $sql = "SELECT `status` FROM `"._DB_PREFIX."file` GROUP BY `status` ORDER BY `status`";
        $statuses = array();
        foreach(qa($sql) as $r){
            $statuses[$r['status']] = $r['status'];
        }
        return $statuses;
    }



    public static function link_public_view($file_id,$h=false){
        if($h){
            return md5('s3cret7hash '._UCM_FOLDER.' '.$file_id);
        }
        if(_REWRITE_LINKS){
            return full_link(_EXTERNAL_TUNNEL_REWRITE.'m.file/h.download/i.'.$file_id.'/hash.'.self::link_public_view($file_id,true));
        }else{
            return full_link(_EXTERNAL_TUNNEL.'?m=file&h=download&i='.$file_id.'&hash='.self::link_public_view($file_id,true));
        }
    }

    public function external_hook($hook){
        switch($hook){
            case 'download':
                @ob_end_clean();
                $file_id = (isset($_REQUEST['i'])) ? (int)$_REQUEST['i'] : false;
                $hash = (isset($_REQUEST['hash'])) ? trim($_REQUEST['hash']) : false;
                if($file_id && $hash){
                    $correct_hash = $this->link_public_view($file_id,true);
                    if($correct_hash == $hash){
                        // all good to print a receipt for this payment.
                        $file_data = $this->get_file($file_id);
                        if(is_file($file_data['file_path'])){
                            header("Pragma: public");
                            header("Expires: 0");
                            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
                            header("Cache-Control: private",false);
                            header("Content-type: ".dtbaker_mime_type($file_data['file_name'],$file_data['file_path']));
                            header("Content-Disposition: attachment; filename=\"".$file_data['file_name']."\";");
                            header("Content-Transfer-Encoding: binary");
                            header("Content-Length: ".filesize($file_data['file_path']));
                            readfile($file_data['file_path']);
                        }else{
                            echo 'Not found';
                        }
                    }
                }
                exit;
                break;
        }
    }

    public static function customer_id_changed($old_customer_id, $new_customer_id) {
        $old_customer_id = (int)$old_customer_id;
        $new_customer_id = (int)$new_customer_id;
        if($old_customer_id>0 && $new_customer_id>0){
            $sql = "UPDATE `"._DB_PREFIX."file` SET customer_id = ".$new_customer_id." WHERE customer_id = ".$old_customer_id;
            query($sql);
        }
    }

    public static function delete_files($owner_table, $owner_id) {
        if(!self::can_i('delete','Files'))return;
        $sql = "DELETE FROM `"._DB_PREFIX."file` WHERE owner_table = '".mysql_real_escape_string($owner_table)."' AND owner_id = '".mysql_real_escape_string($owner_id)."'";
        query($sql);
    }


    public function get_upgrade_sql(){
        $sql = '';
        $fields = get_fields('file');
        if(!isset($fields['pointers'])){
            $sql .= 'ALTER TABLE  `'._DB_PREFIX.'file` ADD `pointers` varchar(255) NULL;';
        }
        if(!isset($fields['file_url'])){
            $sql .= 'ALTER TABLE  `'._DB_PREFIX.'file` ADD `file_url` varchar(255) NOT NULL DEFAULT \'\';';
        }
        $res = qa1("SHOW TABLES LIKE '"._DB_PREFIX."file_comment'");
        if(!$res || !count($res)){
            $sql .= 'CREATE TABLE `'._DB_PREFIX.'file_comment` (
  `file_comment_id` int(11) NOT NULL auto_increment,
    `file_id` int(11) NOT NULL,
    `comment` text NOT NULL,
    `date_created` datetime NOT NULL,
    `date_updated` datetime NOT NULL,
    `create_user_id` int(11) NOT NULL,
    PRIMARY KEY  (`file_comment_id`)
    )  ENGINE=InnoDB  DEFAULT CHARSET=utf8;';
        }
        $res = qa1("SHOW TABLES LIKE '"._DB_PREFIX."file_notification'");
        if(!$res || !count($res)){
            $sql .= 'CREATE TABLE `'._DB_PREFIX.'file_notification` (
    `file_notification_id` int(11) NOT NULL auto_increment,
    `file_id` int(11) NOT NULL DEFAULT \'0\',
    `user_id` int(11) NOT NULL DEFAULT \'0\',
    `email_id` int(11) NOT NULL DEFAULT \'0\',
    `view_time` int(11) NOT NULL DEFAULT \'0\',
    `notification_type` int(11) NOT NULL DEFAULT \'0\',
    `date_created` datetime NOT NULL,
    `date_updated` datetime NOT NULL,
    `create_user_id` int(11) NOT NULL,
    PRIMARY KEY  (`file_notification_id`),
    INDEX (  `file_id` ,  `user_id` ,  `view_time` )
    )  ENGINE=InnoDB  DEFAULT CHARSET=utf8;';
        }

        return $sql;
    }

    public function get_install_sql(){
        ob_start();
        ?>

    CREATE TABLE IF NOT EXISTS `<?php echo _DB_PREFIX;?>file` (
    `file_id` int(11) NOT NULL AUTO_INCREMENT,
    `customer_id` int(11) NULL,
    `job_id` int(11) NULL,
    `owner_id` int(11) NULL,
    `owner_table` varchar(80) NULL,
    `file_path` varchar(100) NULL,
    `file_url` varchar(255) NOT NULL DEFAULT '',
    `file_name` varchar(100) NULL,
    `file_time` int(11) NULL,
    `status` varchar(100) NULL,
    `pointers` varchar(255) NULL,
    `description` TEXT NOT NULL,
    `date_created` datetime NOT NULL,
    `date_updated` datetime NULL,
    `create_user_id` int(11) NOT NULL,
    `update_user_id` int(11) NULL,
    `create_ip_address` varchar(15) NOT NULL,
    `update_ip_address` varchar(15) NULL,
    PRIMARY KEY (`file_id`),
    KEY `group_id` (`owner_id`),
    KEY `group_key` (`owner_table`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

    CREATE TABLE `<?php echo _DB_PREFIX; ?>file_notification` (
    `file_notification_id` int(11) NOT NULL auto_increment,
    `file_id` int(11) NOT NULL DEFAULT '0',
    `user_id` int(11) NOT NULL DEFAULT '0',
    `email_id` int(11) NOT NULL DEFAULT '0',
    `view_time` int(11) NOT NULL DEFAULT '0',
    `notification_type` int(11) NOT NULL DEFAULT '0',
    `date_created` datetime NOT NULL,
    `date_updated` datetime NOT NULL,
    `create_user_id` int(11) NOT NULL,
    PRIMARY KEY  (`file_notification_id`),
    INDEX (  `file_id` ,  `user_id` ,  `view_time` )
    )  ENGINE=InnoDB  DEFAULT CHARSET=utf8;

    CREATE TABLE `<?php echo _DB_PREFIX; ?>file_comment` (
    `file_comment_id` int(11) NOT NULL auto_increment,
    `file_id` int(11) NOT NULL,
    `comment` text NOT NULL,
    `date_created` datetime NOT NULL,
    `date_updated` datetime NOT NULL,
    `create_user_id` int(11) NOT NULL,
    PRIMARY KEY  (`file_comment_id`)
    )  ENGINE=InnoDB  DEFAULT CHARSET=utf8;

    <?php
        return ob_get_clean();
    }

}

