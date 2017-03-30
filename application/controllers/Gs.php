<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Gs extends CI_Controller {

	var $gsconfig = array();

	function __construct() {

		parent::__construct();

        $this->load->model('ext_configs');
    }

	public function index()
	{
		echo "string";
	}

	public function req_file($filename)
	{
		if ( preg_match('/cfg([0-f]+)\.xml/', $filename, $match) )
		{
			$mac = strtolower($match[1]);
			$this->output
				->set_content_type('application/xml')
				->set_output($this->ext_configs->xmlconfig($mac));
			return;
		}

		if ( preg_match('/gxp1600fw\.bin/', $filename, $match) )
		{
			$this->output
				->set_content_type('application/octet-stream')
				->set_output(file_get_contents('data/fw/gxp1600fw.bin'));

			return;
		}
		
		show_404();
	
	}

}
