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

$installer->run("
ALTER TABLE {$this->getTable('rating_option_vote_aggregated')}
    ADD CONSTRAINT `FK_RATING_OPTION_VOTE_AGGREGATED_STORE` FOREIGN KEY (`store_id`)
    REFERENCES {$this->getTable('core_store')} (`store_id`)
        ON UPDATE CASCADE
        ON DELETE CASCADE;
");
$installer->run("
ALTER TABLE {$this->getTable('rating_store')}
    ADD CONSTRAINT `FK_RATING_STORE_STORE` FOREIGN KEY (`store_id`)
    REFERENCES {$this->getTable('core_store')} (`store_id`)
        ON UPDATE CASCADE
        ON DELETE CASCADE;
");
$installer->run("
ALTER TABLE {$this->getTable('rating_title')}
    ADD CONSTRAINT `FK_RATING_TITLE_STORE` FOREIGN KEY (`store_id`)
    REFERENCES {$this->getTable('core_store')} (`store_id`)
        ON UPDATE CASCADE
        ON DELETE CASCADE;
");
$installer->endSetup();