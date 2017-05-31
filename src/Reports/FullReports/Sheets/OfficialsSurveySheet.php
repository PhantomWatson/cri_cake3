<?php
namespace App\Reports\FullReports\Sheets;

use App\Reports\Spreadsheet;
use Cake\ORM\TableRegistry;

class OfficialsSurveySheet
{
    /**
     * Adds an Officials Survey sheet to the provided workbook and returns the workbook
     *
     * @param Spreadsheet $workbook Spreadsheet workbook
     * @param array $data Report data
     * @return mixed
     */
    public function addSheetToWorkbook($workbook, $data)
    {
        $columnTitles = $this->getColumnTitles();
        $sheetTitle = 'Community Officials';
        $groupingHeaders = $this->getGroupingHeaderRow($columnTitles);
        $colGroupSpans = $this->getGroupingHeaderColspans($groupingHeaders);
        $workbook
            ->newSheet($sheetTitle)
            ->setColumnTitles($columnTitles)
            ->writeSheetTitle($workbook->getTitle())
            ->nextRow()
            ->writeSheetSubtitle($sheetTitle)
            ->nextRow()
            ->writeRow($groupingHeaders)
            ->applyBorders('bottom')
            ->styleColGroupHeaders($colGroupSpans)
            ->nextRow()
            ->writeRow($columnTitles)
            ->alignHorizontal('center')
            ->alignVertical('center')
            ->styleRow(['font' => ['bold' => true]])
            ->applyBorders(['bottom', 'left', 'right'])
            ->styleRow([
                'alignment' => ['rotation' => -90]
            ], 2, count($columnTitles) - 2)
            ->nextRow();

        // Write value rows
        $dataRowIterator = 0;
        foreach ($data as $community) {
            if ($dataRowIterator > 0) {
                $workbook->nextRow();
            }
            $row = $this->getDataRow($community);
            $workbook
                ->writeRow($row)
                ->alignHorizontal('left')
                ->alignVertical('top')
                ->applyBorders('right')
                ->applyBorders(['left', 'right'], 0, 1);

            $dataRowIterator++;
        }

        // Wrap up spreadsheet
        $workbook
            ->applyBorders('bottom')
            ->setCellWidth([
                'vs Local Area' => 9,
                'vs Wider Area' => 9,
                'Production' => 4,
                'Wholesale' => 4,
                'Retail' => 4,
                'Residential' => 4,
                'Recreation' => 4,
                'Overall' => 4,
                'Yes' => 7,
                'No / Unknown' => 7
            ]);

        return $workbook;
    }

    /**
     * Returns an array of all column titles used in this sheet
     *
     * @return array
     */
    private function getColumnTitles()
    {
        $surveysTable = TableRegistry::get('Surveys');
        $columnTitles = [
            'Community',
            'Area',
            'Invitations',
            'Responses',
            'Completion Rate',
            'vs Local Area',
            'vs Wider Area',
        ];

        // Industry sectors
        foreach ($surveysTable->getSectors() as $sector) {
            $columnTitles[] = ucwords($sector);
        }

        $columnTitles = array_merge($columnTitles, [
            'Overall',
            'Yes',
            'No / Unknown',
            'Presentation A',
            'Presentation B',
            'Status'
        ]);

        return $columnTitles;
    }

    /**
     * Returns an array containing all the values for the column grouping header row,
     * keyed to the column number at which each value should appear
     *
     * @param array $columnTitles Array of primary column titles
     * @return array
     */
    private function getGroupingHeaderRow($columnTitles)
    {
        $groupingHeaders = [];

        $colNum = $colNum = array_search('vs Local Area', $columnTitles);
        $groupingHeaders[$colNum] = 'PWRRR Alignment';

        $surveysTable = TableRegistry::get('Surveys');
        $sectors = $surveysTable->getSectors();
        $colNum = array_search(ucwords($sectors[0]), $columnTitles);
        $groupingHeaders[$colNum] = 'Internal Alignment';

        $colNum = array_search('Yes', $columnTitles);
        $groupingHeaders[$colNum] = 'Aware of Plan';

        return $groupingHeaders;
    }

    /**
     * Returns an array of [starting column number, ending column number] arrays for all column grouping headers
     *
     * @param array $groupingHeaders Grouping headers array
     * @return array
     */
    private function getGroupingHeaderColspans($groupingHeaders)
    {
        $cellspans = [];

        $colNum = array_search('PWRRR Alignment', $groupingHeaders);
        $cellspans[] = [$colNum, $colNum + 1];

        $colNum = array_search('Internal Alignment', $groupingHeaders);
        $surveysTable = TableRegistry::get('Surveys');
        $sectors = $surveysTable->getSectors();
        $cellspans[] = [$colNum, $colNum + count($sectors)];

        $colNum = array_search('Aware of Plan', $groupingHeaders);
        $cellspans[] = [$colNum, $colNum + 1];

        return $cellspans;
    }

    /**
     * Returns an array representing a row of data to write to the spreadsheet
     *
     * @param array $community Report data for a specific community
     * @return array
     */
    private function getDataRow($community)
    {
        $surveysTable = TableRegistry::get('Surveys');
        $sectors = $surveysTable->getSectors();
        $survey = $community['official_survey'];
        $row = [
            $community['name'],
            $community['parentArea'],
            $survey['invitations'],
            $survey['responses'],
            $survey['responseRate'],
            $survey['alignments']['vsLocal'],
            $survey['alignments']['vsParent'],
        ];
        foreach ($sectors as $sector) {
            $row[] = $survey['internalAlignment'][$sector];
        }
        $row = array_merge($row, [
            $survey['internalAlignment']['total'],
            $survey['awareOfPlanCount'],
            $survey['unawareOfPlanCount'],
            $community['presentationsGiven']['a'],
            $community['presentationsGiven']['b'],
            $survey['status']
        ]);

        return $row;
    }
}