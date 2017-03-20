<?php
/**
 * Created by PhpStorm.
 * User: manuele
 * Date: 23/01/15
 * Time: 10:47
 */

class Webgriffe_Tntpro_Block_Adminhtml_Notifications extends Mage_Adminhtml_Block_Template
{
    protected function _getTntPointUrl()
    {
        return Mage::helper('wgtntpro/pointService')->getTntPointsUrl();
    }

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

    /**
     * @return bool
     */
    protected function _hasError()
    {
        $points = Mage::helper('wgtntpro/pointService')->getCachedActivePoints();
        if ($points === false) {
            // @todo valutare se disabilitare locator facendo attenzione che nella richiesta in cui si disattiva
            // l'opzione di config non è allineata con il valore mostrato in quanto layout già caricato.
            return true;
        }
        return false;
    }
} 
