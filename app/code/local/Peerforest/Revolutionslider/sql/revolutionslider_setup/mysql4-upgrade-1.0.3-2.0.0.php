<?php

$installer = $this;

$installer->startSetup();

$installer->run("

ALTER TABLE {$this->getTable('revolutionslider')} ADD `stores` text default '' AFTER `title`;



    ");

$installer->endSetup();