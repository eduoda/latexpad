<?php

namespace OCA\Files; 

include 'latexpad/lib/etherpad-lite-client/etherpad-lite-client.php';


class Etherpad {
	private $etherpad;
	
	function __construct() {
		$this->etherpad = new \EtherpadLiteClient(\OC_Appconfig::getValue('latexpad', 'etherpad_apikey',''), \OC_Appconfig::getValue('latexpad', 'etherpad_server','').'/api');
	}
	
	public function getAuthor($user){
		$author = $this->etherpad->createAuthorIfNotExistsFor($user,$user);
		$authorID = $author->authorID;
		return $authorID;
	}

	public function getPad($owner,$path){
		$query = \OC_DB::prepare('SELECT `padid` FROM `*PREFIX*etherpad_pads` WHERE `uid` = ? AND `path` = ?');
		$result = $query->execute(array($owner,$path));
		if ($row = $result->fetchRow()) {
			$padID = $row['padid'];
			$groupID = strstr($padID, '$', true);
		}else{
			// try to generate a unique group name for a file
			$group = $this->etherpad->createGroupIfNotExistsFor($owner."::".$path."::".time());
			$groupID = $group->groupID;
			$padID = $groupID.'$content';
			try {
				$pad = $this->etherpad->createGroupPad($groupID,"content","");
			} catch (Exception $e) {
				// provavelmente pad ja existia, ignorando excerssao...
			}
			$query = \OC_DB::prepare('INSERT INTO `*PREFIX*etherpad_pads` (`uid`,`path`,`padid`) VALUES (?,?,?)');
			$query->execute(array($owner,$path,$padID));
	// 		$id=\OC_DB::insertid('*PREFIX*share');
		}

		$authorID = $this->getAuthor(\OC_User::getUser());
		$until=24*60*60;
		$session = $this->etherpad->createSession($groupID,$authorID,time()+$until);
		$sessionID = $session->sessionID;
		setcookie("sessionID", $sessionID, time()+$until, '/', parse_url(\OC_Appconfig::getValue('latexpad', 'etherpad_server',''),PHP_URL_HOST));
		
		return $padID;
	}

	public static function deleteFile($params) {
		$path = $params['path'];
		$owner = \OC\Files\Filesystem::getOwner($path);
		//TODO: is this right??
		if(substr($path,0,7)==='/Shared'){
			$source = \OC_Share_Backend_File::getSource(substr($path,8));
			$path=substr($source['path'],5);
		}
		
		$query = \OC_DB::prepare('SELECT `padid` FROM `*PREFIX*etherpad_pads` WHERE `uid` = ? AND `path` = ?');
		$result = $query->execute(array($owner,$path));
		if ($row = $result->fetchRow()) {
			$padID = $row['padid'];
			$groupID = strstr($padID, '$', true);
			$ep = new \EtherpadLiteClient(\OC_Appconfig::getValue('latexpad', 'etherpad_apikey',''), \OC_Appconfig::getValue('latexpad', 'etherpad_server','').'/api');
			$ep->deleteGroup($groupID);
		}
		
		$query = \OC_DB::prepare('DELETE FROM `*PREFIX*etherpad_pads` WHERE `uid` = ? AND `path` = ?');
		$result = $query->execute(array($owner,$path));
		
		return true;

	}

	public static function moveFile($params) {
		$path = $params['oldpath'];
		$newpath = $params['newpath'];
		$owner = \OC\Files\Filesystem::getOwner($path);
		//TODO: is this right??
		if(substr($path,0,7)==='/Shared'){
			$source = \OC_Share_Backend_File::getSource(substr($path,8));
			$path=substr($source['path'],5);
		}
		$query = \OC_DB::prepare('UPDATE `*PREFIX*etherpad_pads` SET `path` = ? WHERE `uid` = ? AND `path` = ?');
		$result = $query->execute(array($newpath,$owner,$path));

		return true;

	}
	
	public static function writeFile($params) {
		$path = $params['path'];
		if (!$path) return;
		//fix a bug where there were multiply '/' in front of the path, it should only be one
		while ($path[0] == '/') {
			$path = substr($path, 1);
		}
		$path = '/' . $path;
		
		$filecontents = \OC\Files\Filesystem::file_get_contents($path);
		$encoding = mb_detect_encoding($filecontents."a", "UTF-8, WINDOWS-1252, ISO-8859-15, ISO-8859-1, ASCII", true);
		if ($encoding == "") {
			// set default encoding if it couldn't be detected
			$encoding = 'ISO-8859-15';
		}
		$filecontents = iconv($encoding, "UTF-8", $filecontents);

		$owner = \OC\Files\Filesystem::getOwner($path);
		//TODO: is this right??
		if(substr($path,0,7)==='/Shared'){
			$source = \OC_Share_Backend_File::getSource(substr($path,8));
			$path=substr($source['path'],5);
		}
		
		$query = \OC_DB::prepare('SELECT `padid` FROM `*PREFIX*etherpad_pads` WHERE `uid` = ? AND `path` = ?');
		$result = $query->execute(array($owner,$path));
		if ($row = $result->fetchRow()) {
			$padID = $row['padid'];
// 			$groupID = strstr($padID, '$', true);
			$ep = new \EtherpadLiteClient(\OC_Appconfig::getValue('latexpad', 'etherpad_apikey',''), \OC_Appconfig::getValue('latexpad', 'etherpad_server','').'/api');
			$ep->setText($padID,$filecontents);
		}
	}

	public function compileLatex($padID, $checktime=true){

		$query = \OC_DB::prepare('SELECT `uid`,`path` FROM `*PREFIX*etherpad_pads` WHERE `padid`= ?');
		$result = $query->execute(array($padID));
		if ($row = $result->fetchRow()) {
			$uid = $row['uid'];
			$path = $row['path'];
 			\OC_User::setUserId($uid);
			\OC_Util::setupFS($uid);

			if(\OC\Files\Filesystem::isUpdatable($path)) {
				$dir = $path;
				for(;;){
					$dir = dirname($dir);
					$config = $dir.'/.config';
					if(\OC\Files\Filesystem::is_file($config)){
						$config = parse_ini_file(\OC\Files\Filesystem::getLocalFile($config));
						
						$projdir = \OC\Files\Filesystem::getLocalFile($dir);
						$master = $config['master'];
						$pdf = basename($master,'.tex').'.pdf';
						

						$content = $this->etherpad->getText($padID);
						$filecontents = $content->text;
						$filecontents = iconv(mb_detect_encoding($filecontents), 'UTF-8', $filecontents);
						file_put_contents(\OC\Files\Filesystem::getLocalFile($path),$filecontents);
						\OC\Files\Filesystem::touch($path);
						//TODO: endless loop??
						// if fixed, take care with the post_write hook
						//\OC\Files\Filesystem::file_put_contents($path, $filecontents);
						//TODO: do i need this?
						// Clear statcache
						clearstatcache();

						if($checktime && \OC\Files\Filesystem::filemtime($dir.'/build/'.$pdf)>time()-10){
							break;
						}
						if($config['command']=='pdflatex'){
							//TODO: config
							$command = "/usr/share/texmf/bin/pdflatex";
							// $command = escapeshellcmd($command);
						}
						//TODO: config
						$pdftocairo = "/usr/bin/pdftocairo";
						$md5sum = "/usr/bin/md5sum";
						exec("cd ".escapeshellarg($projdir)." ;".$command." -output-directory=build ".escapeshellarg($master)."; cd build; ".$pdftocairo." -png -scale-to-x 700 -scale-to-y -1 ".escapeshellarg($pdf)." page; ".$md5sum." *.log *.png > md5sums");
						break;
					}else if($dir=="/" || $dir=="" || $dir=="."){
						break;
					}
				}
			}
		}
		return new \OC_OCS_Result(array('result'=>'ok'));
	}	
}

