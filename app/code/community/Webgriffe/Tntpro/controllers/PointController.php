<?php
/**
 * Created by PhpStorm.
 * User: manuele
 * Date: 16/01/15
 * Time: 12:56
 */

class Webgriffe_Tntpro_PointController extends Mage_Core_Controller_Front_Action
{

    public function billingAction()
    {
        $checkout = Mage::getSingleton('checkout/session')->getQuote();
        $billAddress = $checkout->getBillingAddress();
        echo $billAddress->getCity();
        echo ' ';
        echo $billAddress->getRegion();
        echo ' ';
        echo $billAddress->getPostcode();
        echo ' ';
        echo $billAddress->getCountry();
        echo ' ';
        echo $billAddress->getCountryId();
        return;
    }

    public function searchAction()
    {
        $query = Mage::app()->getRequest()->getParam(
            Webgriffe_Tntpro_Block_Checkout_Onepage_Billing_Extra::TNT_POINTS_AUTOCOMPLETE_NAME
        );
        $resultPoints = Mage::helper('wgtntpro/pointService')->findActivePointsByQuery($query, 10);
        $this->getResponse()->setBody($this->__formatResultPoints($resultPoints));
    }

    /**
     * @param array $resultPoints
     * @return string
     */
    private function __formatResultPoints(array $resultPoints)
    {
        $html = array('<ul>');
        if (count($resultPoints) == 0) {
            $html[] = '<li><em>' . Mage::app()->getTranslator()->translate(array('No result found...')) . '</em></li>';
        }
        foreach ($resultPoints as $point) {
            $html[] = '<li data-code="'. $point['code'] .'">';
            $html[] = "<strong>{$point['companyName']}</strong><br />";
            $html[] = "{$point['address']} - {$point['postCode']} {$point['town']}<br />";
            $html[] = "{$point['region']}<br />";
            $html[] = " <em>{$point['pointType']}</em> ";
            $html[] = '</li>';
        }
        $html[] = '</ul>';
        return implode(PHP_EOL, $html);
    }
} 
