<?php 
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class WorkerReceiver
{
	public function listen()
    {
    	$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
		$channel = $connection->channel();

		$channel->queue_declare('insert_csv', false, false, false, false);

		echo ' [*] Waiting for messages. To exit press CTRL+C', "\n";

		$channel->basic_consume('insert_csv', '', false, true, false, false, array($this, 'process'));

		while(count($channel->callbacks)) {
		    $channel->wait();
		}

    }

    public function process(AMQPMessage $msg)
    {
    	echo " [x] Received ", $msg->body, "\n";

    	$tMsg = json_decode($msg->body);

    	if($tMsg['type'] == 'insert'){

    	}
    	else if($tMsg['type'] == 'parse'){
		  	$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
		  	$channel = $connection->channel();
		  	$channel->queue_declare('insert_csv', false, false, false, false);
		  	$msg = new AMQPMessage('insert');
		  	$channel->basic_publish($msg, '', 'insert_csv');
		  	$channel->close();
		  	$connection->close();
		}
    }
}