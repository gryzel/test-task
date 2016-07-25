<?php

namespace Booking\Connection;

use Booking\Repository\RepositoryInterface;
use Ratchet\ConnectionInterface;

class BookingConnection implements BookingConnectionInterface
{
  private $connection;
  private $repository;

  public function __construct(ConnectionInterface $conn,RepositoryInterface $repository)
  {
	  //var_dump($conn);die;
    $this->connection = $conn;
    $this->repository = $repository;
  }

  public function sendMsg($name,$data)
  {
    $this->send([        
        "name"       => $name,
        "data"       => $data,
      ]);
  }

  public function getConnection()
  {
    return $this->connection;
  }

  private function send(array $data)
  {
	$this->connection->send(json_encode($data));
  }

}


?>
