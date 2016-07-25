<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Main extends MY_Controller {
	
	function __construct() {
		parent::__construct();
		$this->load->model("Main_model");
	}
	 
	
	function index()
	{	
		$this->out_array['stadiums'] = $this->Main_model->getStadium();
		$this->_out("main");
	}
	
	function populateDatabase()
	{	
		$this->Main_model->populateDatabase();
		
	}
	
	
	
	
	function getSector(){	
		
		$id = (int)$this->input->post('id',true);
		echo json_encode($this->Main_model->getSector($id));
	}
	
	function getRow(){	
		$id = (int)$this->input->post('id',true);
		echo json_encode($this->Main_model->getRow($id));
	}
	
	
	
	function booking(){	
		$seat 		= $this->input->post('seat',true);
		$afterError = (bool)$this->input->post('afterError',true);
		echo json_encode($this->Main_model->booking($seat,$afterError));
	}
	
	function bookingCencel(){	
		$seat 		= $this->input->post('seat',true);
		echo json_encode($this->Main_model->bookingCencel($seat));
	}
	
	function getBookingMember(){	
		echo json_encode($this->Main_model->getBookingMember());
	}
}

?>