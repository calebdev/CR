<?php
namespace classes;


use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class FrontController
{
    private $templatePath = '/views';
    private $cacheDir = 'cache';

    protected $request;

    protected $twig;

    public function __construct()
    {
        $loader = new \Twig_Loader_Filesystem(dirname(dirname(__FILE__)).$this->templatePath);
        $this->twig = new \Twig_Environment($loader);

        //$this->twig->setCache(dirname(dirname(__FILE__)).$this->cacheDir);

        $this->request = Request::createFromGlobals();
    }

    public function render($name, $context){
        $result = $this->twig->render($name,$context);
        $response = new Response(
            $result,
            Response::HTTP_OK,
            array('content-type' => 'text/html')
        );
        $response->send();
    }

}