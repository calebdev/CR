<?php 
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use classes\MySqlConnector;
use Goodby\CSV\Import\Standard\LexerConfig;
use Goodby\CSV\Import\Standard\Lexer;
use Goodby\CSV\Import\Standard\Interpreter;

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
        if(isset($tMsg->type))
    	switch ($tMsg->type){
            case 'insert':
                $stmt = MySqlConnector::getPdo()->prepare('INSERT INTO embauche (compte_facture, num_facture, num_abonne, date, heure, duree_volume_reel, duree_volume_facture, type) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
                $stmt->execute($tMsg->data);
                echo 'INSERT INTO embauche (compte_facture, num_facture, num_abonne, date, heure, duree_volume_reel, duree_volume_facture, type) VALUES' . json_encode($tMsg->data);
                break;
            case 'parse':
                $config = new LexerConfig();
                $config->setDelimiter(';');
                $lexer = new Lexer($config);

                $interpreter = new Interpreter();
                $i = 1;

                $connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
                $channel = $connection->channel();

                $channel->queue_declare('insert_csv', false, false, false, false);

                $interpreter->addObserver(function(array $columns) use (&$i, $channel){
                    $line = $columns;
                    if(count($line) >= 8){
                        //validation de donnees
                        if(preg_match('/^(?:(?:31(\/|-|\.)(?:0?[13578]|1[02]))\1|(?:(?:29|30)(\/|-|\.)(?:0?[1,3-9]|1[0-2])\2))(?:(?:1[6-9]|[2-9]\d)?\d{2})$|^(?:29(\/|-|\.)0?2\3(?:(?:(?:1[6-9]|[2-9]\d)?(?:0[48]|[2468][048]|[13579][26])|(?:(?:16|[2468][048]|[3579][26])00))))$|^(?:0?[1-9]|1\d|2[0-8])(\/|-|\.)(?:(?:0?[1-9])|(?:1[0-2]))\4(?:(?:1[6-9]|[2-9]\d)?\d{2})$/',$line[3]) //validate date
                            && preg_match('/^(?:(?:([01]?\d|2[0-3]):)?([0-5]?\d):)?([0-5]?\d)$/', $line[4])  //type heure
                            && preg_match('/^(?:(?:([01]?\d|2[0-3]):)?([0-5]?\d):)?([0-5]?\d)$/', $line[5])  //type heure
                            && preg_match('/^(?:(?:([01]?\d|2[0-3]):)?([0-5]?\d):)?([0-5]?\d)$/', $line[6])) //type heure
                        {
                            list($d, $m, $y) = explode('/',$line[3]);
                            $line[3] = $y.'-'.$m.'-'.$d;
                            $tMessage = [
                                'type' => 'insert',
                                'data' => $line
                            ];

                            $msg = new AMQPMessage(json_encode($tMessage));
                            $channel->basic_publish($msg, '', 'insert_csv');
                        }
                    }
                    $i++;
                });

                echo 'parse file';

                $lexer->parse(dirname(dirname(__FILE__)).'../uploads/'.$tMsg->file, $interpreter);

                $channel->close();
                $connection->close();

                echo 'finish parse';

                break;
        }
    	/*if($tMsg['type'] == 'insert'){

    	}
    	else if($tMsg['type'] == 'parse'){
		  	$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
		  	$channel = $connection->channel();
		  	$channel->queue_declare('insert_csv', false, false, false, false);
		  	$msg = new AMQPMessage('insert');
		  	$channel->basic_publish($msg, '', 'insert_csv');
		  	$channel->close();
		  	$connection->close();
		}*/
    }
}