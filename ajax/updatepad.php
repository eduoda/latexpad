<?php

OCP\JSON::checkLoggedIn();
// OCP\JSON::callCheck();

$path = isset($_REQUEST['path']) ? $_REQUEST['path'] : '';

\OCA\Files\Etherpad::writeFile(array('path'=>$path));

OCP\JSON::success(array('data' => array('result'=>'ok')));