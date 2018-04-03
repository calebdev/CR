<?php
/**
 * Created by PhpStorm.
 * User: esokia
 * Date: 03/04/18
 * Time: 10:06
 */
$loader = require dirname(__FILE__).'/../vendor/autoload.php';
$loader->register();
require 'WorkerReceiver.php';
$receiver = new WorkerReceiver();
$receiver->listen();