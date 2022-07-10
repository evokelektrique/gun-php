<?php
require_once __DIR__ . "/../vendor/autoload.php";
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use Gun\Socket;
use Gun\Dup;
$dup = new Dup();
$com = new \Gun\Com();

$socket = new Socket();
$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            $socket
        )
    ),
    8080
);

// $count = 0;
// $server->loop->addPeriodicTimer(1, function() use ($socket, &$count, $dup) {
// 	foreach($socket->clients as $client) {
// 		$message = ["#" => $dup->track($count)];
// 		$client->send(json_encode($message));
// 	}
// 	$count++;
// });

$server->run();
