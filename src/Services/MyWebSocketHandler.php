<?php
namespace App\Service;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class MyWebSocketHandler implements MessageComponentInterface
{
    public function onOpen(ConnectionInterface $conn)
    {
        // Logic to handle a new WebSocket connection
        echo "New connection! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        // Logic to handle incoming WebSocket messages
        echo "Received message from {$from->resourceId}: {$msg}\n";
    }

    public function onClose(ConnectionInterface $conn)
    {
        // Logic to handle WebSocket connection close
        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        // Logic to handle WebSocket errors
        echo "An error occurred: {$e->getMessage()}\n";
        $conn->close();
    }
}
