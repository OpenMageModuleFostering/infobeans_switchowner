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

class Infobeans_Switchowner_Block_Adminhtml_Sales_Order_View_Tabs_History extends Mage_Adminhtml_Block_Widget implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('infobeans/switchowner/order/view/tab/history.phtml');
    }

    /**
     * Current Order
     *
     * @return Infobeans_Switchowner_Model_Order
     */
    protected function _getOrder()
    {
        $id = Mage::app()->getRequest()->getParam('order_id');
        $order = Mage::getModel('switchowner/order')->load($id);
        return $order;
    }

    /**
     * Helper
     *
     * @return Infobeans_Switchowner_Helper_Data
     */
    protected function _helper()
    {
        return Mage::helper('switchowner');
    }

    public function getTabLabel()
    {
        return $this->_helper()->__("Switch Owner History");
    }

    public function getTabTitle()
    {
        return $this->_helper()->__("Switch Owner History");
    }

    public function canShowTab()
    {
        return $this->_getOrder()->hasOwnerSwitchHistory();
    }

    public function isHidden()
    {
        return false;
    }

    public function getHistory()
    {
        return $this->_getOrder()->getOwnerSwitchHistory();
    }

}