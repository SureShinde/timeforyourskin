<?php
/**
 * IgorFabio Invoice Grid rewrite for ading extra fields
 * Attribute: Payment method and shipping fee have to be added to default fields.
 * Original: Adminhtml Block Sales Invoice Grid default items renderer
 *
 * @category   IgorFabio
 * @package    IgorFabio_Invoice
 * @author     Igor Revenco - IgorFabio <igorexpert1012@gmail.com>
 */
/**
 * Adminhtml sales orders grid
 *
 * @author      Igor Revenco
 */
class IgorFabio_Invoice_Adminhtml_Block_Sales_Invoice_Grid extends Mage_Adminhtml_Block_Sales_Invoice_Grid
{

//    protected function _exportExcelItem(Varien_Object $item, Varien_Io_File $adapter, $parser = null)
    protected function _exportCsvItem(Varien_Object $item, Varien_Io_File $adapter)
    {
        // if (is_null($parser)) {
        //     $parser = new Varien_Convert_Parser_Xml_Excel();
        // }

        $row = array();

        $this->addColumn('increment_id', array(
            'header'    => Mage::helper('sales')->__('Invoice #'),
            'index'     => 'increment_id',
            'type'      => 'text',
        ));

        // $this->addColumn('created_at', array(
        //     'header'    => Mage::helper('sales')->__('Invoice Date'),
        //     'index'     => 'created_at',
        //     'type'      => 'datetime',
        // ));

        // $this->addColumn('order_increment_id', array(
        //     'header'    => Mage::helper('sales')->__('Order #'),
        //     'index'     => 'order_increment_id',
        //     'type'      => 'text',
        // ));

        // $this->addColumn('order_created_at', array(
        //     'header'    => Mage::helper('sales')->__('Order Date'),
        //     'index'     => 'order_created_at',
        //     'type'      => 'datetime',
        // ));

        // $this->addColumn('billing_name', array(
        //     'header' => Mage::helper('sales')->__('Bill to Name'),
        //     'index' => 'billing_name',
        // ));

        // $this->addColumn('state', array(
        //     'header'    => Mage::helper('sales')->__('Status'),
        //     'index'     => 'state',
        //     'type'      => 'options',
        //     'options'   => Mage::getModel('sales/order_invoice')->getStates(),
        // ));

        // $this->addColumn('grand_total', array(
        //     'header'    => Mage::helper('customer')->__('Amount'),
        //     'index'     => 'grand_total',
        //     'type'      => 'currency',
        //     'align'     => 'right',
        //     'currency'  => 'order_currency_code',
        // ));

        /**
        * Payment information
        */
        // $this->addColumn('method', array(
        //     'header'    => Mage::helper('sales')->__('Payment Information'),
        //     'index'     => 'method',
        //     'type'      => 'text',
        // ));

        // /**
        // * Shipping information
        // */
        // $this->addColumn('shipping_amount', array(
        //     'header'    => Mage::helper('sales')->__('Shipping Cost'),
        //     'index'     => 'shipping_amount',
        //     'type'      => 'currency',
        //     'align'     => 'right',
        //     'currency'  => 'order_currency_code',
        // ));
 
        $this->addExportType('*/*/exportExcel', Mage::helper('sales')->__('Excel XML'));
        $this->addExportType('*/*/exportCsv', Mage::helper('sales')->__('CSV'));

        foreach ($this->_columns as $column) {
            if (!$column->getIsSystem()) {
                $row[] = $column->getRowFieldExport($item);
            }
        }

        $adapter->streamWriteCsv(
            Mage::helper("core")->getEscapedCSVData($row)
        );
        
    }
    protected function _prepareCollection()
    {        
       $collection = Mage::getResourceModel($this->_getCollectionClass());
//        $this->setCollection($collection);
//        $collection->getSelect();

       $collection->getSelect()->joinLeft(array('gsfop'=> 'gjd_sales_flat_order_payment'), 'gsfop.entity_id = main_table.order_id', array('gsfop.shipping_amount','gsfop.method'));

       $collection->getSelect()->joinLeft(array('gsfo'=> 'gjd_sales_flat_order'), 'gsfo.entity_id = main_table.order_id', array('gsfo_created_at'=>'gsfo.created_at','gsfo_state'=>'gsfo.state','gsfo.shipping_method','gsfo.status'));
       
        $collection->getSelect()->joinLeft(array('sfoa'=>'gjd_sales_flat_order_address'),'main_table.order_id = sfoa.parent_id AND sfoa.address_type="shipping"',array('sfoa.street','sfoa.city','sfoa.region','sfoa.postcode','sfoa.telephone','sfoa.country_id')); // New
        
        // $collection->getSelect()->joinLeft(array('ship_country'=> 'gjd_sales_flat_order_address'), 'main_table.order_id = ship_country.parent_id AND ship_country.address_type="shipping"', array('ship_country.country_id'));
        
        // $collection->getSelect()->joinLeft(array('payment_info'=> 'gjd_sales_flat_order_payment'), 'payment_info.entity_id = main_table.order_id', array('payment_info.method'));

        $this->setCollection($collection);
        return Mage_Adminhtml_Block_Widget_Grid::_prepareCollection();
    
    }

    protected function _prepareColumns()
    {
        $this->addColumn('increment_id', array(
            'header'    => Mage::helper('sales')->__('Invoice #'),
            'index'     => 'increment_id',
            'type'      => 'text',
            'filter_index' => 'main_table.increment_id',
        ));

        $this->addColumn('created_at', array(
            'header'    => Mage::helper('sales')->__('Invoice Date'),
            'index'     => 'created_at',
            'type'      => 'datetime',
            'filter_index' => 'main_table.created_at',
        ));
        $this->addColumn('order_increment_id', array(
            'header'    => Mage::helper('sales')->__('Order #'),
            'index'     => 'order_increment_id',
            'type'      => 'text',
            'filter_index' => 'main_table.order_increment_id',
        ));

        $this->addColumn('order_created_at', array(
            'header'    => Mage::helper('sales')->__('Order Date'),
            'index'     => 'order_created_at',
            'type'      => 'datetime',
            'filter_index' => 'main_table.order_created_at',

        ));

        $this->addColumn('billing_name', array(
            'header' => Mage::helper('sales')->__('Bill to Name'),
            'index' => 'billing_name',
            'filter_index' => 'main_table.billing_name', 
        ));

        $this->addColumn('state', array(
            'header'    => Mage::helper('sales')->__('Status'),
            'index'     => 'state',
            'type'      => 'options',
            'options'   => Mage::getModel('sales/order_invoice')->getStates(),
            'filter_index' => 'main_table.state',            
        ));

        $this->addColumn('grand_total', array(
            'header'    => Mage::helper('customer')->__('Amount'),
            'index'     => 'grand_total',
            'type'      => 'currency',
            'align'     => 'right',
            'currency'  => 'order_currency_code',
            'filter_index' => 'main_table.grand_total', 
        ));
////////////////////////////////////////////////////////////////////////////        
        /**
        * Shipping information
        */
        $this->addColumn('shipping_amount', array(
            'header'    => Mage::helper('sales')->__('Shipping Cost'),
            'index'     => 'shipping_amount',            
            'type'      => 'currency',
            'align'     => 'right',
            'currency'  => 'order_currency_code', 
            'filter_index' => 'gsfop.shipping_amount',           
        ));
        $this->addColumn('shipping_method', array(
            'header'    => Mage::helper('sales')->__('Shipping Method'),
            'index'     => 'shipping_method',
            'type'      => 'text',
            'filter_index' => 'gsfo.shipping_method',
        ));
        $this->addColumn('status', array(
            'header'    => Mage::helper('sales')->__('Order Status'),
            'index'     => 'status',            
            'type'      => 'options',
            'filter_index' => 'gsfo.status',
            'options' => Mage::getSingleton('sales/order_config')->getStatuses(),
        ));
        $this->addColumn('city', array(
            'header'    => Mage::helper('sales')->__('Shipping City'),
            'index'     => 'city',
            'type'      => 'text',
            'filter_index' => 'sfoa.city',
        ));
        $this->addColumn('country_id', array(
            'header'    => Mage::helper('sales')->__('Shipping Country'),
            'index'     => 'country_id',
            'type'      => 'text',
            'filter_index' => 'sfoa.country_id',
        ));
        
        // * Payment information
        
        $this->addColumn('method', array(
            'header'    => Mage::helper('sales')->__('Payment Information'),
            'index'     => 'method',
            'type'      => 'text',
            'filter_index' => 'gsfop.method',
        ));

////////////////////////////////////////////////////////////////////////
        $this->addColumn('action',
            array(
                'header'    => Mage::helper('sales')->__('Action'),
                'width'     => '50px',
                'type'      => 'action',
                'getter'     => 'getId',
                'actions'   => array(
                    array(
                        'caption' => Mage::helper('sales')->__('View'),
                        'url'     => array('base'=>'*/sales_invoice/view'),
                        'field'   => 'invoice_id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'is_system' => true
        ));
        
         $this->addExportType('*/*/exportExcel', Mage::helper('sales')->__('Excel XML'));
         $this->addExportType('*/*/exportCsv', Mage::helper('sales')->__('CSV'));

    //    return parent::_prepareColumns();
        return Mage_Adminhtml_Block_Widget_Grid::_prepareColumns();
    }

}
