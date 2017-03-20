<?php
/**
 * Created by PhpStorm.
 * User: manuele
 * Date: 23/01/15
 * Time: 09:58
 */


class Webgriffe_Tntpro_Block_Checkout_Onepage_Billing_Extra_Js extends Mage_Core_Block_Template
{
    protected function _getAllowSymlinks() # fix per la 1.6.0.0
    {
        return TRUE;
    }

    public function fetchView($fileName)
    {
        $this->setScriptPath(
            Mage::getModuleDir('', 'Webgriffe_Tntpro') . DS . 'templates'
        );

        return parent::fetchView($this->getTemplate());
    }

    protected function _getActiveDeliveryPointsByCode()
    {
        return Mage::helper('wgtntpro/pointService')->getCachedActivePointsByCode();
    }
} 
