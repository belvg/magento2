<?php
/**
 * Magento Enterprise Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Enterprise Edition License
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magentocommerce.com/license/enterprise-edition
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category   Enterprise
 * @package    Enterprise_Invitation
 * @copyright  Copyright (c) 2008 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://www.magentocommerce.com/license/enterprise-edition
 */

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$installer->run("
DROP TABLE IF EXISTS `{$installer->getTable('enterprise_invitation/invitation')}`;
CREATE TABLE `{$installer->getTable('enterprise_invitation/invitation')}` (
    `invitation_id` INT UNSIGNED  NOT NULL AUTO_INCREMENT PRIMARY KEY ,
    `customer_id` INT( 10 ) UNSIGNED DEFAULT NULL ,
    `date` DATETIME NOT NULL ,
    `email` VARCHAR( 255 ) NOT NULL ,
    `referral_id` INT( 10 ) UNSIGNED DEFAULT NULL ,
    `protection_code` CHAR(32) NOT NULL,
    `signup_date` DATETIME DEFAULT NULL,
    `store_id` SMALLINT(5) UNSIGNED NOT NULL,
    `group_id` SMALLINT(3) UNSIGNED NOT NULL,
    `message` TEXT DEFAULT NULL,
    `status` ENUM('sent','accepted', 'canceled') NOT NULL,
    INDEX `IDX_customer_id` (`customer_id`),
    INDEX `IDX_referral_id` (`referral_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$fkName = $installer->getTable('enterprise_invitation/invitation');

$installer->getConnection()->addConstraint(
    strtoupper($fkName) . '_STORE',
    $installer->getTable('enterprise_invitation/invitation'),
    'store_id',
    $installer->getTable('core_store'),
    'store_id'
);

$installer->run("
DROP TABLE IF EXISTS `{$installer->getTable('enterprise_invitation/invitation_status_history')}`;
CREATE TABLE `{$installer->getTable('enterprise_invitation/invitation_status_history')}` (
    `history_id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `invitation_id` INT UNSIGNED NOT NULL,
    `date` DATETIME NOT NULL,
    `status` ENUM('sent','accepted', 'canceled') NOT NULL,
    INDEX `IDX_invitation_id` (`invitation_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$fkName = $installer->getTable('enterprise_invitation/invitation_status_history');

$installer->getConnection()->addConstraint(
    strtoupper($fkName). '_INVITATION',
    $installer->getTable('enterprise_invitation/invitation_status_history'),
    'invitation_id',
    $installer->getTable('enterprise_invitation/invitation'),
    'invitation_id'
);

$installer->endSetup();
