<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Main_model extends MY_Model
{
	private function getMemberData(){
		return md5($this->session->session_id);
	}
	// Повртає всі стадіони
	public function getStadium(){
		$query = $this->db->get('stadium');
		
		if($query->num_rows() > 0){
			return array('status' => true,'data' => $query->result_array());
		}else{
			return array('status' => false,'error' => 'пусто!');
		}
	}
	// //Сектор в стадіоні
	public function getSector($id_stadium){
		$query = $this->db->query('
			SELECT 
				s.* 
			FROM 
				sector AS s,
				sector2stadium AS s2s
			WHERE 
				s2s.id_stadium 	= '.$id_stadium.' AND 
				s2s.id_sector 	= s.id
		');
		
		if($query->num_rows() > 0){
			return array('status' => true,'data' => $query->result_array());
		}else{
			return array('status' => false,'error' => 'пусто!');
		}
	}
	// Повертає ряди для сектора
	public function getRow($id_sector){
		$query = $this->db->query('
			SELECT 
				r.* 
			FROM 
				row AS r,
				row2sector AS r2s
			WHERE 
				r2s.id_sector 	= '.$id_sector.' AND 
				r2s.id_row 		= r.id
		');
		$countRow = $query->num_rows();
		if($countRow > 0){
			$row 		= $query->result_array();
			$id 		= array();
			$rowAndSeat = array();
			
			for($i = 0; $i < $countRow; $i++ ){
				$id[] = $row[ $i ]['id'];
				$rowAndSeat[ $row[ $i ]['id'] ] 		= $row[ $i ];
				$rowAndSeat[ $row[ $i ]['id'] ]['seat'] = array();
			}
			$seats = $this->getSeats($id);
			if($seats['status'] != false){
				
				foreach($seats['data'] AS $key => $seat){
					
					$rowAndSeat[ $seat['id_row'] ]['seat'][ $seat['id'] ] = $seat;
				}
				
				return array('status' => true,'data' =>array('rowAndSeat' =>$rowAndSeat,'count'=>$this->getSeatsBooked($id) ));
			}else{
				return array('status' => false,'error' => 'пусто in seat');
			}
			
		}else{
			return array('status' => false,'error' => 'пусто!');
		}
	}
	//Повертає місця для рядів, а також заброньовані місця
	public function getSeats($id_seat){
		//Check seesion data!!!!!
		$session_id = $this->getMemberData();
		if(!is_array($id_seat))
			return array('status' => false,'error' => 'not array');
		$query = $this->db->query('
			SELECT 
				s.*,
				IF(sb.member_cookie = "'.$session_id.'","1","0") AS my,
				IF(sb.member_cookie,"1","0") AS other,
				s2r.id_row 
			FROM 
				seat AS s,
				seat2row AS s2r
				LEFT JOIN seatbooked AS sb ON (s2r .id_seat  = sb.id_seat)
			WHERE 
				s2r.id_row 	IN  ('.implode(', ',$id_seat).') AND 
				s2r.id_seat	= s.id
		');
		/* var_dump('
			SELECT 
				s.*,
				IF(sb.member_cookie = "'.$session_id.'","1","0") AS my,
				IF(sb.member_cookie,"1","0") AS other,
				s2r.id_row 
			FROM 
				seat AS s,
				seat2row AS s2r
				LEFT JOIN seatbooked AS sb ON (s2r .id_seat  = sb.id_seat)
			WHERE 
				s2r.id_row 	IN  ('.implode(', ',$id_seat).') AND 
				s2r.id_seat	= s.id
		' );*/
		if($query->num_rows() > 0){
			return array('status' => true,'data' => $query->result_array());
		}else{
			return array('status' => false,'error' => 'пусто!');
		}
	}
	
	private function getSeatsBooked($id_seat){
		//Check seesion data!!!!!
		$session_id = $this->getMemberData();
		if(!is_array($id_seat))
			return array('status' => false,'error' => 'not array');
		$query = $this->db->query('
			SELECT 
				COUNT(*) AS c
			FROM 
				seat AS s,
				seat2row AS s2r,
				seatbooked AS sb
			WHERE 
				s2r.id_row 	IN  ('.implode(', ',$id_seat).') AND 
				s2r.id_seat	= s.id AND
				s2r .id_seat  = sb.id_seat
		');
	
	
			return $query->row()->c;
		
	}
	// ЗАбиває базу тестовими значеннями
	public function populateDatabase(){
		
		$this->db->query('TRUNCATE TABLE  `row`');
		$this->db->query('TRUNCATE TABLE  `row2sector`');
		$this->db->query('TRUNCATE TABLE  `seat`');
		$this->db->query('TRUNCATE TABLE  `seat2row`');
		$this->db->query('TRUNCATE TABLE  `sector`');
		$this->db->query('TRUNCATE TABLE  `sector2stadium`');
		$this->db->query('TRUNCATE TABLE  `stadium`');
		$count = array(
			'sector' 	=> 10,
			'row' 		=> 50,
			'seat' 		=> 30
		);
		
		$this->db->select('COUNT(*) AS c',false);
		$query = $this->db->get('stadium');
		$countStadium = $query->row()->c;
		
		$stadium = array(
			'name' => 'Стадіон №'.($countStadium + 1)
		);
		$this->db->insert('stadium',$stadium);
		$id_stadium = $this->db->insert_id();

		$sector2stadium = array();
		for($sector = 1;$sector <= $count['sector'];$sector++){
			
			$this->db->insert('sector',array('number' => $sector));
			$id_sector 			= $this->db->insert_id();
			$sector2stadium[] 	= '(NULL, "'. $id_sector .'", "'. $id_stadium .'")';
			
			$row2sector = array();
			for($row = 1;$row <= $count['row'];$row++){
				$this->db->insert('row',array('number' => $row));
				$id_row			= $this->db->insert_id();
				$row2sector[] 	= '(NULL, "'. $id_row .'", "'. $id_sector .'")';
				
				$seat2row = array();
				for($seat = 1;$seat <= $count['seat'];$seat++){
					$this->db->insert('seat',array('number' => $seat));
					$id_seat	= $this->db->insert_id();
					$seat2row[] = '(NULL, "'. $id_seat .'", "'. $id_row .'")';
				}
				$this->db->query('INSERT INTO seat2row (`id`, `id_seat`, `id_row`) VALUES'.implode(', ',$seat2row).'');
			}
			$this->db->query('INSERT INTO row2sector (`id`, `id_row`, `id_sector`) VALUES'.implode(', ',$row2sector).'');
		}
		$this->db->query('INSERT INTO sector2stadium (`id`, `id_sector`, `id_stadium`) VALUES'.implode(', ',$sector2stadium).'');
		//var_dump();die;
	}
	//Відміна бронювання
	public function bookingCencel($data){
		//Check seesion data!!!!!
		$session_id = $this->getMemberData();
		
		$errorData 	= array();
		$cencelData = array();
		
		if(count($data)){
			foreach($data AS $seatId => $val){
				if( $seatId > 0){
					$datete = array(
									'id_seat'		=> intval($seatId),
									'member_cookie'	=>	$session_id
									);
					if($this->db->delete('seatbooked',$datete )){
						$cencelData[ $seatId ] = array('seatId' => intval($seatId));
					}else{
						$errorData[ $seatId ] = array('seatId' => intval($seatId));
					}
				}
			}
			
			return array('status' => true,'data' => array('errorData' =>$errorData,'cencelData' => $cencelData));
		}else{
			return array('error' => '1');
		}
	}
	
	// Процес бронювання
	public function booking($data,$afterError = false){
		//Check seesion data!!!!!
		$session_id = $this->getMemberData();
		//var_dump($session_id);
		$setDataId 	= array();
		$setData 	= array();
		$accessData = array();
		$errorData 	= array();
		//print_r($data);
		if(count($data)){
			foreach($data AS $seatId => $val){
				if( $seatId > 0){
					$setDataId[ $seatId ] = $seatId; 
					$setData[$seatId] = array(
										'id_seat'		=> intval($seatId),
										'member_cookie'	=>	$session_id
									);
				}
					
			}
			
			$query = $this->db->query('
				SELECT 
					* 
				FROM 
					seatbooked
				WHERE 
					id_seat IN ( '.implode(', ',$setDataId).' )
			');
			if(count($setDataId) > 0){
				if($query->num_rows() > 0){
					$temp = $query->result_array();
					
					foreach($temp AS $key => $val){
						unset($setData[ $val['id_seat'] ]);
						unset($setDataId[ $val['id_seat'] ]);
						$errorData[ $val['id_seat'] ] = array('seatId' => $val['id_seat']);
						
					}
					
					if(count($setDataId) > 0){
						foreach($setData AS $key => $val){
							$accessData[ $val['id_seat'] ] = array(
													'seatId' 		=> $val['id_seat'],
												);		
						}						
					}					
					
					
					if($afterError === false)
						return array('status' => false,'error' => '1','data' => array('errorData' =>$errorData,'allowData' =>$accessData,'addData' => array()));
					else{
						if(count($setDataId) > 0){
							return $this->addBooking($setData);
						}else{
							return array('status' => false,'error' => '2','data' => array('errorData' =>$errorData,'allowData' =>$accessData,'addData' => array()));
						}
					}
				}else{
					return $this->addBooking($setData);
				}
			}else{
				return array('error' => '4');
			}
		}else{
			return array('error' => '5');
		}
	}
	
	// Додає місця в зарезерервовані
	private function addBooking($data){
		$addData = array();
		$errorData 	= array();
		
		foreach($data AS $key => $val){
			// якщо,якимось чином,магічним,додасться запис про бронювання,то індекс унік не дозволить вставити бронювання,або просто помилка запиту
			if($this->db->insert('seatbooked',$val)){
				$id_booking = $this->db->insert_id();
				$addData[ $val['id_seat'] ] = array(
													'id_booking' 	=> $id_booking,
													'seatId' 		=> $val['id_seat'],
												);
				
			}else{
				$errorData[ $val['id_seat'] ] = array('seatId' => $val['id_seat']);
			}
		}
		
		if(count($errorData) > 0){
			return array('status' => false,'error' => '3','data' => array('errorData' =>$errorData, 'allowData' =>array(),'addData' => $addData));
		}else{
			return array('status' => true,'data' => array('errorData' =>$errorData,'allowData' =>array(),'addData' => $addData));
		}
	}
	
	// Зарезервовані місця
	public function getBookingMember(){
		//Check seesion data!!!!!
		$session_id = $this->getMemberData();
		
		
		$query = $this->db->query('
			SELECT 
				seat.id AS seatId,
				seat.number AS seat,
				row.number AS row,
				sector.number AS sector,
				stadium.name AS stadium
				
			FROM 
				seat,
				seat2row,
				row,
				row2sector,
				sector,
				sector2stadium,
				stadium,
				seatbooked AS sb 
			WHERE 
				sb.member_cookie = "'.$session_id.'" AND 
				
				sb.id_seat 	= seat.id AND
				seat.id 	= seat2row.id_seat AND
				
				seat2row.id_row 	= row.id AND
				row.id 	= row2sector.id_row AND
				
				row2sector.id_sector 	= sector.id AND
				sector.id 	= sector2stadium.id_sector AND
				
				sector2stadium.id_stadium 	= stadium.id
		');
		
		if($query->num_rows() > 0){
			return array('status' => true,'data' => $query->result_array());
		}else{
			return array('status' => false,'error' => 'пусто!');
		}
	}
}