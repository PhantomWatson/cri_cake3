<?php
namespace App\Controller\Admin;

use App\Controller\AppController;
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
        $sectors = $surveysTable->getSectors();
        $communitiesTable = TableRegistry::get('Communities');
        $report = $communitiesTable->getReport();

        $this->set([
            'report' => $report,
            'sectors' => $sectors,
            'titleForLayout' => 'CRI Admin Report: All Communities'
        ]);
    }


    /**
     * Method for /admin/reports/ocra
     *
     * @return void
     */
    public function ocra()
    {
        if (! isset($_GET['debug'])) {
            $this->response->type(['excel2007' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
            $this->response->type('excel2007');
            $date = date('M-d-Y');
            $this->response->download("CRI Report - OCRA - $date.xlsx");
            $this->viewBuilder()->layout('spreadsheet');
        }
        $communitiesTable = TableRegistry::get('Communities');
        $this->set([
            'ocraReportSpreadsheet' => $communitiesTable->getOcraReportSpreadsheet()
        ]);
    }
}
