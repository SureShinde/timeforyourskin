<?php

require_once 'includes/autoload.php';


//$URL = 'http://api.ekomi.de/v3/getProductfeedback?auth=665|FMJuyuC8uEbo3WxRa5aG&version=cust-1.0.0&charset=utf-8&range=all&type=json&caching=none&rand=1450791601';
$text = 'gÃ¼nstig, QualitÃ¤t sehr gut';

echo Encoding::toUTF8($text);

echo '<br>';
echo Encoding::fixUTF8($text);
echo '<br>';
echo  $text;