<?php

OCP\JSON::checkLoggedIn();
// OCP\JSON::callCheck();

$data = array();
$dir = isset($_REQUEST['dir']) ? $_REQUEST['dir'] : '';
for(;;){
	$config = $dir.'/.config';
	if(\OC\Files\Filesystem::is_file($config)){
		$config = parse_ini_file(\OC\Files\Filesystem::getLocalFile($config));
		$master = $config['master'];
		$log = basename($master,'.tex').'.log';
		$md5sums = \OC\Files\Filesystem::file_get_contents($dir."/build/md5sums");
		$lines = split("\n",$md5sums);
		foreach($lines as $line){
			if($line==="")
				continue;
			$md5=substr($line,0,32);
			$file=substr($line,34);
			if(substr($file,-3)==="log")
				$file = "log";
			else
				$file = basename($file,'.png');
// 			if($_REQUEST[$file]!==$md5){
				$data[$file]=$md5;
// 			}
		}
		$data['dir']=$dir;
		$data['logfilename']=$log;
		break;
		
	}else if($dir==="/" || $dir==="" || $dir=="."){
		break;
	}
	$dir = dirname($dir);
}

OCP\JSON::success(array('data' => $data));
