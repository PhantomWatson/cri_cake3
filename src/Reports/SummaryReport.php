<?php
declare(strict_types=1);

namespace App\Reports;

use Cake\ORM\TableRegistry;
use Cake\View\Helper\TimeHelper;
use Cake\View\View;

class SummaryReport
{
    /**
     * Returns a PHPExcel object for the OCRA summary report
     *
     * @return \PHPExcel
     */
    public function getSpreadsheet()
    {
        $title = 'CRI Summary Report';
        $columnTitles = [
            'Community',
            'Status',
            'Last Activity',
        ];
        $spreadsheet = new Spreadsheet();
        $spreadsheet
            ->setMetadataTitle($title)
            ->setAuthor('Center for Business and Economic Research, Ball State University')
            ->setColumnTitles($columnTitles)
            ->setActiveSheetTitle($title)
            ->writeSheetTitle($title)
            ->nextRow()
            ->writeRow($columnTitles)
            ->styleRow([
                'alignment' => [
                    'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER,
                ],
                'borders' => [
                    'outline' => ['style' => \PHPExcel_Style_Border::BORDER_THIN],
                ],
                'font' => ['bold' => true],
            ])
            ->nextRow();

        $communitiesTable = TableRegistry::getTableLocator()->get('Communities');
        $communities = $communitiesTable->find()
            ->order(['name' => 'ASC'])
            ->all();

        $activityRecordsTable = TableRegistry::getTableLocator()->get('ActivityRecords');
        $View = new View();
        $Time = new TimeHelper($View);
        foreach ($communities as $community) {
            $recentActivity = $activityRecordsTable->getMostRecentForCommunity($community->id);
            if ($recentActivity) {
                $recentActivity = $Time->format(
                    $recentActivity->created,
                    'MMMM d, Y',
                    false,
                    'America/New_York'
                );
            }

            $spreadsheet
                ->writeRow([
                    $community->name,
                    $communitiesTable->getStatusDescription($community),
                    $recentActivity,
                ])
                ->styleRow([
                    'borders' => [
                        'right' => ['style' => \PHPExcel_Style_Border::BORDER_THIN],
                    ],
                    'font' => ['bold' => true],
                ], 0, 0)
                ->nextRow();
        }

        $spreadsheet = $spreadsheet->setCellWidth();

        $phpExcelObj = $spreadsheet->get();

        return $phpExcelObj;
    }
}
