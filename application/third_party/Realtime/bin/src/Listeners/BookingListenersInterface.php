<?php

namespace Booking\Listeners;

use Booking\Repository\RepositoryInterface;
use Ratchet\ConnectionInterface;

interface BookingListenersInterface
{
	// Обробляє запити від користувачів
	public function on(ConnectionInterface $conn,$data);
	
	// Створює обране місце
	public function createSelectSeat($data,ConnectionInterface $conn);
	
	// Повертає обрані місця
	public function getSelectSeats();
	
	// Видаляє обрані місця
	public function removeSelectSeat($data,ConnectionInterface $conn);
	
	

 
}

?>
