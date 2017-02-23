<?php
namespace App\Reports;

use App\Model\Entity\Community;
use App\Model\Entity\Survey;
use Cake\Network\Exception\InternalErrorException;
use Cake\ORM\TableRegistry;
use Cake\Utility\Hash;

class Reports
{
    private $version;
    private $currentRow = 1;
    private $objPHPExcel;

    // Row numbers
    private $firstDataRow;
    private $lastDataRow;

    const SHEET_TITLE_OFFICIALS = 'Community Officials';
    const SHEET_TITLE_ORGANIZATIONS = 'Community Organizations';
    const SHEET_TITLE_NOTES = 'Notes';

    /**
     * Returns an array used in browser-based and Excel reports
     *
     * @return array
     */
    public function getReport()
    {
        $report = [];

        $communitiesTable = TableRegistry::get('Communities');
        $communities = $communitiesTable->find('forReport');
        $respondents = $this->getRespondents();
        $responsesTable = TableRegistry::get('Responses');

        foreach ($communities as $community) {
            // Collect general information about this community
            $report[$community->id] = [
                'name' => $community->name,
                'parentArea' => $community->parent_area->name,
                'presentationsGiven' => $this->getPresentationStatuses($community),
                'notes' => $community->notes,
                'recentActivity' => $community->activity_records
            ];

            // Collect information about survey responses and alignment
            $surveyTypes = [
                'official_survey' => $community->official_survey,
                'organization_survey' => $community->organization_survey,
            ];
            foreach ($surveyTypes as $surveyKey => $survey) {
                $invitationCount = $this->getInvitationCount($survey, $respondents);
                $approvedResponseCount = $this->getApprovedResponseCount($survey, $respondents);
                $report[$community->id][$surveyKey] = [
                    'invitations' => $invitationCount,
                    'responses' => $approvedResponseCount,
                    'responseRate' => $this->getResponseRate($invitationCount, $approvedResponseCount),
                    'alignments' => [
                        'vsLocal' => $survey['alignment_vs_local'],
                        'vsParent' => $survey['alignment_vs_parent']
                    ],
                    'internalAlignment' => $this->getInternalAlignment($survey),
                    'awareOfPlanCount' => $responsesTable->getApprovedAwareOfPlanCount($survey['id']),
                    'unawareOfPlanCount' => $responsesTable->getApprovedUnawareOfPlanCount($survey['id']),
                    'status' => $this->getStatus($community, $surveyKey)
                ];
            }
        }

        return $report;
    }

    /**
     * Returns a PHPExcel object for either the OCRA or the admin version of the "all communities" report
     *
     * @param string $version 'ocra' or 'admin'
     * @return \PHPExcel
     */
    public function getReportSpreadsheet($version)
    {
        if (! in_array($version, ['ocra', 'admin'])) {
            throw new InternalErrorException('"' . $version . '" is not a valid report type.');
        }

        // Setup
        $this->version = $version;
        $report = $this->getReport();
        $this->objPHPExcel = $this->getPhpExcelObject();

        $this->setDefaultStyles();
        $this->setMetaData();

        // Community Officials
        $this->objPHPExcel->getActiveSheet()->setTitle(Reports::SHEET_TITLE_OFFICIALS);
        $this->writeSurveySheet($report);

        // Community Organizations
        $this->currentRow = 1;
        $this->objPHPExcel->createSheet()->setTitle(Reports::SHEET_TITLE_ORGANIZATIONS);
        $this->objPHPExcel->setActiveSheetIndexByName(Reports::SHEET_TITLE_ORGANIZATIONS);
        $this->writeSurveySheet($report);

        // Notes
        $this->currentRow = 1;
        $this->objPHPExcel->createSheet()->setTitle(Reports::SHEET_TITLE_NOTES);
        $this->objPHPExcel->setActiveSheetIndexByName(Reports::SHEET_TITLE_NOTES);
        $this->writeNotes($report);

        // Select first sheet
        $this->objPHPExcel->setActiveSheetIndexByName(Reports::SHEET_TITLE_OFFICIALS);

        return $this->objPHPExcel;
    }

    /**
     * Does all the writing and styling for the currently-selected survey-related sheet
     *
     * @param array $report Report array
     * @return void
     */
    private function writeSurveySheet($report)
    {
        $this->writeTitle();
        $this->styleTitle();
        $this->currentRow++;
        $this->writeSubtitle();
        $this->styleSubtitle();
        if ($this->version == 'admin') {
            $this->currentRow++;
            $this->writeGroupingHeaders();
            $this->styleGroupingHeaders();
        }
        $this->currentRow++;
        $this->writeColumnTitles();
        $this->styleColumnTitles();
        $this->currentRow++;
        $this->writeDataCells($report);
        $this->styleDataCells();
        $this->styleRecentActivity($report);
        $this->setCellWidth();
    }

    /**
     * Does all the writing and styling for the currently-selected notes sheet
     *
     * @param array $report Report array
     * @return void
     */
    private function writeNotes($report)
    {
        $this->writeTitle();
        $this->styleTitle();
        $this->currentRow++;
        $this->writeSubtitle();
        $this->styleSubtitle();
        $this->currentRow++;
        $this->writeColumnTitles();
        $this->styleColumnTitles();
        $this->currentRow++;

        $this->firstDataRow = $this->currentRow;
        foreach ($report as $community) {
            $cells = [
                $community['name'],
                $community['parentArea'],
                $community['notes']
            ];
            foreach ($cells as $col => $value) {
                $this->write($col, $this->currentRow, $value);
            }
            $this->currentRow++;
        }
        $this->lastDataRow = $this->currentRow - 1;

        $this->styleDataCells();
        $this->styleRecentActivity($report);
        $this->setCellWidth();
    }

    /**
     * Returns an array of column titles for the currently-selected sheet
     *
     * @return array
     */
    private function getColumnTitles()
    {
        $columnTitles = [
            'Community',
            'Area'
        ];
        $surveyType = $this->getCurrentSurveyType();
        if ($surveyType) {
            $columnTitles[] = 'Invitations';
            $columnTitles[] = 'Responses';
            $columnTitles[] = 'Completion Rate';

            if ($this->version == 'ocra') {
                $columnTitles[] = 'Alignment Calculated';
            } elseif ($this->version == 'admin') {
                $columnTitles[] = 'vs Local Area';
                $columnTitles[] = 'vs Wider Area';
                $surveysTable = TableRegistry::get('Surveys');
                foreach ($surveysTable->getSectors() as $sector) {
                    $columnTitles[] = ucwords($sector);
                }
                $columnTitles[] = 'Overall';
                if ($surveyType == 'official') {
                    $columnTitles[] = 'Yes';
                    $columnTitles[] = 'No / Unknown';
                }
            }
            if ($this->getCurrentSurveyType() == 'official') {
                $columnTitles[] = 'Presentation A';
                $columnTitles[] = 'Presentation B';
            } else {
                $columnTitles[] = 'Presentation C';
                $columnTitles[] = 'Presentation D';
            }
            $columnTitles[] = 'Status';
        } else {
            $columnTitles[] = 'Notes';
        }

        return $columnTitles;
    }

    /**
     * Returns an initialized PHPExcel object
     *
     * @return \PHPExcel
     */
    private function getPhpExcelObject()
    {
        require_once ROOT . DS . 'vendor' . DS . 'phpoffice' . DS . 'phpexcel' . DS . 'Classes' . DS . 'PHPExcel.php';
        \PHPExcel_Cell::setValueBinder(new \PHPExcel_Cell_AdvancedValueBinder());
        $objPHPExcel = new \PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0);

        return $objPHPExcel;
    }

    /**
     * Returns the nth Excel-style column key (A, B, C, ... AA, AB, etc.)
     *
     * @param int $num Column number
     * @return string
     */
    private function getColumnKey($num)
    {
        $numeric = $num % 26;
        $letter = chr(65 + $numeric);
        $num2 = intval($num / 26);
        if ($num2 > 0) {
            return $this->getColumnKey($num2 - 1) . $letter;
        } else {
            return $letter;
        }
    }

    /**
     * Returns an array of letter => status for each presentation
     *
     * @param Community $community Community entity
     * @return array
     */
    private function getPresentationStatuses($community)
    {
        $presentationsGiven = [];
        foreach (['a', 'b', 'c', 'd'] as $letter) {
            $date = $community->{'presentation_' . $letter};
            if ($date) {
                $presentationsGiven[$letter] = $date->format('F j, Y');
            } else {
                $presentationsGiven[$letter] = 'Not scheduled';
            }
        }

        return $presentationsGiven;
    }

    /**
     * Returns response rate or 'N/A'
     *
     * @param int $invitationCount Invitation count
     * @param int $approvedResponseCount Approved response count
     * @return string
     */
    private function getResponseRate($invitationCount, $approvedResponseCount)
    {
        if ($invitationCount) {
            return round(($approvedResponseCount / $invitationCount) * 100) . '%';
        } else {
            return 'N/A';
        }
    }

    /**
     * Returns the number of invitations that were sent out for this survey
     *
     * @param Survey $survey Survey entity
     * @param array $respondents Array of $surveyId => $respondentId => $respondent
     * @return int
     */
    private function getInvitationCount($survey, $respondents)
    {
        $invitationCount = 0;
        if ($survey && isset($respondents[$survey->id])) {
            foreach ($respondents[$survey->id] as $respondent) {
                if ($respondent->invited) {
                    $invitationCount++;
                }
            }
        }

        return $invitationCount;
    }

    /**
     * Returns the number of responses for this survey that have been approved
     *
     * @param Survey $survey Survey entity
     * @param array $respondents Array of $surveyId => $respondentId => $respondent
     * @return int
     */
    private function getApprovedResponseCount($survey, $respondents)
    {
        $approvedResponseCount = 0;
        if ($survey && isset($respondents[$survey->id])) {
            foreach ($respondents[$survey->id] as $respondent) {
                if ($respondent->approved && ! empty($respondent->responses)) {
                    $approvedResponseCount++;
                }
            }
        }

        return $approvedResponseCount;
    }

    /**
     * Returns formatted and summed internal alignment scores
     *
     * @param Survey $survey Survey entity
     * @return array
     */
    private function getInternalAlignment($survey)
    {
        $responsesTable = TableRegistry::get('Responses');
        $surveysTable = TableRegistry::get('Surveys');
        $sectors = $surveysTable->getSectors();
        $internalAlignment = [];
        if ($survey) {
            $internalAlignment = $responsesTable->getInternalAlignmentPerSector($survey->id);
            if ($internalAlignment) {
                foreach ($internalAlignment as $sector => &$value) {
                    $value = round($value, 1);
                }
                $internalAlignment['total'] = array_sum($internalAlignment);
            }
        }
        if (! $internalAlignment) {
            $internalAlignment = array_combine($sectors, [null, null, null, null, null]);
            $internalAlignment['total'] = null;
        }

        return $internalAlignment;
    }

    /**
     * Returns a string that sums up the status of the specified community
     * and the specified survey type
     *
     * @param Community $community Community entity
     * @param string $surveyKey Either 'official_survey' or 'organization_survey'
     * @return string
     */
    private function getStatus($community, $surveyKey)
    {
        $correspondingStep = ($surveyKey == 'official_survey') ? 2 : 3;
        if ($community->score < $correspondingStep) {
            return 'Not started yet';
        } elseif ($community->score < ($correspondingStep + 1)) {
            return 'In progress';
        } else {
            return 'Complete';
        }
    }

    /**
     * Returns an array of $surveyId => $respondentId => $respondent
     *
     * @return array
     */
    private function getRespondents()
    {
        $respondentsTable = TableRegistry::get('Respondents');
        $respondents = $respondentsTable->find('all')
            ->select(['id', 'approved', 'invited', 'survey_id'])
            ->contain([
                'Responses' => function ($q) {
                    return $q->select(['id', 'respondent_id']);
                }
            ])
            ->toArray();

        return Hash::combine($respondents, '{n}.id', '{n}', '{n}.survey_id');
    }

    /**
     * Sets metadata for $this->objPHPExcel
     *
     * @return void
     */
    private function setMetaData()
    {
        $title = $this->getTitle();
        $author = 'Center for Business and Economic Research, Ball State University';
        $this->objPHPExcel->getProperties()
            ->setCreator($author)
            ->setLastModifiedBy($author)
            ->setTitle($title)
            ->setSubject($title)
            ->setDescription('');
    }

    /**
     * Returns the title for this entire report
     *
     * @return string
     */
    private function getTitle()
    {
        $title = ($this->version == 'ocra') ? 'CRI Report for OCRA - ' : 'CRI Admin Report - ';
        $title .= date('F j, Y');

        return $title;
    }

    /**
     * Sets the default text styling for the spreadsheet
     *
     * @return void
     */
    private function setDefaultStyles()
    {
        $this->objPHPExcel->getDefaultStyle()->getFont()
            ->setName('Arial')
            ->setSize(11);
    }

    /**
     * Returns 'official' or 'organization' depending on which
     * survey-specific sheet is selected, or false if neither is
     *
     * @return string|bool
     * @throws InternalErrorException
     */
    private function getCurrentSurveyType()
    {
        switch ($this->objPHPExcel->getActiveSheet()->getTitle()) {
            case Reports::SHEET_TITLE_OFFICIALS:
                return 'official';
            case Reports::SHEET_TITLE_ORGANIZATIONS:
                return 'organization';
            default:
                return false;
        }
    }

    /**
     * Writes the title of this spreadsheet
     *
     * @return void
     */
    private function writeTitle()
    {
        $this->write(0, $this->currentRow, $this->getTitle());
    }

    /**
     * Styles the title cell
     *
     * @return void
     */
    private function styleTitle()
    {
        $this->objPHPExcel->getActiveSheet()->getStyle('A1:A1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 24
            ]
        ]);
        $lastCol = $this->getLastColumnLetter();
        $span = "A1:{$lastCol}1";
        $this->objPHPExcel->getActiveSheet()->mergeCells($span);
    }

    /**
     * Writes the subtitle of this sheet
     *
     * @return void
     */
    private function writeSubtitle()
    {
        $subtitle = $this->objPHPExcel->getActiveSheet()->getTitle();
        $this->write(0, $this->currentRow, $subtitle);
    }

    /**
     * Styles the subtitle cell
     *
     * @return void
     */
    private function styleSubtitle()
    {
        $cell = 'A' . $this->currentRow;
        $this->objPHPExcel->getActiveSheet()->getStyle("$cell:$cell")->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 18
            ]
        ]);
        $lastCol = $this->getLastColumnLetter();
        $span = "$cell:$lastCol" . $this->currentRow;
        $this->objPHPExcel->getActiveSheet()->mergeCells($span);
    }

    /**
     * Returns the letter corresponding to the rightmost populated column
     *
     * @return string
     */
    private function getLastColumnLetter()
    {
        $columnCount = count($this->getColumnTitles()) - 1;

        return $this->getColumnKey($columnCount);
    }

    /**
     * Returns the PHPExcel alignment styling array for centering text
     *
     * @param string $alignment Either center, left, or right
     * @return array
     */
    private function align($alignment)
    {
        switch ($alignment) {
            case 'center':
                return ['horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER];
            case 'right':
                return ['horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_RIGHT];
            case 'left':
            default:
                return ['horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_LEFT];
        }
    }

    /**
     * Returns the PHPExcel definition for this spreadsheet's border
     *
     * @return array
     */
    private function getBorder()
    {
        return ['style' => \PHPExcel_Style_Border::BORDER_THIN];
    }

    /**
     * Writes grouping headers
     *
     * @return void
     */
    private function writeGroupingHeaders()
    {
        $colNum = $this->getColNumWithTitle('vs Local Area');
        $this->write(
            $colNum,
            $this->currentRow,
            'PWRRR Alignment'
        );

        $surveysTable = TableRegistry::get('Surveys');
        $sectors = $surveysTable->getSectors();
        $colNum = $this->getColNumWithTitle(ucwords($sectors[0]));
        $this->write(
            $colNum,
            $this->currentRow,
            'Internal Alignment'
        );

        if ($this->getCurrentSurveyType() == 'official') {
            $colNum = $this->getColNumWithTitle('Yes');
            $this->write(
                $colNum,
                $this->currentRow,
                'Aware of Plan'
            );
        }
    }

    /**
     * Returns a column number corresponding to a column with the specified title
     *
     * @param string $title Title
     * @return int
     * @throws InternalErrorException
     */
    private function getColNumWithTitle($title)
    {
        $colNum = array_search($title, $this->getColumnTitles());
        if ($colNum === false) {
            throw new InternalErrorException("Column title '$title' not found");
        }

        return $colNum;
    }

    /**
     * Returns a column key (letter) corresponding to a column with the specified title
     *
     * @param string $title Title
     * @return string
     */
    private function getColKeyWithTitle($title)
    {
        $colNum = $this->getColNumWithTitle($title);

        return $this->getColumnKey($colNum);
    }

    /**
     * Apply styleColGroupHeader() to all column grouping headers
     *
     * @return void
     */
    private function styleGroupingHeaders()
    {
        // PWRRR alignment
        $colNum = $this->getColNumWithTitle('vs Local Area');
        $from = $this->getColumnKey($colNum) . $this->currentRow;
        $to = $this->getColumnKey($colNum + 1) . $this->currentRow;
        $this->styleColGroupHeader("$from:$to");

        // Internal alignment
        $surveysTable = TableRegistry::get('Surveys');
        $sectors = $surveysTable->getSectors();
        $colNum = $this->getColNumWithTitle(ucwords($sectors[0]));
        $from = $this->getColumnKey($colNum) . $this->currentRow;
        $to = $this->getColumnKey($colNum + count($sectors)) . $this->currentRow;
        $this->styleColGroupHeader("$from:$to");

        // Aware of plan
        if ($this->getCurrentSurveyType() == 'official') {
            $colNum = $this->getColNumWithTitle('Yes');
            $from = $this->getColumnKey($colNum) . $this->currentRow;
            $to = $this->getColumnKey($colNum + 1) . $this->currentRow;
            $this->styleColGroupHeader("$from:$to");
        }
    }

    /**
     * Styles column group headers
     *
     * @param string $span e.g. "B2:D2"
     * @return void
     */
    private function styleColGroupHeader($span)
    {
        $this->objPHPExcel->getActiveSheet()
            ->mergeCells($span)
            ->getStyle($span)
            ->applyFromArray([
                'alignment' => $this->align('center'),
                'borders' => [
                    'top' => $this->getBorder(),
                    'left' => $this->getBorder(),
                    'right' => $this->getBorder(),
                    'bottom' => ['style' => \PHPExcel_Style_Border::BORDER_NONE]
                ],
                'font' => ['bold' => true]
            ]);
    }

    /**
     * Writes a value to the spreadsheet
     *
     * @param int $colNum Column number
     * @param int $rowNum Row number
     * @param string $value Value
     * @return void
     */
    private function write($colNum, $rowNum, $value)
    {
        $this->objPHPExcel
            ->getActiveSheet()
            ->setCellValueByColumnAndRow($colNum, $rowNum, $value);
    }

    /**
     * Applies styling to the spreadsheet's column titles
     *
     * @return void
     */
    private function styleColumnTitles()
    {
        // Styles for entire row
        $from = 'A' . $this->currentRow;
        $to = $this->getLastColumnLetter() . $this->currentRow;
        $this->objPHPExcel->getActiveSheet()
            ->getStyle("$from:$to")
            ->applyFromArray([
                'alignment' => [
                    'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                ],
                'borders' => [
                    'outline' => $this->getBorder()
                ],
                'font' => ['bold' => true]
            ]);

        // Community information columns
        $from = 'A' . $this->currentRow;
        $to = $this->getColKeyWithTitle('Area') . $this->currentRow;
        $this->applyBorders($from, $to, ['left', 'top']);

        // No more styling for notes sheet
        if ($this->objPHPExcel->getActiveSheet()->getTitle() == Reports::SHEET_TITLE_NOTES) {
            return;
        }

        // Survey information columns
        $from = $this->getColKeyWithTitle('Invitations') . $this->currentRow;
        $to = $this->getColKeyWithTitle('Status') . $this->currentRow;
        $this->applyBorders($from, $to, ['left', 'right']);

        // Rotate titles
        $from = $this->getColKeyWithTitle('Invitations') . $this->currentRow;
        $to = $this->getColumnKey($this->getColNumWithTitle('Status') - 1) . $this->currentRow;
        $this->objPHPExcel->getActiveSheet()
            ->getStyle("$from:$to")
            ->applyFromArray([
                'alignment' => ['rotation' => -90]
            ]);

        // Remove top border from titles under column group headers
        if ($this->version == 'admin') {
            $from = $this->getColKeyWithTitle('vs Local Area') . $this->currentRow;
            if ($this->getCurrentSurveyType() == 'official') {
                $colNum = $this->getColNumWithTitle('Yes') + 1;
            } else {
                $colNum = $this->getColNumWithTitle('Overall');
            }
            $to = $this->getColumnKey($colNum) . $this->currentRow;
            $this->objPHPExcel->getActiveSheet()
                ->getStyle("$from:$to")
                ->applyFromArray([
                    'borders' => [
                        'top' => ['style' => \PHPExcel_Style_Border::BORDER_NONE]
                    ]
                ]);
        }
    }

    /**
     * Adds borders in a span of cells along the listed edges
     *
     * @param string $from Column letter + row number
     * @param string $to Column letter + row number
     * @param array $edges Array of left, right, top, bottom, outline strings
     * @return void
     */
    private function applyBorders($from, $to, $edges)
    {
        $borders = array_fill_keys($edges, $this->getBorder());
        $this->objPHPExcel->getActiveSheet()
            ->getStyle("$from:$to")
            ->applyFromArray([
                'borders' => $borders
            ]);
    }

    /**
     * Writes values to all of the cells in the body of the spreadsheet
     *
     * @param array $report Report
     * @return void
     */
    private function writeDataCells($report)
    {
        $this->firstDataRow = $this->currentRow;
        $surveysTable = TableRegistry::get('Surveys');
        $sectors = $surveysTable->getSectors();

        foreach ($report as $community) {
            // Build array of values to write
            $cells = [
                $community['name'],
                $community['parentArea'],
            ];
            $surveyType = $this->getCurrentSurveyType();
            $survey = $community["{$surveyType}_survey"];
            $cells[] = $survey['invitations'];
            $cells[] = $survey['responses'];
            $cells[] = $survey['responseRate'];
            if ($this->version == 'ocra') {
                $cells[] = ($survey['alignments']['vsLocal'] || $survey['alignments']['vsParent']) ?
                    'Yes' :
                    'No';
            } elseif ($this->version == 'admin') {
                $cells[] = $survey['alignments']['vsLocal'];
                $cells[] = $survey['alignments']['vsParent'];
                foreach ($sectors as $sector) {
                    $cells[] = $survey['internalAlignment'][$sector];
                }
                $cells[] = $survey['internalAlignment']['total'];
                if ($surveyType == 'official') {
                    $cells[] = $survey['awareOfPlanCount'];
                    $cells[] = $survey['unawareOfPlanCount'];
                }
            }
            if ($surveyType == 'official') {
                $cells[] = $community['presentationsGiven']['a'];
                $cells[] = $community['presentationsGiven']['b'];
            } else {
                $cells[] = $community['presentationsGiven']['c'];
                $cells[] = $community['presentationsGiven']['d'];
            }
            $cells[] = $survey['status'];

            // Write values
            foreach ($cells as $col => $value) {
                // Non-percentage values
                if (strpos($value, '%') === false) {
                    $this->write($col, $this->currentRow, $value);

                // Percentage values
                } else {
                    $cell = $this->getColumnKey($col) . $this->currentRow;
                    $this->objPHPExcel->getActiveSheet()->getCell($cell)->setValueExplicit(
                        $value,
                        \PHPExcel_Cell_DataType::TYPE_STRING
                    );
                }
            }

            $this->currentRow++;
        }

        $this->lastDataRow = $this->currentRow - 1;
    }

    /**
     * Styles rows for communities with recent activity
     *
     * @param array $report Report
     * @return void
     */
    private function styleRecentActivity($report)
    {
        $row = $this->firstDataRow;
        foreach ($report as $community) {
            if ($community['recentActivity']) {
                $from = 'A' . $row;
                $to = $this->getLastColumnLetter() . $row;
                $this->objPHPExcel->getActiveSheet()
                    ->getStyle("$from:$to")
                    ->applyFromArray([
                        'font' => ['bold' => true]
                    ]);
            }
            $row++;
        }
    }

    /**
     * Applies styling to all data cells in the body of the spreadsheet
     *
     * @return void
     */
    private function styleDataCells()
    {
        // Style all data cells
        $from = 'A' . $this->firstDataRow;
        $to = $this->getLastColumnLetter() . $this->lastDataRow;
        $this->objPHPExcel->getActiveSheet()
            ->getStyle("$from:$to")
            ->applyFromArray([
                'alignment' => [
                    'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                    'vertical' => \PHPExcel_Style_Alignment::VERTICAL_TOP
                ]
            ]);
        $this->applyBorders($from, $to, ['outline']);

        // Add border separating community info and survey info
        $colNum = $this->getColNumWithTitle('Area') + 1;
        $colKey = $this->getColumnKey($colNum);
        $from = $colKey . $this->firstDataRow;
        $to = $colKey . $this->lastDataRow;
        $this->applyBorders($from, $to, ['left']);
    }

    /**
     * Writes values to the the header cells above columns
     *
     * @return void
     */
    private function writeColumnTitles()
    {
        foreach ($this->getColumnTitles() as $col => $colTitle) {
            $this->write($col, $this->currentRow, $colTitle);
        }
    }

    /**
     * Sets the width of spreadsheet cells
     *
     * @return void
     */
    private function setCellWidth()
    {
        // Set the width of certain cells (the rest will be automatically sized to fit their contents)
        $widthsByColTitle = [
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
        ];
        $widthsByColNum = [];
        $colTitles = $this->getColumnTitles();
        foreach ($widthsByColTitle as $title => $width) {
            $colNum = array_search($title, $colTitles);
            if ($colNum) {
                $widthsByColNum[$colNum] = $width;
            }
        }
        for ($n = 0; true; $n++) {
            $colLetter = $this->getColumnKey($n);
            if (isset($widthsByColNum[$n])) {
                $this->objPHPExcel
                    ->getActiveSheet()
                    ->getColumnDimension($colLetter)
                    ->setWidth($widthsByColNum[$n]);
            } else {
                $this->objPHPExcel
                    ->getActiveSheet()
                    ->getColumnDimension($colLetter)
                    ->setAutoSize(true);
            }
            if ($colLetter == $this->getLastColumnLetter()) {
                break;
            }
        }
    }
}
