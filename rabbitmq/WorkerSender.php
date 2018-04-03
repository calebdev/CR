<?php
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class WorkerSender
{
    public function execute($message)
    {
        $connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
        $channel = $connection->channel();

        $msg_json = json_encode($message);

        $channel->queue_declare('insert_csv', false, false, false, false);

        $msg = new AMQPMessage($msg_json);
        $channel->basic_publish($msg, '', 'insert_csv');

        $channel->close();
        $connection->close();
    }
}