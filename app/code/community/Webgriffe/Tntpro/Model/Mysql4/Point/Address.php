<?php
/**
 * Created by PhpStorm.
 * User: manuele
 * Date: 16/01/15
 * Time: 10:42
 */ 
class Webgriffe_Tntpro_Model_Mysql4_Point_Address extends Mage_Core_Model_Resource_Db_Abstract
{
    protected $_serializableFields = array('tnt_point_data' => array(null, array()));

    protected function _construct()
    {
        $this->_init('wgtntpro/tnt_point_address', 'id');
    }
}
