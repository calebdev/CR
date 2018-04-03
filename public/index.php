<?php
ini_set('display_errors', 1);

$loader = require '../vendor/autoload.php';
$loader->register();
require '../classes/mysqlConnector.php';
require '../classes/FrontController.php';



use Symfony\Component\Config\FileLocator;
use Symfony\Component\Routing\Loader\YamlFileLoader;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


$fileLocator = new FileLocator(array(__DIR__.'/../config/'));
$loader = new YamlFileLoader($fileLocator);
$routes = $loader->load('routes.yml');
$context = new RequestContext('/');
$matcher = new UrlMatcher($routes, $context);

$request = Request::createFromGlobals();

try{
    $parameters = $matcher->match($request->getPathInfo());
    $arr = explode('::', $parameters['_controller'],2);
    if (!file_exists('../controller/'.$arr[0].'.php' ))
        throw new Exception ('Class '. $arr[0] .' does not exist');

    require '../controller/'.$arr[0].'.php';
    if(!is_callable($parameters['_controller']))
        throw new Exception('Can not call : '.$parameters['_controller']);

    $objName = $arr[0];
    $obj = new $objName();
    call_user_func([$obj, $arr[1]]);
}catch (\Symfony\Component\Routing\Exception\ResourceNotFoundException $e){
    $response = new Response('Not found! ', Response::HTTP_NOT_FOUND);
    echo $response->send();
}
catch( Exception $e )
{
    echo $e->getMessage();
}
