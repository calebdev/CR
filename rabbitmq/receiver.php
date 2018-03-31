<?php
/**
 * Created by PhpStorm.
 * User: esokia
 * Date: 03/04/18
 * Time: 10:06
 */
require dirname(__FILE__).'/../vendor/autoload.php';
require dirname(__FILE__).'/../classes/mysqlConnector.php';
require 'WorkerReceiver.php';
require 'WorkerSender.php';

$receiver = new WorkerReceiver();
$receiver->listen();