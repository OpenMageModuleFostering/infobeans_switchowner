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

$installer = $this;

$installer->startSetup();
$installer->run(
    "

DROP TABLE IF EXISTS {$this->getTable('ib_switchowner_history_details')};
DROP TABLE IF EXISTS {$this->getTable('ib_switchowner_history')};

CREATE TABLE IF NOT EXISTS {$this->getTable('ib_switchowner_history')} (
  `history_id` int(11) unsigned NOT NULL auto_increment,
  `assign_time` timestamp NOT NULL,
  `order_id` int(10) unsigned NOT NULL,
  `is_notified` smallint(1) unsigned DEFAULT 0 NOT NULL,
  PRIMARY KEY (`history_id`),
  KEY (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS {$this->getTable('ib_switchowner_history_details')} (
  `detail_id` int(15) unsigned NOT NULL auto_increment,
  `history_id` int(11) unsigned NOT NULL,
  `data_key` varchar(255) NOT NULL,
  `from` varchar(255) NULL,
  `to` varchar(255) NULL,
  PRIMARY KEY (`detail_id`),
  KEY `FK_IB_AORDER_DETAIL_HISTORY` (`history_id`),
  CONSTRAINT `FK_IB_AORDER_DETAIL_HISTORY` FOREIGN KEY (`history_id`) REFERENCES `{$this->getTable('ib_switchowner_history')}` (`history_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    "
);

$installer->endSetup(); 