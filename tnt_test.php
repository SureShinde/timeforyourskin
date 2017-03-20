<?php

$c = new SoapClient('http://www.mytnt.it/ResiService/ResiServiceImpl.wsdl');

var_dump($c->__getFunctions());

?>
