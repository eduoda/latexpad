<?php

OC::$CLASSPATH['OCA\Files\Etherpad'] = 'latexpad/lib/etherpad.php';

OCP\Util::addscript( 'latexpad', 'latexpad');
OCP\App::registerAdmin('latexpad','settings');
 
OCP\Util::connectHook('OC_Filesystem', 'post_delete', 'OCA\Files\Etherpad', 'deleteFile');
OCP\Util::connectHook('OC_Filesystem', 'post_rename', 'OCA\Files\Etherpad', 'moveFile');
OCP\Util::connectHook('OC_Filesystem', 'post_write',  'OCA\Files\Etherpad', 'writeFile');
