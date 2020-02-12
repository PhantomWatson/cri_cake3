<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AppController;
use App\Reports\FullReports\AdminReport;
use App\Reports\FullReports\OcraReport;
use App\Reports\Reports;
use App\Reports\SummaryReport;
use Cake\ORM\TableRegistry;

class ReportsController extends AppController
{
    /**
     * Method for /admin/reports/index
     *
     * @return void
     */
    public function index()
    {
        /** @var \App\Model\Table\SurveysTable $surveysTable */
        $surveysTable = TableRegistry::get('Surveys');
        $reports = new Reports();
        $report = $reports->getReport();
        $notes = [];
        foreach ($report as $communityId => $community) {
            if ($community['notes']) {
                $notes[$communityId] = nl2br($community['notes']);
            }
        }
        $this->set([
            'notes' => $notes,
            'report' => $report,
            'sectors' => $surveysTable->getSectors(),
            'titleForLayout' => 'CRI Reports',
        ]);
        $this->viewBuilder()->setHelpers(['ActivityRecords', 'Reports']);
    }

    /**
     * Method for /admin/reports/ocra-full
     *
     * @return void
     */
    public function ocraFull()
    {
        $filename = 'CRI Full Report - OCRA - ' . date('M-d-Y') . '.xlsx';
        $this->respondWithSpreadsheet($filename);
        $ocraReport = new OcraReport();
        $this->set('reportSpreadsheet', $ocraReport->getSpreadsheet());
        $this->viewBuilder()->setLayout('spreadsheet');
        $this->render('view');
    }

    /**
     * Method for /admin/reports/ocra-summary
     *
     * @return void
     */
    public function ocraSummary()
    {
        $filename = 'CRI Summary Report - OCRA - ' . date('M-d-Y') . '.xlsx';
        $this->respondWithSpreadsheet($filename);
        $summaryReport = new SummaryReport();
        $this->set('reportSpreadsheet', $summaryReport->getSpreadsheet());
        $this->viewBuilder()->setLayout('spreadsheet');
        $this->render('view');
    }

    /**
     * Method for /admin/reports/admin
     *
     * @return void
     */
    public function admin()
    {
        $filename = 'CRI Report - Admin - ' . date('M-d-Y') . '.xlsx';
        $this->respondWithSpreadsheet($filename);
        $adminReport = new AdminReport();
        $this->set('reportSpreadsheet', $adminReport->getSpreadsheet());
        $this->viewBuilder()->setLayout('spreadsheet');
        $this->render('view');
    }

    /**
     * Sets up the response to prompt a download of a spreadsheet
     *
     * @param string $filename Filename
     * @return void
     */
    private function respondWithSpreadsheet($filename)
    {
        $response = $this->response;
        $response = $response->withType('xlsx');
        $response = $response->withDownload($filename);
        $this->response = $response;
    }
}
