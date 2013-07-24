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


class module_debug extends module_base{

	public static $debug = array();
    public static $show_debug = false;
    public static $start_time = 0;
    public static function can_i($actions,$name=false,$category=false,$module=false){
        if(!$module)$module=__CLASS__;
        return parent::can_i($actions,$name,$category,$module);
    }
	public static function get_class() {
        return __CLASS__;
    }
	public function init(){
        self::$start_time = microtime(true);
		$this->module_name = "debug";
		$this->module_position = 1;
	}

    public static function log($data){
        if($data && _DEBUG_MODE){
            $data['time'] = substr((microtime(true) - self::$start_time),0,5);
            $data['trace'] = debug_backtrace();
            if(isset($data['trace'][0])){
                unset($data['trace'][0]);
            }
            self::$debug[] = $data;
        }
    }
    public static function push_to_parent(){
        if(_DEBUG_MODE){
            ?>
            <script type="text/javascript">
                var tbl = $('#system_debug_data tbody');
                if(typeof tbl[0] != 'undefined'){
                    $(tbl).append('<tr><td colspan="5"><strong>Debug from: <?php
                    echo substr($_SERVER['REQUEST_URI'],0,40).'...';
                    ;?></strong></td></tr>');
                    $(tbl).append('<?php
                        ob_start();
                        self::debug_list();
                        $html = ob_get_clean();
                        $html = preg_replace('/\s+/',' ',$html);
                        echo addcslashes($html,"'");
                    ;?>');
                }
            </script>
            <?php
        }
    }
    public static function debug_list(){
        $hash = md5(microtime(true));
        $x=1;
        ob_start();
        foreach(self::$debug as $debug){ ?>
            <tr>
                <td>
                    <?php echo isset($debug['time']) ? $debug['time'] : '??';?>
                </td>
                <td>
                    <?php echo $x++;?>
                </td>
                <td>
                    <?php echo isset($debug['title']) ? $debug['title'] : 'NA';?>
                </td>
                <td>
                    <?php echo isset($debug['data']) ? $debug['data'] : 'NA';?>
                </td>
                <td>
                    <?php echo isset($debug['file']) ? $debug['file'] : 'NA';?>
                </td>
                <td>
                    <?php if(module_config::c('debug_show_data',0)){ ?>
                    <a href="#" onclick="$('#trace_<?php echo $hash.$x;?>').toggle(); return false;">Show &raquo;</a>
                    <div id="trace_<?php echo $hash.$x;?>" style="display:none; position:absolute; background-color:#CCC; font-size:10px;">
                    <pre><?php echo nl2br(var_export($debug['trace'],true));?></pre>
                    </div>
                    <?php } ?>
                </td>
            </tr>
        <?php
        }
        echo preg_replace('#\s+#',' ',ob_get_clean());
    }
    public static function print_heading(){
        if(self::$show_debug){
            ?>
        <link rel="stylesheet" href="<?php echo _BASE_HREF;?>css/styles.css?ver=3" type="text/css" />
        <link type="text/css" href="<?php echo _BASE_HREF;?>css/smoothness/jquery-ui-1.9.2.custom.min.css" rel="stylesheet" />
        <script type="text/javascript" src="<?php echo _BASE_HREF;?>js/jquery-1.8.3.min.js"></script>
            <?php
        }
        ?>
        <div id="system_debug" style="position:absolute; z-index:90000; background:#FFF; border:1px solid #CCC;">
            <a href="#" onclick="$('#system_debug_data').toggle(); return false;">View Debug &raquo;</a>
            <div id="system_debug_data" style="<?php echo (!self::$show_debug)?'display:none;':'';?>" class="tableclass tableclass_rows">
                <h3>Debug Information:</h3>
                <table width="100%" cellpadding="4">
                    <thead>
                    <tr>
                        <th>Time</th>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Data</th>
                        <th>File</th>
                        <th>Trace</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    self::debug_list();
                    ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
    }

}

