<?php
/**
 * @author Tiago Sampaio <tiago@tiagosampaio.com>
 */

namespace ZPay\Standard\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use ZPay\Standard\Model\ResourceModel\Transaction\Order;

/**
 * Class InstallSchema
 *
 * @package ZPay\Standard\Setup
 */
class InstallSchema implements InstallSchemaInterface
{

    /**
     * @param SchemaSetupInterface   $setup
     * @param ModuleContextInterface $context
     *
     * @throws \Zend_Db_Exception
     *
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        /** Create table zpay_standard_transaction_order */
        $tableName = $setup->getTable(Order::MAIN_TABLE);
        $setup->getConnection()->dropTable($tableName);

        $table = $setup->getConnection()->newTable($tableName)
            ->addColumn('id', Table::TYPE_SMALLINT, null, [
                'identity' => true,
                'nullable' => false,
                'primary'  => true
            ], 'Transaction ID.')
            ->addColumn('quote_id', Table::TYPE_INTEGER, null, [
                'unsigned' => true,
                'nullable' => false,
                'primary'  => true
            ])
            ->addColumn('order_id', Table::TYPE_INTEGER, null, [
                'unsigned' => true,
                'nullable' => false,
                'primary'  => true
            ])
            ->addColumn('zpay_order_id', Table::TYPE_TEXT, 50, [
                'nullable' => false,
            ])
            ->addColumn('zpay_quote_id', Table::TYPE_TEXT, 50, [
                'nullable' => false,
            ])
            ->addColumn('zpay_address', Table::TYPE_TEXT, 50, [
                'nullable' => false,
            ])
            ->addColumn('zpay_amount_to', Table::TYPE_DECIMAL, '20,20', [
                'nullable' => false,
            ])
            ->addColumn('zpay_order_status', Table::TYPE_TEXT, 50, [
                'nullable' => true,
            ])
            ->addColumn('zpay_payout_status', Table::TYPE_TEXT, 50, [
                'nullable' => true,
            ])
            ->addColumn('zpay_time', Table::TYPE_INTEGER, 25, [
                'nullable' => false,
            ])
            ->addColumn('zpay_timestamp', Table::TYPE_DATETIME, 25, [
                'nullable' => false,
            ])
            ->addForeignKey(
                $setup->getFkName($tableName, 'quote_id', 'quote', 'entity_id'),
                'quote_id',
                $setup->getTable('quote'),
                'entity_id',
                Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $setup->getFkName($tableName, 'order_id', 'sales_order', 'entity_id'),
                'order_id',
                $setup->getTable('sales_order'),
                'entity_id',
                Table::ACTION_CASCADE
            );
        $setup->getConnection()->createTable($table);

        $setup->endSetup();
    }
}
