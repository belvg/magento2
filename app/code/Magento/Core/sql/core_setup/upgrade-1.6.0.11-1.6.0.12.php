<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

/* @var $installer \Magento\Setup\Module\SetupModule */
$installer = $this;

$installer->startSetup();
$connection = $installer->getConnection();

$oldName = 'core_theme_files_link';
$newName = 'core_theme_file_update';

$oldTableName = $installer->getTable($oldName);

/**
 * Drop foreign key and index
 */
$connection->dropForeignKey($oldTableName, $installer->getFkName($oldName, 'theme_id', 'core_theme', 'theme_id'));
$connection->dropIndex($oldTableName, $installer->getFkName($oldName, 'theme_id', 'core_theme', 'theme_id'));

/**
 * Rename table
 */
if ($installer->tableExists($oldName)) {
    $connection->renameTable($installer->getTable($oldName), $installer->getTable($newName));
}

$newTableName = $installer->getTable($newName);

/**
 * Rename column
 */
$oldColumn = 'files_link_id';
$newColumn = 'file_update_id';
$connection->changeColumn(
    $newTableName,
    $oldColumn,
    $newColumn,
    [
        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        'primary' => true,
        'nullable' => false,
        'unsigned' => true,
        'comment' => 'Customization file update id'
    ]
);

/**
 * Rename column
 */
$oldColumn = 'layout_link_id';
$newColumn = 'layout_update_id';
$connection->changeColumn(
    $newTableName,
    $oldColumn,
    $newColumn,
    [
        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        'nullable' => false,
        'unsigned' => true,
        'comment' => 'Theme layout update id'
    ]
);

/**
 * Add foreign keys and indexes
 */
$connection->addIndex(
    $newTableName,
    $installer->getIdxName(
        $newTableName,
        'theme_id',
        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
    ),
    'theme_id',
    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
);
$connection->addForeignKey(
    $installer->getFkName($newTableName, 'theme_id', 'core_theme', 'theme_id'),
    $newTableName,
    'theme_id',
    $installer->getTable('core_theme'),
    'theme_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
);
$connection->addIndex(
    $newTableName,
    $installer->getIdxName(
        $newTableName,
        'layout_update_id',
        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
    ),
    'layout_update_id',
    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
);
$connection->addForeignKey(
    $installer->getFkName($newTableName, 'layout_update_id', 'core_layout_update', 'layout_update_id'),
    $newTableName,
    'layout_update_id',
    $installer->getTable('core_layout_update'),
    'layout_update_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
);

/**
 * Change data
 */
$select = $connection->select()->from(
    $newTableName
)->join(
    ['link' => $installer->getTable('core_layout_link')],
    sprintf('link.layout_link_id = %s.layout_update_id', $newTableName)
);
$rows = $connection->fetchAll($select);
foreach ($rows as $row) {
    $connection->update(
        $newTableName,
        ['layout_update_id' => $row['layout_update_id']],
        'file_update_id = ' . $row['file_update_id']
    );
}

$installer->endSetup();
