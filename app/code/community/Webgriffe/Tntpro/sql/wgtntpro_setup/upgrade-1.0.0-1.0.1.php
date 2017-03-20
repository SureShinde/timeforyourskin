<?php

/** @var Webgriffe_Tntpro_Model_Setup $installer */
$installer = $this;
$installer->startSetup();

$connection = $installer->getConnection();

$table = $connection->newTable($installer->getTable('wgtntpro/tnt_point_address'))
    ->addColumn(
        'id',
        Varien_Db_Ddl_Table::TYPE_BIGINT,
        null,
        array('identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true)
    )
    ->addColumn(
        'quote_address_id',
        Varien_Db_Ddl_Table::TYPE_INTEGER,
        null,
        array('nullable' => true, 'unsigned' => true)
    )
    ->addColumn(
        'order_address_id',
        Varien_Db_Ddl_Table::TYPE_INTEGER,
        null,
        array('nullable' => true, 'unsigned' => true)
    )
    ->addColumn('tnt_point_code', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array('nullable' => false))
    ->addColumn('tnt_point_data', Varien_Db_Ddl_Table::TYPE_TEXT, null, array('nullable' => true))
    ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array('nullable' => false))
    ->addColumn('updated_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array('nullable' => true))
    ->addForeignKey(
        $installer->getFkName('wgtntpro/tnt_point_address', 'quote_address_id', 'sales/quote_address', 'address_id'),
        'quote_address_id',
        $installer->getTable('sales/quote_address'),
        'address_id',
        Varien_Db_Ddl_Table::ACTION_SET_NULL,
        Varien_Db_Ddl_Table::ACTION_NO_ACTION
    )
    ->addForeignKey(
        $installer->getFkName('wgtntpro/tnt_point_address', 'order_address_id', 'sales/order_address', 'entity_id'),
        'order_address_id',
        $installer->getTable('sales/order_address'),
        'entity_id',
        Varien_Db_Ddl_Table::ACTION_SET_NULL,
        Varien_Db_Ddl_Table::ACTION_NO_ACTION
    );

$connection->createTable($table);

$installer->endSetup();
