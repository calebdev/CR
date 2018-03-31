<?php
namespace classes;
use Symfony\Component\Yaml\Yaml;


class MySqlConnector {
    private static $_pdoInstance = null;

    public static function getPdo() {
        if(is_null(self::$_pdoInstance)) {
            try{
                $params = Yaml::parseFile(dirname(dirname(__FILE__)).'/config/database.yml', Yaml::PARSE_OBJECT_FOR_MAP);

                if(property_exists($params->database, 'host') && property_exists($params->database, 'bdName') && property_exists($params->database, 'user') && property_exists($params->database, 'password')){
                    $dns = "mysql:host=".$params->database->host.";dbname=".$params->database->bdName.";charset=utf8";
                    self::$_pdoInstance = new \PDO($dns, $params->database->user, $params->database->password,
                        array(\PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"));
                }else{
                    throw new \Exception ('Configuration datapase not yet');
                }

            }catch (\PDOException $e){
                echo $e->getMessage();
            }catch (\Exception $e){
                echo $e->getMessage();
            }
        }

        return self::$_pdoInstance;
    }
}