<?php

namespace Booking\Repository;

use Ratchet\ConnectionInterface;

interface RepositoryInterface
{
  public function getClientByConnection(ConnectionInterface $conn);

  public function addClient(ConnectionInterface $conn);

  public function removeClient(ConnectionInterface $conn);

  public function getClients();
}

?>
