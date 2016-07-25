<?php

namespace Booking\Listeners;

use Booking\Repository\RepositoryInterface;
use Ratchet\ConnectionInterface;

class BookingListeners implements BookingListenersInterface
{
	// Всі вибрані місця
	private $selectSeat 			= array();
	
	// Користувачі, які вибрали дане місце, і проходять процес бронювання
	private $selectSeatAndMember	= array();
	
	// Вибрані місця на кожного користувача
	private $memberAndSelectSeat	= array();
	
	private $repository 			= array();
	
	public function __construct(RepositoryInterface $repository){
		$this->repository = $repository;
	}
	
	public function on(ConnectionInterface $conn,$data){
		
		 $currClient = $this->repository->getClientByConnection($conn);
		
		 if(isset($data->name)){
			switch($data->name){
				case 'processBooking':
					//!!!!!!!!!!!! перевіряти $data->data->booking
					if(isset($data->data->status) && $data->data->status >= 0)
						$status = (int) $data->data->status;
					
					
					
					if(isset($status) && $status === 0)
						$statusSeat = $this->removeSelectSeat($data->data->booking,$conn);
					else if(isset($status) && $status === 1)
						$statusSeat = $this->createSelectSeat($data->data->booking,$conn);
					else if(isset($status) && $status === 2)
						$statusSeat = $this->removeSelectSeatFromMemeber($data->data->booking);//TODO: перевырити,чи саме цей мембер зробив бронювання!!!!
					
					if(isset($statusSeat['removeAll']) && $statusSeat['removeAll'] === true)
						$removeAll = true;
					else
						$removeAll = false;
					//var_dump($data->data);
					if($statusSeat !==false){
						foreach ($this->repository->getClients() as $client){
							$my = false;
							if($currClient === $client)
								$my = true;
							
							
							$emit 	= array(
											'booking'	=> $statusSeat['booking'],
											'my'		=> $my,
											'status'	=> $status,
											'remove'	=> $removeAll,
										);
							$client->sendMsg('processBooking',$emit);
							
							
						}
					}else{
						$msg = $this->error('wrong data');//!!!
						$currClient->sendMsg('error',array('msg' => $msg));
					}
					
				break;
				case 'getProcessBooking':
					$process 	= $this->getSelectSeats();
					$processMy 	= $this->getmemberAndSelectSeat($conn);
					
					$currClient->sendMsg('getProcessBooking',array('all' => $process,'my' => $processMy));
					
					//$this->getCookie($conn);
					//var_dump();
				break;
				default:
					$msg = $this->error('not method');//!!!
					$currClient->sendMsg('error',$msg);
				break;
			}
		} 
	}
	
	private function removeSelectSeatFromMemeber($data){
		$seatId 		= (int) $data->seat->seatId;
		if($seatId <=0)
			return false;
		
		if(isset($this->selectSeat[$seatId])){
			
			
			
			foreach($this->selectSeatAndMember[$seatId] AS $cook => $val){
				if(isset($this->memberAndSelectSeat[$cook][$seatId]))
					unset($this->memberAndSelectSeat[$cook][$seatId]);

				if(count($this->memberAndSelectSeat[$cook]) == 0)
					unset($this->memberAndSelectSeat[$cook]); 
						
			}
			unset($this->selectSeatAndMember[$seatId]); 
			unset($this->selectSeat[$seatId]); 
			
		}
		return array('booking'=>$data,'removeAll' => true);	
	}
	
	public function removeSelectSeat($data,ConnectionInterface $conn){
		
		$cookie 	= $this->getCookie($conn);
		$seatId 		= (int) $data->seat->seatId;
		
		if($seatId <= 0 || is_null($cookie))
			return false;
		
		$removeAll = false;
		
		if(!isset($this->selectSeat[$seatId])){
			return array('booking'=>$data,'removeAll' => true);	
		}else{
			
			
			
			
			if(count($this->selectSeatAndMember[$seatId]) == 1){
				return $this->removeSelectSeatFromMemeber($data);
			}else{
				if(isset($this->selectSeatAndMember[$seatId][$cookie]))
					unset($this->selectSeatAndMember[$seatId][$cookie]); 
				
				if(isset($this->memberAndSelectSeat[$cookie][$seatId]))
					unset($this->memberAndSelectSeat[$cookie][$seatId]); 
			
				if(count($this->memberAndSelectSeat[$cookie]) == 0)
					unset($this->memberAndSelectSeat[$cookie]);
			}
				
		}
			
		return array('booking'=>$data,'removeAll' => $removeAll);	
	}
	
	public function createSelectSeat($data,ConnectionInterface $conn){
		$seatId 	= (int) $data->seat->seatId;
		$cookie 	= $this->getCookie($conn);
		
		if($seatId <=0 || is_null($cookie))
			return false;
			
		$this->selectSeat[$seatId] = $data; 
		$this->selectSeat[$seatId]->time = time(); 
		
			
		if(!isset($this->selectSeatAndMember[$seatId][$cookie]))
			$this->selectSeatAndMember[$seatId][$cookie] = true; 
		
		if(!isset($this->memberAndSelectSeat[$cookie]))
			$this->memberAndSelectSeat[$cookie] = array(); 
		
		if(!isset($this->memberAndSelectSeat[$cookie][$seatId]))
			$this->memberAndSelectSeat[$cookie][$seatId] = true; 
		
		return array('booking'=>$data);
	}
	
	private function getmemberAndSelectSeat(ConnectionInterface $conn){
		$cookie 	= $this->getCookie($conn);
		//var_dump($cookie);
		//var_dump($this->memberAndSelectSeat);
		
		if(is_null($cookie))
			return array();
		
		$temp = array();
		if(isset($this->memberAndSelectSeat[ $cookie ])){
			foreach($this->memberAndSelectSeat[ $cookie ] AS $seatId => $val){
				$temp[ $seatId ] = $this->selectSeat[$seatId];
			}
		}
		 
		 return $temp;
	}
	
	public function getSelectSeats(){
		$time = time() - (15*1);
		foreach($this->selectSeat AS $seatId => $data){
			if($this->selectSeat[$seatId]->time < $time){
				$temp = $this->removeSelectSeatFromMemeber($data);
			
				if($temp !== false){
					foreach ($this->repository->getClients() as $client){
						
						$emit 	= array(
										'booking'	=> $temp['booking'],
										'my'		=> false,
										'status'	=> 0,
										'remove'	=> true,
									);
						$client->sendMsg('processBooking',$emit);
						
						
					}
				}
			}
		}
		
		return $this->selectSeat;
	}
	
	private function getCookie($conn){
		$cookie = $conn->WebSocket->request->getCookie('ci_session_test');
		if( !is_null($cookie) && is_string($cookie) && preg_match('/^[0-9a-f]{40}$/', $cookie) )
			return $cookie;
		else
			return false;
	}
	private function error($name){
		  return $name;
	}
  

}

?>
