<?php
/**
 * Created by PhpStorm.
 * User: manuele
 * Date: 16/01/15
 * Time: 10:42
 */ 
class Webgriffe_Tntpro_Model_Mysql4_Point_Address_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{

    protected function _construct()
    {
        $this->_init('wgtntpro/point_address');
    }

}
