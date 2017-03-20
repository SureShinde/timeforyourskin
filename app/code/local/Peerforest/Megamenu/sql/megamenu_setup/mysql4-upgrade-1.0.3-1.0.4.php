<?php
$installer = $this;
$installer->startSetup();

$setup = new Mage_Catalog_Model_Resource_Setup('core_setup');

$setup->addAttribute("catalog_category", "category_top_image",  array(
        "type"     => "varchar",
        "backend"  => "catalog/category_attribute_backend_image",
        "frontend" => "",
        "label"    => "Category Top Image",
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
        "group"         => 'General Information',
        "visible_on_front"  => false,
        "unique"     => false,
        "note"       => "",
        ));

$setup->addAttribute("catalog_category", "category_description", array(
    "type"              => "text",
    "backend"           => "",
    "frontend"          => "",
    "label"             => "Category Description",
    "input"             => "textarea",
    "class"             => "",
    "source"            => "",
    "global"            => "0",
    "visible"           => true,
    "required"          => false,
    "user_defined"      => true,
    "default"           => "",
    "searchable"        => false,
    "filterable"        => false,
    "comparable"        => false,
    "visible_on_front"  => true,
    "used_in_product_listing" => false,
    "unique"            => false,
    "wysiwyg_enabled"   => true,
    "apply_to"          => "",
    "is_configurable"   => true,
    "group"         => 'General Information'
));
    
$installer->endSetup();