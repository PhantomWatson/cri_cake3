<?php
namespace App\Test\Fixture;

use App\Model\Table\ProductsTable;
use Cake\TestSuite\Fixture\TestFixture;

/**
 * PurchasesFixture
 *
 */
class PurchasesFixture extends TestFixture
{
    /**
     * Fields
     *
     * @var array
     */
    // @codingStandardsIgnoreStart
    public $fields = [
        'id' => ['type' => 'integer', 'length' => 11, 'unsigned' => false, 'null' => false, 'default' => null, 'comment' => '', 'autoIncrement' => true, 'precision' => null],
        'user_id' => ['type' => 'integer', 'length' => 11, 'unsigned' => false, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null, 'autoIncrement' => null],
        'community_id' => ['type' => 'integer', 'length' => 11, 'unsigned' => false, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null, 'autoIncrement' => null],
        'product_id' => ['type' => 'integer', 'length' => 11, 'unsigned' => false, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null, 'autoIncrement' => null],
        'postback' => ['type' => 'text', 'length' => null, 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => '', 'precision' => null],
        'admin_added' => ['type' => 'boolean', 'length' => null, 'null' => false, 'default' => '0', 'comment' => '', 'precision' => null],
        'source' => ['type' => 'string', 'length' => 5, 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'notes' => ['type' => 'text', 'length' => null, 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => '', 'precision' => null],
        'created' => ['type' => 'datetime', 'length' => null, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null],
        'refunded' => ['type' => 'datetime', 'length' => null, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null],
        'refunder_id' => ['type' => 'integer', 'length' => 11, 'unsigned' => false, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null, 'autoIncrement' => null],
        '_constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id'], 'length' => []],
        ],
        '_options' => [
            'engine' => 'InnoDB',
            'collation' => 'utf8_general_ci'
        ],
    ];
    // @codingStandardsIgnoreEnd

    /**
     * Records
     *
     * @var array
     */
    public $records = [
        [
            'community_id' => 1,
            'product_id' => ProductsTable::OFFICIALS_SURVEY,
        ],
        [
            'community_id' => 4,
            'product_id' => ProductsTable::OFFICIALS_SURVEY,
        ],
        [
            'community_id' => 4,
            'product_id' => ProductsTable::ORGANIZATIONS_SURVEY,
        ],
        [
            'community_id' => 4,
            'product_id' => ProductsTable::OFFICIALS_SUMMIT,
        ],
        [
            'community_id' => 4,
            'product_id' => ProductsTable::ORGANIZATIONS_SUMMIT,
        ],
        [
            'community_id' => 5,
            'product_id' => ProductsTable::OFFICIALS_SURVEY,
        ],
        [
            'community_id' => 5,
            'product_id' => ProductsTable::ORGANIZATIONS_SURVEY,
        ],
        [
            'community_id' => 6,
            'product_id' => ProductsTable::OFFICIALS_SURVEY,
        ],
        [
            'community_id' => 6,
            'product_id' => ProductsTable::ORGANIZATIONS_SURVEY,
        ],
        [
            'community_id' => 7,
            'product_id' => ProductsTable::OFFICIALS_SURVEY,
        ],
        [
            'community_id' => 7,
            'product_id' => ProductsTable::OFFICIALS_SUMMIT,
        ],
        [
            'community_id' => 7,
            'product_id' => ProductsTable::ORGANIZATIONS_SURVEY,
        ],
        [
            'community_id' => 7,
            'product_id' => ProductsTable::ORGANIZATIONS_SUMMIT,
        ],
    ];

    /**
     * Initialization method
     *
     * @return void
     */
    public function init()
    {
        parent::init();
        $defaultData = [
            'user_id' => 1,
            'postback' => '',
            'admin_added' => true,
            'source' => 'ocra',
            'notes' => 'Paid for by OCRA',
            'created' => '2016-01-22 16:55:26',
            'refunded' => null,
            'refunder_id' => null,
        ];

        foreach ($this->records as $n => &$record) {
            $record += $defaultData;
            $record['id'] = $n + 1;
        }
    }
}
