<?php
namespace App\Controller\Admin;

use App\Controller\AppController;
use App\Reports\Reports;
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
            'titleForLayout' => 'CRI Reports'
        ]);
        $this->viewBuilder()->setHelpers(['ActivityRecords', 'Reports']);
    }

    /**
     * Method for /admin/reports/ocra
     *
     * @return void
     */
    public function ocra()
    {
        $reports = new Reports();
        $filename = 'CRI Report - OCRA - ' . date('M-d-Y') . '.xlsx';
        $this->respondWithSpreadsheet($filename);
        $this->set('reportSpreadsheet', $reports->getReportSpreadsheet('ocra'));
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
        $reports = new Reports();
        $filename = 'CRI Report - Admin - ' . date('M-d-Y') . '.xlsx';
        $this->respondWithSpreadsheet($filename);
        $this->set('reportSpreadsheet', $reports->getReportSpreadsheet('admin'));
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
