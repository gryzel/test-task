<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class MY_Controller extends CI_Controller {
	public $out_array	= array();

    public function __construct(){
		parent::__construct();
		
		
		$this->load->helper('url');
		$this->load->library('session');
		
		
		$this->out_array['settings'] 				= array();
		$this->out_array['settings']['error'] 		= '/';
		$this->out_array['settings']['basePath'] 	= '/';
		$this->out_array['settings']['pathAssets'] 	= '/assets/';
		$this->out_array['settings']['pathViews'] 	= 'blocks/';
		$this->out_array['settings']['pathImg'] 	= '/';
		
	}
	
	
	
	

	public function _out($include ){

		$this->load->view($include,$this->out_array);
	}

}
