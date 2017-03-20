<?php
//echo 'cron started'.PHP_EOL;
//ini_set('display_errors', 1);
//error_reporting(E_ALL);
// run magento
require 'app/Mage.php';
Mage::app();
//place your code below
Mage::getModel('followupemail/cron')->cronJobs();
