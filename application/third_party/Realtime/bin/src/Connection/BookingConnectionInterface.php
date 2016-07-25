<?php

namespace Booking\Connection;

interface BookingConnectionInterface
{
  public function getConnection();

  public function sendMsg($name,$data);
}

?>
