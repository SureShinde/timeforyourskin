<?php
$installer = $this;
$installer->startSetup();

$setup = new Mage_Catalog_Model_Resource_Setup('core_setup');

$setup->addAttribute('catalog_category', 'verticalmenu_width', array(
    'group'         => 'Mega Menu',
    'input'         => 'text',
    'type'          => 'varchar',
    'label'         => 'Popup Width',
    'backend'       => '',
    'visible'       => true,
    'required'      => false,
    'visible_on_front' => true,
    'note'	        => 'Width calculated in px. Exa: 700.',
    'global'        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
));
    
$installer->endSetup();