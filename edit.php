<?php
// Look up other security checks in the docs!
\OCP\User::checkLoggedIn();
\OCP\App::checkAppEnabled('latexpad');

require_once 'lib/etherpad.php';


// $path = rawurldecode($params['path'])
$dir = rawurldecode($params['dir']);
$filename = rawurldecode($params['file']);
$path = $dir."/".$filename;
$owner = \OC\Files\Filesystem::getOwner($path);
//TODO: is this right??
if(substr($path,0,7)==='/Shared'){
	$source = \OC_Share_Backend_File::getSource(substr($path,8));
// 	foreach ($source as $key => $value)
// 		echo $key.": ".$value."<br>\n";
	$path=substr($source['path'],5);
}

$etherpad = new \OCA\Files\Etherpad();
$padID = $etherpad->getPad($owner,$path);


// Make breadcrumb
$breadcrumb = array();
$pathtohere = '';
foreach (explode('/', $path) as $i) {
	if ($i != '') {
		$pathtohere .= '/' . $i;
		$breadcrumb[] = array('dir' => $pathtohere, 'name' => $i);
	}
}
$breadcrumbNav = new OCP\Template('files', 'part.breadcrumb', '');
$breadcrumbNav->assign('breadcrumb', $breadcrumb);
$breadcrumbNav->assign('baseURL', OCP\Util::linkTo('files', 'index.php') . '?dir=');


OCP\Util::addStyle('latexpad', 'latexpad');
$tpl = new OCP\Template("latexpad", "latexeditor", "user");
$tpl->assign('breadcrumb', $breadcrumbNav->fetchPage());
$tpl->assign('padID', $padID);
$tpl->assign('etherpad_server', OC_Appconfig::getValue('latexpad', 'etherpad_server',''));
$tpl->assign('user', rawurlencode(\OC_User::getUser()));
$tpl->assign('dir', $dir);
$tpl->printPage();
