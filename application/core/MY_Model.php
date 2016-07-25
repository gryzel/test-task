<?php 
defined('BASEPATH') OR exit('No direct script access allowed');
class MY_Model extends CI_Model {
	//public $out_array	= array();

	public $msg = array(
			'success' 	=> array(),
			'info' 		=> array(),
			'warning' 	=> array(),
			'danger' 	=> array(),
			);
    public function __construct(){
		parent::__construct();
		
	}
	

	public function getEmptyRow($table){
		$data = $this->db->query("SHOW FULL COLUMNS FROM `".$table."` ")->result_array();
		$return = array();
		foreach($data AS $key => $val){
			$return[ $val['Field'] ] = '';
		}
		return $return;
	}
	
	public function getOneElementFromArray($data,$get = 'id'){
		$el = array();
			if(!is_array($id_seat)){
				$c = count($data);
				
				for($i = 0; $i < $c; $i++ )
					$el[] = $data[ $i ][ $get ];
			}
		return $el;
	}
	public function getMsg(){
		return $this->msg;
		
	}
	
	public function setMsg($t,$msg){
		$type ='';
		switch($t){
			case 'd' || 'danger': 
			$type = 'danger';
		break;
			
			case 'i' || 'info':
			$type = 'info';
		break;
		
			case 'w' || 'warning':
			$type = 'warning';
		break;
			
			case 's' || 'success':
			default:
			$type = 'success';
		break;
		}
		
		$this->msg[$type][] = $msg;
		
	}
}