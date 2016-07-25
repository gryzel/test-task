<?php

require __DIR__ . "/../vendor/autoload.php";

use Booking\Booking;

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;

$server = IoServer::factory(new HttpServer(new WsServer(new Booking)), 2000);

$server->run();

?>
