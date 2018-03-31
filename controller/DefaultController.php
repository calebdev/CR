<?php
use classes\MySqlConnector;
use Goodby\CSV\Import\Standard\LexerConfig;
use Goodby\CSV\Import\Standard\Lexer;
use Goodby\CSV\Import\Standard\Interpreter;

class DefaultController extends \classes\FrontController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function indexAction(){
        $this->render('index.html', array('name' => 'Fabien'));
    }

    private function sum_the_time($time1, $time2) {
        $times = array($time1, $time2);
        $seconds = 0;
        foreach ($times as $time)
        {
            list($hour,$minute,$second) = explode(':', $time);
            $seconds += $hour*3600;
            $seconds += $minute*60;
            $seconds += $second;
        }
        $hours = floor($seconds/3600);
        $seconds -= $hours*3600;
        $minutes  = floor($seconds/60);
        $seconds -= $minutes*60;
        // return "{$hours}:{$minutes}:{$seconds}";
        return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
    }

    public function dureeTotalAction(){
        $sth = MySqlConnector::getPdo()->prepare('
            SELECT duree_volume_reel FROM embauche
            WHERE date >= 2012-02-15
            ;
        ');
        $sth->execute();
        $results = $sth->fetchAll(PDO::FETCH_OBJ);

        //count
        $sumTime = '00:00:00';
        foreach($results as $result){
            $sumTime = $this->sum_the_time($result->duree_volume_reel, $sumTime);
        }

        $this->render('duree-total.html', ['sum' => $sumTime]);
    }

    public function importCsvAction(){
        $delimiter = $this->request->query->get('delimiter');
        $csvFile = $this->request->files->get('csv');
        $init = intval($this->request->query->get('init')) - 1;

        $file_name = sha1(time()).'.csv';

        if(move_uploaded_file($csvFile, '../uploads/'.$file_name ) ){

            $message = [
              'type'  => 'parse',
              'file'  =>  $file_name
            ];
            \rabbit\WorkerSender::execute($message);
            /*$config = new LexerConfig();
            $config->setDelimiter(';');
            $lexer = new Lexer($config);

            $interpreter = new Interpreter();

            $list = [];
            $pdo = MySqlConnector::getPdo();
            $i = 1;
            $interpreter->addObserver(function(array $columns) use (&$i, $init, $pdo){
                if($i >= $init){
                    $line = $columns;
                    //validation des donee
                    if(preg_match('/^(?:(?:31(\/|-|\.)(?:0?[13578]|1[02]))\1|(?:(?:29|30)(\/|-|\.)(?:0?[1,3-9]|1[0-2])\2))(?:(?:1[6-9]|[2-9]\d)?\d{2})$|^(?:29(\/|-|\.)0?2\3(?:(?:(?:1[6-9]|[2-9]\d)?(?:0[48]|[2468][048]|[13579][26])|(?:(?:16|[2468][048]|[3579][26])00))))$|^(?:0?[1-9]|1\d|2[0-8])(\/|-|\.)(?:(?:0?[1-9])|(?:1[0-2]))\4(?:(?:1[6-9]|[2-9]\d)?\d{2})$/',$line[3]) //validate date
                     && preg_match('/^(?:(?:([01]?\d|2[0-3]):)?([0-5]?\d):)?([0-5]?\d)$/', $line[4])  //type heure
                     && preg_match('/^(?:(?:([01]?\d|2[0-3]):)?([0-5]?\d):)?([0-5]?\d)$/', $line[5])  //type heure
                     && preg_match('/^(?:(?:([01]?\d|2[0-3]):)?([0-5]?\d):)?([0-5]?\d)$/', $line[6])) //type heure
                    {
                        list($d, $m, $y) = explode('/',$line[3]);
                        $line[3] = $y.'-'.$m.'-'.$d;
                        $stmt = $pdo->prepare('INSERT INTO embauche (compte_facture, num_facture, num_abonne, date, heure, duree_volume_reel, duree_volume_facture, type) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
                        $stmt->execute($line);
                    }
                }
                $i++;
            });

            $lexer->parse('../uploads/'.$file_name, $interpreter);
            */

        }
        $this->render('upload.html');
    }

    public function uploadAction(){
        $delimiter = $this->request->query->get('delimiter');
        $csvFile = $this->request->files->get('csvFile');
        $init = intval($this->request->query->get('init')) - 1;

        $file_name = sha1(time()).'.csv';

        if(move_uploaded_file($csvFile, '../uploads/'.$file_name ) ){
            $config = new LexerConfig();
            $config->setDelimiter(';');
            $lexer = new Lexer($config);

            $interpreter = new Interpreter();

            $list = [];
            $i = 0;
            $interpreter->addObserver(function(array $columns) use (&$list, &$i, $init){
                if($i <= 10){
                    $list[] = (object)[
                        'compte_facture' => isset($columns[0])?$columns[0]:'',
                        'num_facture' => isset($columns[1])?$columns[1]:'',
                        'num_abonne' => isset($columns[2])?$columns[2]:'',
                        'date' => isset($columns[3])?$columns[3]:'',
                        'heure' => isset($columns[4])?$columns[4]:'',
                        'duree_volume_reel' => isset($columns[5])?$columns[5]:'',
                        'duree_volume_facture' => isset($columns[6])?$columns[6]:'',
                        'type' => isset($columns[7])?$columns[7]:'',
                    ];
                }
                $i++;
            });

            $lexer->parse('../uploads/'.$file_name, $interpreter);

            $this->render('list.html', ['lists' => $list]);

        }
    }
}