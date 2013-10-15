<?php

OCP\JSON::checkLoggedIn();
// OCP\JSON::callCheck();

$padID = $_REQUEST['padID'];
$etherpad = new OCA\Files\Etherpad();
$etherpad->compileLatex($padID,false);

OCP\JSON::success(array('data' => array('result'=>'ok')));