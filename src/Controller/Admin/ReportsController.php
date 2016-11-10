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
            $date = date('M-d-Y');
            $this->respondWithSpreadsheet("CRI Report - OCRA - $date.xlsx");
        }
        $communitiesTable = TableRegistry::get('Communities');
        $this->set([
            'reportSpreadsheet' => $communitiesTable->getReportSpreadsheet('ocra')
        ]);
        $this->render('view');
    }

    /**
     * Method for /admin/reports/admin
     *
     * @return void
     */
    public function admin()
    {
        if (! isset($_GET['debug'])) {
            $date = date('M-d-Y');
            $this->respondWithSpreadsheet("CRI Report - Admin - $date.xlsx");
        }
        $communitiesTable = TableRegistry::get('Communities');
        $this->set([
            'reportSpreadsheet' => $communitiesTable->getReportSpreadsheet('admin')
        ]);
        $this->render('view');
    }

    /**
     * Sets up the response to prompt a download of a spreadsheet
     *
     * @param string $filename
     * @return void
     */
    private function respondWithSpreadsheet($filename)
    {
        $this->response->type(['excel2007' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
        $this->response->type('excel2007');
        $this->response->download($filename);
        $this->viewBuilder()->layout('spreadsheet');
    }
}
