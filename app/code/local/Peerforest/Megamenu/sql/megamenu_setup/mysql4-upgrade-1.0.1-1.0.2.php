<?php
$installer = $this;
$installer->startSetup();

$setup = new Mage_Catalog_Model_Resource_Setup('core_setup');

$setup->addAttribute("catalog_category", "thumbnail",  array(
        "type"     => "varchar",
        "backend"  => "catalog/category_attribute_backend_image",
        "frontend" => "",
        "label"    => "Thumbnail Image",
        "input"    => "image",
        "class"    => "",
        "source"   => "",
        "global"   => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
        "visible"  => true,
        "required" => false,
        "user_defined"  => false,
        "default" => "",
        "searchable" => false,
        "filterable" => false,
        "comparable" => false,
        'group'         => 'General Information',
        "visible_on_front"  => false,
        "unique"     => false,
        "note"       => "",
        ));
    
$installer->endSetup();