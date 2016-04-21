<?php
namespace App\Controller\Client;

use App\Controller\AppController;
use App\Mailer\Mailer;
use Cake\Network\Exception\BadRequestException;
use Cake\Network\Exception\NotFoundException;
use Cake\ORM\TableRegistry;

/**
 * Surveys Controller
 *
 * @property \App\Model\Table\SurveysTable $Surveys
 */
class SurveysController extends AppController
{

    public function initialize()
    {
        parent::initialize();
        $this->loadComponent('SurveyProcessing');
        $this->loadComponent('RequestHandler');
    }

    public function invite($respondentTypePlural = null)
    {
        // Find and validate community
        $clientId = $this->getClientId();
        if (! $clientId) {
            return $this->chooseClientToImpersonate();
        }
        $communitiesTable = TableRegistry::get('Communities');
        $communityId = $communitiesTable->getClientCommunityId($clientId);
        if (! $communityId || ! $communitiesTable->exists(['id' => $communityId])) {
            throw new NotFoundException('Sorry, we couldn\'t find the community corresponding with your account (#'.$clientId.')');
        }

        $this->Surveys->validateRespondentTypePlural($respondentTypePlural, $communityId);
        $respondentType = str_replace('s', '', $respondentTypePlural);
        $surveyId = $this->Surveys->getSurveyId($communityId, $respondentType);

        if ($this->request->is('post')) {
            $this->SurveyProcessing->processInvitations($communityId, $respondentType, $surveyId);
        }

        $respondentsTable = TableRegistry::get('Respondents');
        $approvedRespondents = $respondentsTable->getApprovedList($surveyId);
        $unaddressedUnapprovedRespondents = $respondentsTable->getUnaddressedUnapprovedList($surveyId);
        $allRespondents = array_merge($approvedRespondents, $unaddressedUnapprovedRespondents);

        $survey = $this->Surveys->get($surveyId);
        $this->set([
            'surveyType' => $survey->type,
            'titleForLayout' => 'Invite Community '.ucwords($respondentTypePlural),
        ]);
        $this->set(compact(
            'allRespondents',
            'approvedRespondents',
            'communityId',
            'respondentTypePlural',
            'surveyId',
            'titleForLayout',
            'unaddressedUnapprovedRespondents'
        ));
    }

    public function remind($surveyType)
    {
        $clientId = $this->getClientId();
        if (! $clientId) {
            return $this->chooseClientToImpersonate();
        }

        $communitiesTable = TableRegistry::get('Communities');
        $communityId = $communitiesTable->getClientCommunityId($clientId);
        if (! $communityId) {
            throw new NotFoundException('Your account is not currently assigned to a community');
        }

        $surveysTable = TableRegistry::get('Surveys');
        $surveyId = $surveysTable->getSurveyId($communityId, $surveyType);
        $survey = $surveysTable->get($surveyId);

        if ($this->request->is('post')) {
            $Mailer = new Mailer();
            $sender = $this->Auth->user();
            if ($Mailer->sendReminders($surveyId, $sender)) {
                $this->Flash->success('Reminder email successfully sent');
                return $this->redirect([
                    'prefix' => 'client',
                    'controller' => 'Communities',
                    'action' => 'index'
                ]);
            }

            $msg = 'There was an error sending reminder emails.';
            $adminEmail = Configure::read('admin_email');
            $msg .= ' Email <a href="mailto:'.$adminEmail.'">'.$adminEmail.'</a> for assistance.';
            $this->Flash->error($msg);

            // Redirect so that hitting refresh won't re-send POST request
            return $this->redirect([
                'prefix' => 'client',
                'controller' => 'Surveys',
                'action' => 'remind',
                $survey->type
            ]);
        }

        $respondentsTable = TableRegistry::get('Respondents');
        $unresponsive = $respondentsTable->getUnresponsive($surveyId);
        $this->set([
            'community' => $communitiesTable->get($communityId),
            'survey' => $survey,
            'titleForLayout' => 'Send Reminders to Community '.ucwords($survey->type).'s',
            'unresponsive' => $unresponsive,
            'unresponsiveCount' => count($unresponsive),
        ]);
    }

    public function uploadInvitationSpreadsheet($surveyId)
    {
        if (empty($_FILES['files'])) {
            throw new BadRequestException('No file was uploaded.');
        }

        // Validate extension
        $filename = $_FILES['files']['name'][0];
        $filenameParts = explode('.', $filename);
        $extension = array_pop($filenameParts);
        if (strtolower($extension) != 'xlsx') {
            throw new BadRequestException('Invalid file type: '.$extension);
        }

        require_once(ROOT.DS.'vendor'.DS.'phpoffice'.DS.'phpexcel'.DS.'Classes'.DS.'PHPExcel.php');
        $filepath = $_FILES['files']['tmp_name'][0];
        $excelReader = \PHPExcel_IOFactory::createReader('Excel2007');
        $excelReader->setReadDataOnly(true);
        $excelObj = $excelReader->load($filepath);
        $sheetData = $excelObj->getActiveSheet()->toArray(null, false, false, false);

        // Collect column information
        $colNumbers = [];
        $headerRow = null;
        $expectedCols = [
            'First Name',
            'Last Name',
            'Entity',
            'Title',
            'Email'
        ];
        foreach ($sheetData as $rowNum => $cols) {
            if (! in_array('Email', $cols)) {
                continue;
            }
            $headerRow = $rowNum;
            foreach ($expectedCols as $label) {
                $colNumber = array_search($label, $cols);
                if ($colNumber === false) {
                    throw new BadRequestException('Spreadsheet cannot be read: Header row improperly formatted');
                }
                $colNumbers[$label] = $colNumber;
            }
            break;
        }
        if (empty($colNumbers)) {
            throw new BadRequestException('Spreadsheet cannot be read: Header row not found');
        }

        // Collect data
        $rowNum = $headerRow + 1;
        $data = [];
        while (! empty($sheetData[$rowNum][$colNumbers['Email']])) {
            $row = $sheetData[$rowNum];
            $email = trim($row[$colNumbers['Email']]);
            $fName = trim($row[$colNumbers['First Name']]);
            $lName = trim($row[$colNumbers['Last Name']]);
            $title = trim($row[$colNumbers['Title']]);
            $organization = trim($row[$colNumbers['Entity']]);
            if ($title && $organization) {
                $fullTitle = "$title, $organization";
            } else {
                $fullTitle = $title.$organization;
            }
            $data[] = [
                'name' => trim("$fName $lName"),
                'email' => $email,
                'title' => $fullTitle
            ];
            $rowNum++;
        }

        if (empty($data)) {
            $message = 'No invitation information found in spreadsheet';
        } else {
            $message = 'Invitation information imported from spreadsheet';
        }

        $this->viewBuilder()->layout('json');
        $this->set([
            '_serialize' => [
                'code',
                'message',
                'data'
            ],
            'code' => 200,
            'message' => $message,
            'data' => $data
        ]);
    }
}
