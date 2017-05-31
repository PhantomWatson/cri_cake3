<?php
namespace App\Reports\FullReports\Sheets;

use App\Reports\Spreadsheet;

class NotesSheet
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
        $sheetTitle = 'Notes';
        $workbook
            ->newSheet($sheetTitle)
            ->setColumnTitles($columnTitles)
            ->writeSheetTitle($workbook->getTitle())
            ->nextRow()
            ->writeSheetSubtitle($sheetTitle)
            ->nextRow()
            ->writeRow($columnTitles)
            ->alignHorizontal('center')
            ->alignVertical('center')
            ->styleRow(['font' => ['bold' => true]])
            ->applyBorders(['outline'])
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
                ->applyBorders(['left', 'right'], 0, 1)
                ->setWrapText(2, 2);

            $dataRowIterator++;
        }

        // Wrap up spreadsheet
        $workbook
            ->applyBorders('bottom')
            ->setCellWidth([
                'Notes' => 60
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
        return [
            'Community',
            'Area',
            'Notes'
        ];
    }

    /**
     * Returns an array representing a row of data to write to the spreadsheet
     *
     * @param array $community Report data for a specific community
     * @return array
     */
    private function getDataRow($community)
    {
        return [
            $community['name'],
            $community['parentArea'],
            $community['notes']
        ];
    }
}