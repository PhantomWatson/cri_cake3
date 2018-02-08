<?php
namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * DeliverablesFixture
 *
 */
class DeliverablesFixture extends TestFixture
{

    /**
     * Fields
     *
     * @var array
     */
    // @codingStandardsIgnoreStart
    public $fields = [
        'id' => ['type' => 'integer', 'length' => 11, 'unsigned' => false, 'null' => false, 'default' => null, 'comment' => '', 'autoIncrement' => true, 'precision' => null],
        'name' => ['type' => 'string', 'length' => 100, 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'delivered_by' => ['type' => 'string', 'length' => 100, 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'delivered_to' => ['type' => 'string', 'length' => 100, 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
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
            'name' => 'Presentation A materials',
            'delivered_by' => 'CBER',
            'delivered_to' => 'ICI'
        ],
        [
            'id' => 2,
            'name' => 'Presentation B materials',
            'delivered_by' => 'CBER',
            'delivered_to' => 'ICI'
        ],
        [
            'id' => 3,
            'name' => 'Presentation C materials',
            'delivered_by' => 'CBER',
            'delivered_to' => 'ICI'
        ],
        [
            'id' => 4,
            'name' => 'Presentation D materials',
            'delivered_by' => 'CBER',
            'delivered_to' => 'ICI'
        ],
        [
            'id' => 5,
            'name' => 'policy development',
            'delivered_by' => 'CBER / ICI',
            'delivered_to' => 'client community'
        ]
    ];
}
