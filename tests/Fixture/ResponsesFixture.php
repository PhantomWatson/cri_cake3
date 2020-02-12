<?php
namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * ResponsesFixture
 *
 */
class ResponsesFixture extends TestFixture
{
    /**
     * Fields
     *
     * @var array
     */
    // @codingStandardsIgnoreStart
    public $fields = [
        'id' => ['type' => 'integer', 'length' => 11, 'unsigned' => false, 'null' => false, 'default' => null, 'comment' => '', 'autoIncrement' => true, 'precision' => null],
        'respondent_id' => ['type' => 'integer', 'length' => 11, 'unsigned' => false, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null, 'autoIncrement' => null],
        'survey_id' => ['type' => 'integer', 'length' => 11, 'unsigned' => false, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null, 'autoIncrement' => null],
        'response' => ['type' => 'text', 'length' => null, 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => '', 'precision' => null],
        'production_rank' => ['type' => 'integer', 'length' => 4, 'unsigned' => false, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null, 'autoIncrement' => null],
        'wholesale_rank' => ['type' => 'integer', 'length' => 4, 'unsigned' => false, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null, 'autoIncrement' => null],
        'retail_rank' => ['type' => 'integer', 'length' => 4, 'unsigned' => false, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null, 'autoIncrement' => null],
        'residential_rank' => ['type' => 'integer', 'length' => 4, 'unsigned' => false, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null, 'autoIncrement' => null],
        'recreation_rank' => ['type' => 'integer', 'length' => 4, 'unsigned' => false, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null, 'autoIncrement' => null],
        'alignment_vs_local' => ['type' => 'integer', 'length' => 4, 'unsigned' => false, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null, 'autoIncrement' => null],
        'alignment_vs_parent' => ['type' => 'integer', 'length' => 4, 'unsigned' => false, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null, 'autoIncrement' => null],
        'aware_of_plan' => ['type' => 'boolean', 'length' => null, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null],
        'response_date' => ['type' => 'datetime', 'length' => null, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null],
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
            'respondent_id' => 1,
            'survey_id' => 1,
        ],
        [
            'respondent_id' => 2,
            'survey_id' => 4,
        ],
        [
            'respondent_id' => 3,
            'survey_id' => 5,
        ],
    ];

    // @codingStandardsIgnoreStart
    /**
     * Initialization method
     *
     * @return void
     */
    public function init()
    {
        parent::init();
        $defaultData = [
            'response' => 'YToxNzp7aTowO2E6Mjp7czo3OiJhbnN3ZXJzIjthOjQ6e2k6MDthOjI6e3M6NDoidGV4dCI7czoxMDoiVG9tIERlQmF1biI7czozOiJyb3ciO3M6MTA6Ijc2NTc2MzM5OTciO31pOjE7YToyOntzOjQ6InRleHQiO3M6NToiTWF5b3IiO3M6Mzoicm93IjtzOjEwOiI3NjU3NjMzOTk4Ijt9aToyO2E6Mjp7czo0OiJ0ZXh0IjtzOjE5OiJDaXR5IG9mIFNoZWxieXZpbGxlIjtzOjM6InJvdyI7czoxMDoiNzY1NzYzMzk5OSI7fWk6MzthOjI6e3M6NDoidGV4dCI7czoxMDoibm90IGxpc3RlZCI7czozOiJyb3ciO3M6MTA6Ijc2NTc2MzQwMDAiO319czoxMToicXVlc3Rpb25faWQiO3M6OToiNjYzMTU3MDQ1Ijt9aToxO2E6Mjp7czo3OiJhbnN3ZXJzIjthOjE6e2k6MDthOjI6e3M6NDoidGV4dCI7czoxOToiQ2l0eSBvZiBTaGVsYnl2aWxsZSI7czozOiJyb3ciO3M6MToiMCI7fX1zOjExOiJxdWVzdGlvbl9pZCI7czo5OiI2NjMxNTcwNDYiO31pOjI7YToyOntzOjc6ImFuc3dlcnMiO2E6Mzp7aTowO2E6MTp7czozOiJyb3ciO3M6MTA6Ijc4MjI4NDQzODAiO31pOjE7YToxOntzOjM6InJvdyI7czoxMDoiNzgyMjg0NDM4MSI7fWk6MjthOjI6e3M6NDoidGV4dCI7czoyNDoiQ2l0eS0gMjAxMSwgQ291bnR5IDIwMTA/IjtzOjM6InJvdyI7czoxMDoiNzgyMjg0NDM3OCI7fX1zOjExOiJxdWVzdGlvbl9pZCI7czo5OiI2NjMxNTcwNDciO31pOjM7YToyOntzOjc6ImFuc3dlcnMiO2E6MTp7aTowO2E6Mjp7czo0OiJ0ZXh0IjtzOjczOiJOb3QgdGhhdCBJIGFtIGF3YXJlIG9mLiAgV2UgZG8gaGF2ZSBTYW5pdGFyeSBhbmQgU3Rvcm0gU2V3ZXIgbWFzdGVyIFBsYW5zIjtzOjM6InJvdyI7czoxOiIwIjt9fXM6MTE6InF1ZXN0aW9uX2lkIjtzOjk6IjY2MzE1NzA0OCI7fWk6NDthOjI6e3M6NzoiYW5zd2VycyI7YToxOntpOjA7YToyOntzOjQ6InRleHQiO3M6OToiTm8gYW5zd2VyIjtzOjM6InJvdyI7czoxOiIwIjt9fXM6MTE6InF1ZXN0aW9uX2lkIjtzOjk6IjY2MzE1NzA0OSI7fWk6NTthOjI6e3M6NzoiYW5zd2VycyI7YTo1OntpOjA7YToyOntzOjQ6InRleHQiO3M6MTE6IkFteSBIYWFja2VyIjtzOjM6InJvdyI7czoxMDoiNzY1NDIzODcxMSI7fWk6MTthOjI6e3M6NDoidGV4dCI7czo0OiJCUkNGIjtzOjM6InJvdyI7czoxMDoiNzY1NDIzODcxMiI7fWk6MjthOjI6e3M6NDoidGV4dCI7czoyOiJFRCI7czozOiJyb3ciO3M6MTA6Ijc2NTQyMzg3MTMiO31pOjM7YToyOntzOjQ6InRleHQiO3M6MzI6ImFoYWFja2VyQGJsdWVyaXZlcmZvdW5kYXRpb24uY29tIjtzOjM6InJvdyI7czoxMDoiNzY1NDIzODcxNCI7fWk6NDthOjI6e3M6NDoidGV4dCI7czoxMDoiMzE3MzkyNzk1NSI7czozOiJyb3ciO3M6MTA6Ijc2NTQyMzg3MTUiO319czoxMToicXVlc3Rpb25faWQiO3M6OToiNjYzMTU3MDUwIjt9aTo2O2E6Mjp7czo3OiJhbnN3ZXJzIjthOjE6e2k6MDthOjI6e3M6NDoidGV4dCI7czo5OiJObyBhbnN3ZXIiO3M6Mzoicm93IjtzOjE6IjAiO319czoxMToicXVlc3Rpb25faWQiO3M6OToiNjYzMTcwNzQ4Ijt9aTo3O2E6Mjp7czo3OiJhbnN3ZXJzIjthOjE6e2k6MDthOjI6e3M6NDoidGV4dCI7czo5OiJObyBhbnN3ZXIiO3M6Mzoicm93IjtzOjE6IjAiO319czoxMToicXVlc3Rpb25faWQiO3M6OToiNjYzMTcxNDAyIjt9aTo4O2E6Mjp7czo3OiJhbnN3ZXJzIjthOjE6e2k6MDthOjI6e3M6NDoidGV4dCI7czo5OiJObyBhbnN3ZXIiO3M6Mzoicm93IjtzOjE6IjAiO319czoxMToicXVlc3Rpb25faWQiO3M6OToiNjYzNDg5MjIxIjt9aTo5O2E6Mjp7czo3OiJhbnN3ZXJzIjthOjE6e2k6MDthOjI6e3M6NDoidGV4dCI7czo5OiJObyBhbnN3ZXIiO3M6Mzoicm93IjtzOjE6IjAiO319czoxMToicXVlc3Rpb25faWQiO3M6OToiNjYzNDg5NDc3Ijt9aToxMDthOjI6e3M6NzoiYW5zd2VycyI7YToxOntpOjA7YToxOntzOjM6InJvdyI7czoxMDoiNzY1Nzc5NTU0MCI7fX1zOjExOiJxdWVzdGlvbl9pZCI7czo5OiI2NjM0OTA2MjAiO31pOjExO2E6Mjp7czo3OiJhbnN3ZXJzIjthOjE6e2k6MDthOjE6e3M6Mzoicm93IjtzOjEwOiI3ODIyNjE0NDgyIjt9fXM6MTE6InF1ZXN0aW9uX2lkIjtzOjk6IjY2MzQ5MTcyOCI7fWk6MTI7YToyOntzOjc6ImFuc3dlcnMiO2E6NTp7aTowO2E6Mjp7czozOiJjb2wiO3M6MTA6Ijc4MjI4NzA5NzkiO3M6Mzoicm93IjtzOjEwOiI3ODIyODcwOTY5Ijt9aToxO2E6Mjp7czozOiJjb2wiO3M6MTA6Ijc4MjI4NzA5ODEiO3M6Mzoicm93IjtzOjEwOiI3ODIyODcwOTc0Ijt9aToyO2E6Mjp7czozOiJjb2wiO3M6MTA6Ijc4MjI4NzA5ODIiO3M6Mzoicm93IjtzOjEwOiI3ODIyODcwOTc3Ijt9aTozO2E6Mjp7czozOiJjb2wiO3M6MTA6Ijc4MjI4NzA5ODQiO3M6Mzoicm93IjtzOjEwOiI3ODIyODcwOTc2Ijt9aTo0O2E6Mjp7czozOiJjb2wiO3M6MTA6Ijc4MjI4NzA5ODciO3M6Mzoicm93IjtzOjEwOiI3ODIyODcwOTcxIjt9fXM6MTE6InF1ZXN0aW9uX2lkIjtzOjk6IjY2MzUwMzc1MyI7fWk6MTM7YToyOntzOjc6ImFuc3dlcnMiO2E6MTp7aTowO2E6Mjp7czo0OiJ0ZXh0IjtzOjUzOiJFZHVjYXRlZCB3b3JrZm9yY2UsIHN0cm9uZyBzY2hvb2xzLCBwcm94aW1pdHkgdG8gSW5keSI7czozOiJyb3ciO3M6MToiMCI7fX1zOjExOiJxdWVzdGlvbl9pZCI7czo5OiI2NjM1MDkwNjgiO31pOjE0O2E6Mjp7czo3OiJhbnN3ZXJzIjthOjE6e2k6MDthOjI6e3M6NDoidGV4dCI7czo2MDoiSSB0cnVseSBiZWxpZXZlIHdlIGFyZSBvbiB0aGUgYnJpbmsgb2YgbWFqb3IgcG9zaXRpdmUgY2hhbmdlIjtzOjM6InJvdyI7czoxOiIwIjt9fXM6MTE6InF1ZXN0aW9uX2lkIjtzOjk6IjY2MzUwOTQ1MiI7fWk6MTU7YToyOntzOjc6ImFuc3dlcnMiO2E6MTp7aTowO2E6MTp7czozOiJyb3ciO3M6MTA6Ijc4MjI2MTE1NDUiO319czoxMToicXVlc3Rpb25faWQiO3M6OToiNjc5NTY0ODc1Ijt9aToxNjthOjI6e3M6NzoiYW5zd2VycyI7YToxOntpOjA7YToxOntzOjM6InJvdyI7czoxMDoiNzgyMjc4NDA0NyI7fX1zOjExOiJxdWVzdGlvbl9pZCI7czo5OiI2Nzk1ODA4MjEiO319',
            'production_rank' => 1,
            'wholesale_rank' => 5,
            'retail_rank' => 4,
            'residential_rank' => 3,
            'recreation_rank' => 2,
            'alignment_vs_local' => 62,
            'alignment_vs_parent' => 62,
            'aware_of_plan' => null,
            'response_date' => '2014-09-08 13:57:43',
            'created' => '2015-11-17 17:03:03',
            'modified' => '2016-03-24 18:30:44'
        ];

        foreach ($this->records as $n => &$record) {
            $record += $defaultData;
            $record['id'] = $n + 1;
        }
    }
    // @codingStandardsIgnoreEnd
}
