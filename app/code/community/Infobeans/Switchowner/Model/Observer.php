<?php
/**
 * InfoBeans SwitchOwner Extension
 *
 * @category   Infobeans
 * @package    Infobeans_Switchowner
 * @version    1.0.1
 * @author     InfoBeans Technologies Limited http://www.infobeans.com/
 * @copyright  Copyright (c) 2016 InfoBeans Technologies Limited
 */

class Infobeans_Switchowner_Model_Observer
{
    /**
     * Helper
     *
     * @return Infobeans_Switchowner_Helper_Data
     */
    protected function _helper()
    {
        return Mage::helper('switchowner');
    }

    public function generateBlockAfter($event)
    {
        $block = $event->getBlock();

        // Order View
        if ($block && ($block->getNameInLayout() == 'sales_order_edit')) {
            if ($block instanceof Mage_Adminhtml_Block_Sales_Order_View) {

                /** @var Infobeans_Switchowner_Model_Order $order */
                $order = Mage::helper('switchowner/order')->getOrder();
                $state = explode(",", Mage::getStoreConfig('switchowner/general/orderstate'));
                $orderState = $order->getStatus();
                
                if (!in_array($orderState , $state)) {
                    if ($order->isGuestOrder() && $this->_helper()->isAllowed()) {
                        $block->addButton('switchOrder', array(
                            'label' => $this->_helper()->__("Switch Order Owner"),
                            'onclick' => "javascript: switchownerRowClick()",
                            'class' => 'switch-order',
                        ));
                    } else {
                        $block->addButton('switchOrder', array(
                            'label' => $this->_helper()->__("Switch Order Owner"),
                            'onclick' => "javascript: switchownerRowClick()",
                            'class' => 'switch-order',
                        ));
                    }
                }
            }
        }
    }

    public function massActionOption($observer)
    {
        if (!$this->_helper()->extEnabled()) {
            return;
        }

        $block = $observer->getBlock();
        $allowedNames = array(
            'Enterprise_SalesArchive_Block_Adminhtml_Sales_Order_Grid_Massaction',
            'Enterprise_Salesarchive_Block_Widget_Grid_Massaction',
            'Mage_Adminhtml_Block_Widget_Grid_Massaction',
        );

        if ($block && in_array(get_class($block), $allowedNames)) {
            $allowedControllerNames = array(
                'sales_order',
            );

            if ( in_array($block->getRequest()->getControllerName(), $allowedControllerNames) ) {
                $backendUrl = Mage::getSingleton('adminhtml/url');
                $block->addItem('switch', array(
                    'label' => $this->_helper()->__("Switch Order Owner"),
                    'url' =>  "javascript:switchownerRowClick();",
                ));
            }
        }
    }
}
