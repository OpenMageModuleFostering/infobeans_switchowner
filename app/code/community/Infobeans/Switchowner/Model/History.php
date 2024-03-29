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

class Infobeans_Switchowner_Model_History extends Mage_Core_Model_Abstract
{
    protected $_order = null;
    protected $_details = null;

    public function _construct()
    {
        parent::_construct();
        $this->_init('switchowner/history');
    }

    public function getOrder()
    {
        if (!$this->_order) {
            if ($orderId = $this->getOrderId()) {
                $order = Mage::getModel('switchowner/order')->load($orderId);
                $this->_order = $order;
            } else {
                return false;
            }
        }

        return $this->_order;
    }

    public function applyOrder(Mage_Sales_Model_Order $order, $sendEmail = false)
    {
        $this->_order = $order;
        $timestamp = new Zend_Date();
        $this
            ->setOrderId($order->getId())
            ->setIsNotified($sendEmail ? 1 : 0)
            ->setAssignTime($timestamp->toString(Zend_Date::ISO_8601))
            ->save();

        return $this;
    }

    public function addDetails($key, $from = null, $to = null)
    {
        $detail = Mage::getModel('switchowner/detail');
        $detail->setHistoryId($this->getId())
            ->setDataKey($key)
            ->setFrom($from)
            ->setTo($to)
            ->save();
        return $this;
    }

    public function getDetails()
    {
        if (!$this->_details) {
            /** @var $collection  Infobeans_Switchowner_Model_Mysql4_Detail_Collection */
            $collection = Mage::getModel('switchowner/detail')->getCollection();
            $collection->addFieldToFilter('history_id', $this->getId());
            $this->_details = $collection;
        }

        return $this->_details;
    }

    public function hasDetails()
    {
        return !!$this->getDetails()->getSize();
    }

    public function getAssignTime()
    {
        $timezone = Mage::app()->getStore()->getConfig('general/locale/timezone');
        $date = new Zend_Date($this->getData('assign_time'), Zend_Date::ISO_8601);
        $date->setTimezone($timezone);
        return $date->toString(Zend_Date::DATETIME_MEDIUM);
    }

    public function getUrl($routePath = null, $routeParams = null)
    {
        /** @var $urlModel Mage_Adminhtml_Model_Url */
        $urlModel = Mage::getSingleton('adminhtml/url');

        return $urlModel->getUrl($routePath, $routeParams);
    }

    public function getCustomerUrl()
    {
        return $this->getUrl('adminhtml/customer/edit', array('id' => $this->getCustomer()->getId()));
    }

    public function getCustomer()
    {
        $customerId = null;
        foreach ($this->getDetails() as $detail) {
            if ($detail->getDataKey() == 'customer_id') {
                $customerId = $detail->getTo();
                break;
            }
        }

        if ($customerId) {
            $customer = Mage::getModel('customer/customer')->load($customerId);
            return $customer;
        }

        return new Varien_Object();
    }
    
    public function getAdminUrl()
    {
        return $this->getUrl('adminhtml/permissions_user/edit', array('user_id' => $this->getAdmin()->getUserId()));
    }
    
    public function getAdmin()
    {
        $adminId = null;
        foreach ($this->getDetails() as $detail) {
            if ($detail->getDataKey() == 'assignor') {
                $adminId = $detail->getTo();
                break;
            }
        }

        if ($adminId) {
            $adminData = Mage::getModel('admin/user')->load($adminId);
            return $adminData;
        }

        return new Varien_Object();
    }

    public function getFromData()
    {
        $data = array();

        foreach ($this->getDetails() as $detail) {
            $data[$detail->getDataKey()] = $detail->getFrom();
        }

        return $data;
    }
}