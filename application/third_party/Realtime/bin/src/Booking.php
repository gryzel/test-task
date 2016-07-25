<?php

/**
 * Author : Kishor Mali
 * Filename : Booking.php
 * 
 * Class : Booking
 * This class is used for accepting and broadcasting the socket request
 */

namespace Booking;

use Booking\Repository\Repository;

use Booking\Listeners\BookingListeners;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class Booking implements MessageComponentInterface
{
  	protected $repository;
  	protected $bookingListeners;

	/**
	 * Default constructor of the class
	 */
  	public function __construct()
  	{
    	$this->repository 		= new Repository;
    	$this->bookingListeners = new BookingListeners($this->repository);
  	}
	
	/**
	 * This function is used to add the connected machine to queue
	 * @param {object} $conn : Connection interface object
	 */
  	public function onOpen(ConnectionInterface $conn)
  	{
    	$this->repository->addClient($conn);
		
  	}

  	public function onClose(ConnectionInterface $conn)
  	{
    	$this->repository->removeClient($conn);
  	}

  	public function onError(ConnectionInterface $conn, \Exception $e)
  	{
    	echo "The following error occured : ". $e->getMessage();
		$client = $this->repository->getClientByConnection($conn);
    	if($client !== null)
    	{
      		$client->getConnection()->close();
      		$this->repository->removeClient($conn);
		}
  	}

  	public function onMessage(ConnectionInterface $conn , $msg)
  	{
    	$data = $this->parseMessage($msg);
		$this->bookingListeners->on($conn,$data);
	}

  	private function parseMessage($msg)
  	{
    	return json_decode($msg);
  	}
}

