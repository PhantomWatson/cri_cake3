<?php
namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * ProductsFixture
 *
 */
class ProductsFixture extends TestFixture
{

    /**
     * Fields
     *
     * @var array
     */
    // @codingStandardsIgnoreStart
    public $fields = [
        'id' => ['type' => 'integer', 'length' => 11, 'unsigned' => false, 'null' => false, 'default' => null, 'comment' => '', 'autoIncrement' => true, 'precision' => null],
        'description' => ['type' => 'string', 'length' => 100, 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'item_code' => ['type' => 'string', 'length' => 10, 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'price' => ['type' => 'integer', 'length' => 11, 'unsigned' => false, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null, 'autoIncrement' => null],
        'step' => ['type' => 'integer', 'length' => 11, 'unsigned' => false, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null, 'autoIncrement' => null],
        'prerequisite' => ['type' => 'integer', 'length' => 11, 'unsigned' => false, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null, 'autoIncrement' => null],
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
            'id' => 1,
            'description' => 'Community Leadership Alignment Assessment',
            'item_code' => 'S482',
            'price' => 3500,
            'step' => 1,
            'prerequisite' => null
        ],
        [
            'id' => 2,
            'description' => 'Leadership Summit',
            'item_code' => 'S483',
            'price' => 1500,
            'step' => 2,
            'prerequisite' => 1
        ],
        [
            'id' => 3,
            'description' => 'Community Organizations Alignment Assessment',
            'item_code' => 'S484',
            'price' => 3500,
            'step' => 2,
            'prerequisite' => 1
        ],
        [
            'id' => 4,
            'description' => 'Facilitated Community Awareness Conversation',
            'item_code' => 'S485',
            'price' => 1500,
            'step' => 3,
            'prerequisite' => 3
        ],
        [
            'id' => 5,
            'description' => 'PWRRR Policy Development',
            'item_code' => 'S486',
            'price' => 5000,
            'step' => 3,
            'prerequisite' => 3
        ],
    ];
}
