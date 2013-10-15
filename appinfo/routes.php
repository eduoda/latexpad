<?php

$this->create('latexpad_edit', '/edit/{dir}/{file}')->action(
    function($params){
        require __DIR__ . '/../edit.php';
    }
);

\OCP\API::register(
	'get',
	'/apps2/latexpad/update/{padID}',
	function($params){
		$etherpad = new OCA\Files\Etherpad();
		return $etherpad->compileLatex($params['padID']);
	},
	'latexpad',
	OC_API::ADMIN_AUTH
);
