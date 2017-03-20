<?php
class Webgriffe_Tntpro_Block_Checkout_Onepage_Billing_Extra extends Mage_Core_Block_Template
{
    const TNT_POINTS_SELECT_NAME = 'tnt_delivery_point_select';
    const TNT_POINTS_AUTOCOMPLETE_NAME = 'tnt_delivery_point_autocomplete';

    protected function _getAllowSymlinks() # fix per la 1.6.0.0
    {
        return true;
    }

    public function fetchView($fileName) {
        $this->setScriptPath(
            Mage::getModuleDir('','Webgriffe_Tntpro'). DS . 'templates'
        );

        return parent::fetchView($this->getTemplate());
    }

    protected function _getActiveDeliveryPoints()
    {
        return Mage::helper('wgtntpro/pointService')->getCachedActivePoints();
    }

    protected function _renderItemLabel($item)
    {
        // @todo Implementare rendering elemento
        return $item['companyName'];
    }


}
