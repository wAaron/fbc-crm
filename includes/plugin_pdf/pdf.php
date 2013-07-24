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


class module_pdf extends module_base{
	
	var $links;

    public static function can_i($actions,$name=false,$category=false,$module=false){
        if(!$module)$module=__CLASS__;
        return parent::can_i($actions,$name,$category,$module);
    }
	public static function get_class() {
        return __CLASS__;
    }
	function init(){
        $this->version=2.122;
        // 2.1 - added arialuni ttf file
        // 2.11 - more options for pdf generation
        // 2.12 - better unicode configuration
        // 2.121 - 2013-05-27 - space after slash fix
        // 2.122 - 2013-05-27 - jpeg image quality improvement
		$this->links = array();
		$this->module_name = "pdf";
		$this->module_position = 8882;
	}

}


function convert_html2pdf($html,$pdf){

    // start conversion
    //require_once('includes/html2ps/dave.php');

    ini_set('error_reporting',E_ERROR);
    ini_set("display_errors",false);

    require_once('html2ps/config.inc.php');
    require_once(HTML2PS_DIR.'pipeline.factory.class.php');

    set_time_limit(120);
    parse_config_file(HTML2PS_DIR.'html2ps.config');


    global $g_font_resolver_pdf;
//    print_r($g_font_resolver_pdf->ttf_mappings); exit;
    $g_font_resolver_pdf->ttf_mappings['Arial Unicode MS'] = module_config::c('pdf_unicode_font','arialuni.ttf');

    /**
     * Handles the saving generated PDF to user-defined output file on server
     */
    if(!class_exists('MyDestinationFile',false)){
        class MyDestinationFile extends Destination {
            /**
             * @var String result file name / path
             * @access private
             */
            var $_dest_filename;

            function MyDestinationFile($dest_filename) {
                $this->_dest_filename = $dest_filename;
            }

            function process($tmp_filename, $content_type) {
                copy($tmp_filename, $this->_dest_filename);
            }
        }

        class MyFetcherLocalFile extends Fetcher {
            var $_content;

            function MyFetcherLocalFile($file) {
                $this->_content = file_get_contents($file);
            }

            function get_data($dummy1) {
                return new FetchedDataURL($this->_content, array(), "");
            }

            function get_base_url() {
                return "http://".$_SERVER['HTTP_HOST'].'/';
            }
        }


        /**
         * Runs the HTML->PDF conversion with default settings
         *
         * Warning: if you have any files (like CSS stylesheets and/or images referenced by this file,
         * use absolute links (like http://my.host/image.gif).
         *
         * @param $path_to_html String path to source html file.
         * @param $path_to_pdf  String path to file to save generated PDF to.
         */
        function convert_to_pdf($path_to_html, $path_to_pdf) {
            $pipeline = PipelineFactory::create_default_pipeline("", "");
            // Override HTML source
            $pipeline->fetchers[] = new MyFetcherLocalFile($path_to_html);

            //$filter = new PreTreeFilterHeaderFooter("HEADER", "FOOTER");
            //$pipeline->pre_tree_filters[] = $filter;

            // Override destination to local file
            $pipeline->destination = new MyDestinationFile($path_to_pdf);

            $baseurl = "";
            $media = Media::predefined(module_config::c('pdf_media_size','A4'));
            $media->set_landscape(false);
            $media->set_margins(array('left'   => module_config::c('pdf_media_left','0'),
                'right'  => module_config::c('pdf_media_right','0'),
                'top'    => module_config::c('pdf_media_top','0'),
                'bottom' => module_config::c('pdf_media_bottom','0')));
            $media->set_pixels(module_config::c('pdf_media_pixels','1010'));

            global $g_config;
            $g_config = array(
                'compress'     => true,
                'cssmedia'     => 'screen',
                'scalepoints'  => '1',
                'renderimages' => true,
                'renderlinks'  => true,
                'renderfields' => true,
                'renderforms'  => false,
                'mode'         => 'html',
                'encoding'     => 'UTF-8',
                'debugbox'     => false,
                'pdfversion'    => '1.4',
                'draw_page_border' => false,
                'media' => module_config::c('pdf_media_size','A4'),
            );
            $pipeline->configure($g_config);
            //$pipeline->add_feature('toc', array('location' => 'before'));
            $pipeline->process($baseurl, $media);
        }
    }

    convert_to_pdf($html, $pdf);


    return $pdf;
}