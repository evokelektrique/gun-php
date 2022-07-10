<?php

namespace Gun;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use React\EventLoop\Loop;
use Gun\Dup;
use Gun\Ham;

class Socket implements MessageComponentInterface {
	public $dup;
	public $graph;

    public function __construct()
    {
        $this->clients = new \SplObjectStorage;
        $this->dup = new Dup();
        $this->graph = [];
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);

        echo "New connection! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
    	$message = json_decode($msg, true);
    	if($this->dup->check($message["#"])) {
    		return;
    	}
    	$this->dup->track($message["#"]);

    	// Ham
    	if(isset($message["put"])) {
    		var_dump(Ham::mix($message["put"], $this->graph));
    		echo "----------------\n";
    		var_dump($this->graph);
    	}

    	foreach($this->clients as $client) {
    		$client->send(json_encode($message));
    	}
    }

    public function onClose(ConnectionInterface $conn) {}

    public function onError(ConnectionInterface $conn, \Exception $e) {}
}
