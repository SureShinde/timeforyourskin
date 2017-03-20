<?php
/**
 * Created by PhpStorm.
 * User: manuele
 * Date: 16/01/15
 * Time: 10:42
 */

/**
 * Class Webgriffe_Tntpro_Model_Point_Address
 * @method int getQuoteAddressId()
 * @method Webgriffe_Tntpro_Model_Point_Address setQuoteAddressId(int $quoteAddressId)
 * @method int getOrderAddressId()
 * @method Webgriffe_Tntpro_Model_Point_Address setOrderAddressId(int $orderAddressId)
 * @method string getTntPointCode()
 * @method Webgriffe_Tntpro_Model_Point_Address setTntPointCode(string $tntPointCode)
 * @method array getTntPointData()
 * @method Webgriffe_Tntpro_Model_Point_Address setTntPointData(array $tntPointData)
 * @method DateTime getCreatedAt()
 * @method Webgriffe_Tntpro_Model_Point_Address setCreatedAt(string $createdAt)
 * @method DateTime getUpdatedAt()
 * @method Webgriffe_Tntpro_Model_Point_Address setUpdatedAt(string $updatedAt)
 */
class Webgriffe_Tntpro_Model_Point_Address extends Mage_Core_Model_Abstract
{

    protected function _construct()
    {
        $this->_init('wgtntpro/point_address');
    }

    protected function _beforeSave()
    {
        $this->setUpdatedAt(Mage::getSingleton('core/date')->gmtDate());
        if ($this->isObjectNew() && null === $this->getCreatedAt()) {
            $this->setCreatedAt(Mage::getSingleton('core/date')->gmtDate());
        }
        return parent::_beforeSave();
    }

    /**
     * @param $quoteAddressId
     * @return Webgriffe_Tntpro_Model_Point_Address
     */
    public function loadByQuoteAddressId($quoteAddressId)
    {
        return $this->load($quoteAddressId, 'quote_address_id');
    }

    /**
     * @param $orderAddressId
     * @return Webgriffe_Tntpro_Model_Point_Address
     */
    public function loadByOrderAddressId($orderAddressId)
    {
        return $this->load($orderAddressId, 'order_address_id');
    }

    public function getOperationalType(){
        /*
         * if(points[i].pointType.toLowerCase() === "filiale tnt"){deO =1;}
    else if(points[i].pointType.toLowerCase() === "locker"){deO =5;}
            else{deO=3;}
         */
        $data = $this->getTntPointData();
        if (empty($data)) {
            return false;
        }
        $pointType = strtolower($data['pointType']);
        if ($pointType === 'filiale tnt') {
            return 1;
        }
        if ($pointType === 'locker') {
            return 5;
        }
        return 3;
    }

    public function getOperationalCode(){
        $data = $this->getTntPointData();
        if (empty($data)) {
            return false;
        }
        return $data['code'];
    }
}
