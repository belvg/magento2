<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category   Mage
 * @package    Mage_Rating
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * FOREIGN KEY update
 *
 * @category   Mage
 * @package    Mage_Rating
 * @author     Victor Tihonchuk <victor@varien.com>
 */

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();
//KEY `subject_id` (`subject_id`),
//  KEY `object_id` (`object_id`),
//  KEY `event_type_id` (`event_type_id`),
//  KEY `store_id` (`store_id`),
//  KEY `subtype` (`subtype`)
$installer->run("
ALTER TABLE {$this->getTable('report_event')}
    DROP INDEX `event_type_id`,
    ADD INDEX `IDX_EVENT_TYPE` (`event_type_id`);
ALTER TABLE {$this->getTable('report_event')}
    ADD CONSTRAINT `FK_REPORT_EVENT_TYPE` FOREIGN KEY (`event_type_id`)
    REFERENCES {$this->getTable('report_event_types')} (`event_type_id`)
        ON UPDATE CASCADE
        ON DELETE CASCADE;
ALTER TABLE {$this->getTable('report_event')}
    DROP INDEX `subject_id`,
    ADD INDEX `IDX_SUBJECT` (`subject_id`);
ALTER TABLE {$this->getTable('report_event')}
    DROP INDEX `object_id`,
    ADD INDEX `IDX_OBJECT` (`object_id`);
ALTER TABLE {$this->getTable('report_event')}
    DROP INDEX `subtype`,
    ADD INDEX `IDX_SUBTYPE` (`subtype`);
ALTER TABLE {$this->getTable('report_event')}
    DROP INDEX `store_id`;
ALTER TABLE {$this->getTable('report_event')}
    CHANGE `store_id` `store_id` smallint(5) unsigned NOT NULL;
ALTER TABLE {$this->getTable('report_event')}
    ADD CONSTRAINT `FK_REPORT_EVENT_STORE` FOREIGN KEY (`store_id`)
    REFERENCES {$this->getTable('core_store')} (`store_id`)
        ON UPDATE CASCADE
        ON DELETE CASCADE;
");
$installer->endSetup();