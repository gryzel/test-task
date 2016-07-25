<?php

namespace Booking\Repository;

use SplObjectStorage;
use Booking\Connection\BookingConnection;
use Ratchet\ConnectionInterface;

class Repository implements RepositoryInterface
{
  private $clients;

  public function __construct()
  {
    $this->clients = new SplObjectStorage;
  }

  public function getClientByConnection(ConnectionInterface $conn)
  {
    foreach ($this->clients as $client)
    {
      if($client->getConnection() === $conn)
        return $client;
    }

    return null;
  }

  public function addClient(ConnectionInterface $conn)
  {
    $this->clients->attach(new BookingConnection($conn, $this));
  }

  public function removeClient(ConnectionInterface $conn)
  {
    $client = $this->getClientByConnection($conn);
    if($client !== null)
      $this->clients->detach($client);
  }

  public function getClients()
  {
    return $this->clients;
  }
}

?>
