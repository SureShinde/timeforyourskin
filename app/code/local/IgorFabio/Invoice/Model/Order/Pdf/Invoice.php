<?php
/**
 * IgorFabio PDF rewrite for custom attribute
 * * Attribute "IgorFabio_Survey_Information" has to be set manually
 * Original: Sales Order Invoice PDF model
 *
 * @category   IgorFabio
 * @package    IgorFabio_Invoice
 * @author     Igor Revenco - IgorFabio <igorexpert1012@gmail.com>
 */
class IgorFabio_Invoice_Model_Order_Pdf_Invoice extends Mage_Sales_Model_Order_Pdf_Invoice
{
	public function getPdf($invoices = array())
    {
        $this->_beforeGetPdf();
        $this->_initRenderer('invoice');
 
        $pdf = new Zend_Pdf();
        $this->_setPdf($pdf);
        $style = new Zend_Pdf_Style();
        $this->_setFontBold($style, 10);
 
        foreach ($invoices as $invoice) {
            if ($invoice->getStoreId()) {
                Mage::app()->getLocale()->emulate($invoice->getStoreId());
                Mage::app()->setCurrentStore($invoice->getStoreId());
            }
            $page = $pdf->newPage(Zend_Pdf_Page::SIZE_A4);
            $pdf->pages[] = $page;
 
            $order = $invoice->getOrder();
 
            /* Add image */
            $this->insertLogo($page, $invoice->getStore());
 
            /* Add address */
            $this->insertAddress($page, $invoice->getStore());
 
            /* Add head */
            $this->insertOrder(
                $page, 
                $order, 
                Mage::getStoreConfigFlag(self::XML_PATH_SALES_PDF_INVOICE_PUT_ORDER_ID, $order->getStoreId())
            );
            /* Add document text and number */
            $this->insertDocumentNumber(
                $page,
                Mage::helper('sales')->__('Invoice # ') . $invoice->getIncrementId()
            );

            $page->setFillColor(new Zend_Pdf_Color_GrayScale(1));
            $this->_setFontRegular($page);
        //    $page->drawText(Mage::helper('sales')->__('Invoice # ') . $invoice->getIncrementId(), 35, 780, 'UTF-8');
            /***********************************************************/
            //IgorFabio - Added Survey Information to PDF, first get it if exists
            $orderId=$order->getid();
            $survey = Mage::getModel('onestepcheckout/survey')->load($orderId, 'order_id');
            $surveyquestion = $survey->getQuestion();
            $surveyanswer = $survey->getAnswer();
            if (isset($surveyquestion)){
                /* Add Survey Information table */
                $page->setFillColor(new Zend_Pdf_Color_RGB(0.93, 0.92, 0.92));
                $page->setLineColor(new Zend_Pdf_Color_GrayScale(0.5));
                $page->setLineWidth(0.5);
            //    $this->_setFontBold($style, 10);
     
                $page->drawRectangle(25, $this->y + 5, 570, $this->y - 15);
                $this->y -= 8;

                /* Add Survey Information table head */   
                $page->setFillColor(new Zend_Pdf_Color_RGB(0, 0, 0));
                $page->drawText(Mage::helper('sales')->__('Indagine domanda'), 35, $this->y, 'UTF-8');
                $page->drawText(Mage::helper('sales')->__('Risposta'), 240, $this->y, 'UTF-8');

                $this->y -=23;
                $page->drawText(Mage::helper('sales')->__($surveyquestion), 35, $this->y, 'UTF-8');
                $page->drawText(Mage::helper('sales')->__($surveyanswer), 240, $this->y, 'UTF-8');
                $this->y -=15;

                if ($this->y < 35) {
                        $page = $this->insertsurveyinformation(array('table_header' => true));
                }
            } 

            /* Add table */
            $page->setFillColor(new Zend_Pdf_Color_RGB(0.93, 0.92, 0.92));
            $page->setLineColor(new Zend_Pdf_Color_GrayScale(0.5));
            $page->setLineWidth(0.5);
 
            $page->drawRectangle(25, $this->y + 5, 570, $this->y -15);
            $this->y -=8;
            
            /* Add table head */
            $page->setFillColor(new Zend_Pdf_Color_RGB(0, 0, 0));
            $page->drawText(Mage::helper('sales')->__('Products'), 35, $this->y, 'UTF-8');
			$page->drawText(Mage::helper('sales')->__('SKU'), 240, $this->y, 'UTF-8');
            $page->drawText(Mage::helper('sales')->__('Price'), 360, $this->y, 'UTF-8');
            $page->drawText(Mage::helper('sales')->__('Qty'), 423, $this->y, 'UTF-8');
            $page->drawText(Mage::helper('sales')->__('Tax'), 465, $this->y, 'UTF-8');
            $page->drawText(Mage::helper('sales')->__('Subtotal'), 520, $this->y, 'UTF-8');
 
            $this->y -=25;
 
            $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
 
            /* Add body */
            foreach ($invoice->getAllItems() as $item){
                if ($item->getOrderItem()->getParentItem()) {
                    continue;
                }
 
                if ($this->y < 15) {
                    $page = $this->newPage(array('table_header' => true));
                }
 
                /* Draw item */
                $this->_drawItem($item, $page, $order);
                $page = end($pdf->pages);
//                $page = $this->_drawItem($item, $page, $order);
            }
 
            /* Add totals */
            $page = $this->insertTotals($page, $invoice);
 
            if ($invoice->getStoreId()) {
                Mage::app()->getLocale()->revert();
            }
        }
        $this->_afterGetPdf();
 
        return $pdf;
    }
 
	public function newPage(array $settings = array())
    {
        /* Add new table head */
        $page = $this->_getPdf()->newPage(Zend_Pdf_Page::SIZE_A4);
        $this->_getPdf()->pages[] = $page;
        $this->y = 800;
 
        if (!empty($settings['table_header'])) {
            $this->_setFontRegular($page);
            $page->setFillColor(new Zend_Pdf_Color_RGB(0.93, 0.92, 0.92));
            $page->setLineColor(new Zend_Pdf_Color_GrayScale(0.5));
            $page->setLineWidth(0.5);
            $page->drawRectangle(25, $this->y, 570, $this->y-15);
            $this->y -=10;
 
            $page->setFillColor(new Zend_Pdf_Color_RGB(0, 0, 0));
            $page->drawText(Mage::helper('sales')->__('Product'), 35, $this->y, 'UTF-8');
            $page->drawText(Mage::helper('sales')->__('SKU'), 325, $this->y, 'UTF-8');
            $page->drawText(Mage::helper('sales')->__('Price'), 380, $this->y, 'UTF-8');
            $page->drawText(Mage::helper('sales')->__('Qty'), 430, $this->y, 'UTF-8');
            $page->drawText(Mage::helper('sales')->__('Tax'), 480, $this->y, 'UTF-8');
            $page->drawText(Mage::helper('sales')->__('Subtotal'), 535, $this->y, 'UTF-8');
 
            $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
            $this->y -=20;
        }
        return $page;
    }

    /**
     * Insert Survey Information to pdf page- Igor Revenko
     *
     * @param Zend_Pdf_Page $page
     * @param Mage_Sales_Model_Abstract $invoice
     * return Zend_Pdf_Page
     */
    public function insertsurveyinformation(array $settings = array())
    {
        $page = $this->_getPdf()->newPage(Zend_Pdf_Page::SIZE_A4);
        $this->_getPdf()->pages[] = $page;
        $this->y = 800;

        if (!empty($settings['table_header'])) {
            $this->_setFontRegular($page);
            $page->setFillColor(new Zend_Pdf_Color_RGB(0.93, 0.92, 0.92));
            $page->setLineColor(new Zend_Pdf_Color_GrayScale(0.5));
            $page->setLineWidth(0.5);
            $page->drawRectangle(25, $this->y, 570, $this->y-15);
            $this->y -=10;
 
            $page->setFillColor(new Zend_Pdf_Color_RGB(0, 0, 0));
            $page->drawText(Mage::helper('sales')->__('Survey Question'), 35, $this->y, 'UTF-8');
            $page->drawText(Mage::helper('sales')->__('Answer'), 325, $this->y, 'UTF-8');
            $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
            $this->y -=20;
        }
        return $page;
    }

    
}