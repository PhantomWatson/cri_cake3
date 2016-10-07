<?php
namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * StatCategoriesFixture
 *
 */
class StatCategoriesFixture extends TestFixture
{

    /**
     * Fields
     *
     * @var array
     */
    // @codingStandardsIgnoreStart
    public $fields = [
        'id' => ['type' => 'integer', 'length' => 11, 'unsigned' => false, 'null' => false, 'default' => null, 'comment' => '', 'autoIncrement' => true, 'precision' => null],
        'name' => ['type' => 'string', 'length' => 200, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null, 'fixed' => null],
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
    public $records = [];

    public function init()
    {
        //  id, name, created, modified
        $dbDump = '
            1,Production Demand Pull Factor (exportable demand per capita),2013-09-24 19:33:12,2013-09-24 19:33:12
            2,Production Supply Pull Factor (exportable supply per capita),2013-09-24 19:39:29,2013-09-24 19:39:29
            3,2011 Wholesale Personal Income per Capita Pull Factor,2013-09-24 19:39:37,2013-09-24 19:39:37
            4,Total Establishments: Wholesale Trade per Capita Pull Factor,2013-09-24 19:39:45,2013-09-24 19:39:45
            5,2012 Truck Transportation Personal Income per Capita Pull Factor,2013-09-24 19:39:53,2013-09-24 19:39:53
            6,Retail Supply Pull Factor (non-exportable supply per capita),2013-09-24 19:40:01,2013-09-24 19:40:01
            7,Retail Demand Pull Factor (non-exportable demand per capita),2013-09-24 19:40:10,2013-09-24 19:40:10
            8,Total Establishments: Retail Trade per Capita Pull Factor,2013-09-24 19:40:21,2013-09-24 19:40:21
            9,Housing Density (units per square mile) Pull Factor,2013-09-24 19:40:27,2013-09-24 19:40:27
            10,Metro Dummy Pull factor,2013-09-24 19:40:34,2013-09-24 19:40:34
            11,2001-2011 Population Growth Pull Factor,2013-09-24 19:40:43,2013-09-24 19:40:43
            12,Median House Value Pull Factor,2013-09-24 19:40:50,2013-09-24 19:40:50
            13,Index of Changeable Amenities Pull Factor,2013-09-24 19:40:56,2013-09-24 19:40:56
            14,Index of Relatively Static Amenities Pull Factor,2013-09-24 19:41:04,2013-09-24 19:41:04
            15,2011 Percentage of 25 Yr & Older Population that have a Bachelor Degree or Higher Pull Factor,2013-09-24 19:41:11,2013-09-24 19:41:11
            16,Total Establishments: Arts Entertainment Recreation per Capita Pull Factor,2013-09-24 19:41:18,2013-09-24 19:41:18
            17,2011 Percentage of Total Population under 30 years Pull Factor,2013-09-24 19:41:24,2013-09-24 19:41:24
            18,Exportable Sector Employment,2013-10-07 14:05:24,2013-10-07 14:05:24
            19,Non-Exportable Sector Employment,2013-10-07 14:05:24,2013-10-07 14:05:24
        ';
        $lines = explode("\n", $dbDump);
        foreach ($lines as $lineNum => $line) {
            $line = trim($line);
            if ($line == '') {
                continue;
            }
            $fields = explode(',', $line);
            $this->records[] = [
                'id' => $fields[0],
                'name' => $fields[1],
                'created' => $fields[2],
                'modified' => $fields[3]
            ];
        }
        parent::init();
    }
}
