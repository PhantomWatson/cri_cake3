<?php
namespace App\Reports\FullReports;

use App\Reports\FullReports\Sheets\NotesSheet;
use App\Reports\FullReports\Sheets\OfficialsSurveySheet;
use App\Reports\FullReports\Sheets\OrganizationsSurveySheet;
use App\Reports\Reports;
use App\Reports\Spreadsheet;
use Cake\ORM\TableRegistry;

class AdminReport
{
    private $reportTitle;

    /**
     * Returns a PHPExcel object for the admin report
     *
     * @return \PHPExcel
     */
    public function getSpreadsheet()
    {
        $Report = new Reports();
        $data = $Report->getReport();

        $workbook = new Spreadsheet();
        $workbook
            ->setTitle('CRI Admin Report - ' . date('F j, Y'))
            ->setMetadataTitle()
            ->setAuthor('Center for Business and Economic Research, Ball State University')

            // Remove default starting sheet
            ->removeSheet();

        $sheet = new OfficialsSurveySheet();
        $workbook = $sheet->addSheetToWorkbook($workbook, $data);

        $sheet = new OrganizationsSurveySheet();
        $workbook = $sheet->addSheetToWorkbook($workbook, $data);

        $sheet = new NotesSheet();
        $workbook = $sheet->addSheetToWorkbook($workbook, $data);

        $workbook->selectFirstSheet();
        $phpExcelObj = $workbook->get();

        return $phpExcelObj;
    }







    /**
     * Adds the 'community organizations' sheet to the spreadsheet
     *
     * @param Spreadsheet $spreadsheet Spreadsheet object
     * @param array $data Report data array
     * @return Spreadsheet
     */
    private function organizationsSurveySheet($spreadsheet, $data)
    {
        $surveyType = 'organizations';
        $columnTitles = $this->getSurveyColumnTitles($surveyType);
        $sheetTitle = 'Community ' . ucwords($surveyType) . 's';
        $groupingHeaders = $this->getGroupingHeaderRow($columnTitles);
        $colGroupSpans = $this->getGroupingHeaderColspans($groupingHeaders);
        $spreadsheet
            ->setActiveSheetTitle($sheetTitle)
            ->setColumnTitles($columnTitles)
            ->writeSheetTitle($this->reportTitle)
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
                $spreadsheet->nextRow();
            }
            $row = $this->getDataRow($community, $surveyType);
            $spreadsheet
                ->writeRow($row)
                ->alignHorizontal('left')
                ->alignVertical('top')
                ->applyBorders('right')
                ->applyBorders(['left', 'right'], 0, 2);

            $dataRowIterator++;
        }

        // Wrap up spreadsheet
        $spreadsheet
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
                'No / Unknown' => 7,
                'Notes' => 60
            ]);

        return $spreadsheet;
    }
}