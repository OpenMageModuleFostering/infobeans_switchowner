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

class Infobeans_Switchowner_Model_Mysql4_History_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected $_last = null;

    protected function _construct()
    {
        parent::_construct();
        $this->_init('switchowner/history');
    }

    protected function _afterLoad()
    {
        parent::_afterLoad();
        foreach ($this->getItems() as $item) {
            $this->_last = $item;
        }
    }

    /**
     * Last Item
     *
     * @return Infobeans_Switchowner_Model_History
     */
    public function getLastItem()
    {
        if (!$this->isLoaded()) {
            $this->load();
        }

        return $this->_last;
    }
}
