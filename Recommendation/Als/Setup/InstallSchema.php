<?php

namespace Recommendation\Als\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        //Products Token Table
        $table = $setup->getConnection()->newTable(
            $setup->getTable('recommendation_als_page_recommended_user')
        )->addColumn(
            'id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Id'
        )->addColumn(
            'page_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Page ID'
        )->addColumn(
            'store_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Store ID'
        )->setComment(
            'Recommendation Als Pagination Table'
        );

        $setup->getConnection()->createTable($table);

        $setup->endSetup();
    }
}
