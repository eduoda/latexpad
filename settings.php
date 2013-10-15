<?php

$params = array(
    'etherpad_apikey',
    'etherpad_server',
);

if ($_POST) {
  foreach($params as $param){
    if(isset($_POST[$param])){
      OC_Appconfig::setValue('latexpad', $param, $_POST[$param]);
    }
  }
}

// fill template
$tmpl = new OC_Template('latexpad', 'settings');
foreach($params as $param){
  $value = OC_Appconfig::getValue('latexpad', $param,'');
  $tmpl->assign($param, $value);
}

return $tmpl->fetchPage();
