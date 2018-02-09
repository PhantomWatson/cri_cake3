<?php
namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * CommunitiesFixture
 *
 */
class CommunitiesFixture extends TestFixture
{

    /**
     * Fields
     *
     * @var array
     */
    // @codingStandardsIgnoreStart
    public $fields = [
        'id' => ['type' => 'integer', 'length' => 11, 'unsigned' => false, 'null' => false, 'default' => null, 'comment' => '', 'autoIncrement' => true, 'precision' => null],
        'name' => ['type' => 'string', 'length' => 200, 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'local_area_id' => ['type' => 'integer', 'length' => 11, 'unsigned' => false, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null, 'autoIncrement' => null],
        'parent_area_id' => ['type' => 'integer', 'length' => 11, 'unsigned' => false, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null, 'autoIncrement' => null],
        'public' => ['type' => 'boolean', 'length' => null, 'null' => false, 'default' => '0', 'comment' => '', 'precision' => null],
        'fast_track' => ['type' => 'boolean', 'length' => null, 'null' => false, 'default' => '0', 'comment' => '', 'precision' => null],
        'score' => ['type' => 'decimal', 'length' => 2, 'precision' => 1, 'unsigned' => false, 'null' => false, 'default' => '0.0', 'comment' => ''],
        'town_meeting_date' => ['type' => 'date', 'length' => null, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null],
        'intAlignmentAdjustment' => ['type' => 'decimal', 'length' => 4, 'precision' => 2, 'unsigned' => false, 'null' => false, 'default' => '8.98', 'comment' => ''],
        'intAlignmentThreshold' => ['type' => 'decimal', 'length' => 4, 'precision' => 2, 'unsigned' => false, 'null' => false, 'default' => '1.00', 'comment' => ''],
        'presentation_a' => ['type' => 'date', 'length' => null, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null],
        'presentation_b' => ['type' => 'date', 'length' => null, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null],
        'presentation_c' => ['type' => 'date', 'length' => null, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null],
        'presentation_d' => ['type' => 'date', 'length' => null, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null],
        'dummy' => ['type' => 'boolean', 'length' => null, 'null' => false, 'default' => '0', 'comment' => '', 'precision' => null],
        'notes' => ['type' => 'text', 'length' => null, 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => '', 'precision' => null],
        'active' => ['type' => 'boolean', 'length' => null, 'null' => false, 'default' => '1', 'comment' => '', 'precision' => null],
        'slug' => ['type' => 'string', 'length' => 100, 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'created' => ['type' => 'datetime', 'length' => null, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null],
        'modified' => ['type' => 'datetime', 'length' => null, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null],
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
            'name' => 'Test Community (public)',
            'public' => true
        ],
        [
            'id' => 2,
            'name' => 'Test Community (non-public)',
            'public' => false
        ],
        [
            'id' => 3,
            'name' => 'Test Community (inactive)',
            'active' => false
        ],
        [
            'id' => 4,
            'name' => 'Test Community (alertable: deliver presentations)',
        ],
        [
            'id' => 5,
            'name' => 'Test Community (alertable: create surveys)',
        ],
        [
            'id' => 6,
            'name' => 'Test Community (alertable: activate surveys)',
        ],
        [
            'id' => 7,
            'name' => 'Test Community (alertable: schedule presentations)',
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
            'name' => 'Test Community (default data)',
            'local_area_id' => 2,
            'parent_area_id' => 1,
            'public' => false,
            'fast_track' => 0,
            'score' => 1,
            'town_meeting_date' => null,
            'intAlignmentAdjustment' => 8.98,
            'intAlignmentThreshold' => 1.0,
            'presentation_a' => null,
            'presentation_b' => null,
            'presentation_c' => null,
            'presentation_d' => null,
            'dummy' => false,
            'notes' => 'Notes...',
            'active' => true,
            'slug' => 'test-community',
            'created' => '2017-04-18 23:18:27',
            'modified' => '2017-04-18 23:18:27'
        ];

        foreach ($this->records as &$record) {
            $record += $defaultData;
            $record['slug'] = 'test-community-' . $record['id'];
        }
    }
}
