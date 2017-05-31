<?php
namespace App\Reports\FullReports\Sheets;

use App\Reports\Spreadsheet;
use App\View\Helper\ActivityRecordsHelper;
use Cake\View\Helper\TimeHelper;

class RecentActivitySheet
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
        $sheetTitle = 'Recent Activity';
        $workbook
            ->newSheet($sheetTitle)
            ->setColumnTitles($columnTitles)
            ->writeSheetTitle($workbook->getTitle())
            ->nextRow()
            ->writeSheetSubtitle($sheetTitle . ' (last 30 days)')
            ->nextRow()
            ->writeRow($columnTitles)
            ->alignHorizontal('center')
            ->alignVertical('center')
            ->styleRow(['font' => ['bold' => true]])
            ->applyBorders(['outline'])
            ->nextRow();

        // Write value rows
        $dataRowIterator = 0;
        $recentActivity = $this->getRecentActivity($data);
        foreach ($recentActivity as $time => $rows) {
            foreach ($rows as $row) {
                if ($dataRowIterator > 0) {
                    $workbook->nextRow();
                }
                $workbook
                    ->writeRow(array_values($row))
                    ->alignHorizontal('left')
                    ->alignVertical('top')
                    ->applyBorders('right')
                    ->applyBorders(['left', 'right'], 0, 1);

                $dataRowIterator++;
            }
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
        return [
            'Community',
            'Area',
            'Activity',
            'Date'
        ];
    }

    /**
     * Arranges all communities' recent activity into a single array indexed by date
     *
     * @param array $data Report data
     * @return array
     */
    private function getRecentActivity($data)
    {
        $recentActivity = [];
        $View = new \Cake\View\View();
        $ActivityRecordsHelper = new ActivityRecordsHelper($View);
        $Time = new TimeHelper($View);
        foreach ($data as $community) {
            foreach ($community['recentActivity'] as $activityRecord) {
                $timeKey = $Time->format($activityRecord->created, 'yyyy-MM-dd HH:mm:ss', false, 'America/New_York');
                $recentActivity[$timeKey][] = [
                    'community' => $community['name'],
                    'area' => $community['parentArea'],
                    'event' => $ActivityRecordsHelper->event($activityRecord),
                    'time' => $Time->format($activityRecord->created, 'MMM d Y, h:mma', false, 'America/New_York')
                ];
            }
        }
        krsort($recentActivity);

        return $recentActivity;
    }
}