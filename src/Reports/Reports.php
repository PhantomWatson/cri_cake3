<?php
namespace App\Reports;

use App\Model\Entity\Community;
use App\Model\Entity\Survey;
use Cake\Network\Exception\InternalErrorException;
use Cake\ORM\TableRegistry;
use Cake\Utility\Hash;

class Reports
{
    private $afterSurveysColTitles = [];
    private $columnTitles = [];
    private $currentRow = 1;
    private $objPHPExcel;
    private $surveyColumnHeaders = [];
    private $title = '';

    // Column Counts
    private $afterSurveysColCount = 0;
    private $awareColOffset = 0;
    private $beforeSurveysColCount = 0;
    private $intAlignmentColOffset = 0;
    private $officialsColCount = 0;
    private $orgsColCount = 0;
    private $pwrrrAlignmentColOffset = 0;
    private $totalColCount = 0;

    // Column keys
    private $firstColAfterSurveys;
    private $firstOfficialsSurveyCol;
    private $firstOrgSurveyCol;
    private $lastCol;
    private $lastGeneralCol;
    private $lastOfficialsSurveyCol;
    private $lastOrgSurveyCol;

    // Row numbers
    private $firstDataRow;

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
                'parentAreaFips' => $community->parent_area->fips,
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
        $report = $this->getReport();
        $this->objPHPExcel = $this->getPhpExcelObject();
        $this->setDefaultStyles();
        $this->setMetaData($version);
        $this->setColumnHeaders($version);
        $this->setColumnCounts();
        $this->setColumnKeys();

        // Write and style
        $this->writeTitle();
        $this->writeSurveyGroupingHeaders();
        $this->writeColGroupingHeaders($version);
        $this->writeColumnTitles();
        $this->styleColumnTitles();
        $this->firstDataRow = $this->currentRow + 1;
        $this->writeAllDataCells($report, $version);
        $this->styleRecentActivity($report);
        $this->styleDataCells();
        $this->setCellWidth();

        return $this->objPHPExcel;
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
                if ($date->format('Y-m-d') <= date('Y-m-d')) {
                    $presentationsGiven[$letter] = 'Completed';
                } else {
                    $presentationsGiven[$letter] = 'Scheduled';
                }
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
     * @param string $version Version of report (ocra or admin)
     * @return void
     */
    private function setMetaData($version)
    {
        $this->title = ($version == 'ocra') ? 'CRI Report for OCRA - ' : 'CRI Admin Report - ';
        $this->title .= date('F j, Y');
        $author = 'Center for Business and Economic Research, Ball State University';
        $this->objPHPExcel->getProperties()
            ->setCreator($author)
            ->setLastModifiedBy($author)
            ->setTitle($this->title)
            ->setSubject($this->title)
            ->setDescription('');
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
     * Sets $this->columnTitles and $this->surveyColumnHeaders
     *
     * @param string $version Version of report (ocra or admin)
     * @return void
     */
    private function setColumnHeaders($version)
    {
        $this->columnTitles = [
            'Community',
            'Area',
            'Area FIPS'
        ];
        $this->surveyColumnHeaders = [];
        $this->intAlignmentColOffset = 0;
        $surveysTable = TableRegistry::get('Surveys');
        $sectors = $surveysTable->getSectors();

        foreach (['officials', 'organizations'] as $surveyType) {
            $this->surveyColumnHeaders[$surveyType] = [
                'Invitations',
                'Responses',
                'Completion Rate'
            ];

            // Note how many columns come before PWRRR alignment in each survey group
            if (! $this->pwrrrAlignmentColOffset) {
                $this->pwrrrAlignmentColOffset = count($this->surveyColumnHeaders[$surveyType]);
            }

            if ($version == 'ocra') {
                $this->surveyColumnHeaders[$surveyType][] = 'Alignment Calculated';
            } else {
                $this->surveyColumnHeaders[$surveyType][] = 'vs Local Area';
                $this->surveyColumnHeaders[$surveyType][] = 'vs Wider Area';
            }

            // Note how many columns come before internal alignment in each survey group
            if (! $this->intAlignmentColOffset) {
                $this->intAlignmentColOffset = count($this->surveyColumnHeaders[$surveyType]);
            }

            if ($version == 'admin') {
                foreach ($sectors as $sector) {
                    $this->surveyColumnHeaders[$surveyType][] = ucwords($sector);
                }
                $this->surveyColumnHeaders[$surveyType][] = 'Overall';

                // Note how many columns come before "aware of plan" cols
                if (! $this->awareColOffset) {
                    $this->awareColOffset = count($this->surveyColumnHeaders[$surveyType]);
                }

                if ($surveyType == 'officials') {
                    $this->surveyColumnHeaders[$surveyType][] = 'Yes';
                    $this->surveyColumnHeaders[$surveyType][] = 'No / Unknown';
                }
            }

            if ($surveyType == 'officials') {
                $this->surveyColumnHeaders[$surveyType][] = 'Presentation A';
                $this->surveyColumnHeaders[$surveyType][] = 'Presentation B';
            } else {
                $this->surveyColumnHeaders[$surveyType][] = 'Presentation C';
            }

            $this->surveyColumnHeaders[$surveyType][] = 'Status';
        }
        $this->afterSurveysColTitles = ['Notes'];
        $this->columnTitles = array_merge($this->columnTitles, $this->surveyColumnHeaders['officials']);
        $this->columnTitles = array_merge($this->columnTitles, $this->surveyColumnHeaders['organizations']);
        $this->columnTitles = array_merge($this->columnTitles, $this->afterSurveysColTitles);
    }

    /**
     * Writes and styles the title in the top row of this spreadsheet
     *
     * @return void
     */
    private function writeTitle()
    {
        // Write title
        $this->write(0, $this->currentRow, $this->title);

        // Style title
        $this->objPHPExcel->getActiveSheet()->getStyle('A1:A1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 24
            ]
        ]);
        $span = "A{$this->currentRow}:{$this->lastCol}{$this->currentRow}";
        $this->objPHPExcel->getActiveSheet()->mergeCells($span);
    }

    /**
     * Writes grouping headers for each survey to the spreadsheet and styles them
     *
     * @return void
     */
    private function writeSurveyGroupingHeaders()
    {
        // Write survey-type grouping headers
        $this->currentRow++;
        $this->write($this->beforeSurveysColCount, $this->currentRow, 'Community Leadership');
        $this->write(
            $this->beforeSurveysColCount + $this->officialsColCount,
            $this->currentRow,
            'Community Organizations'
        );

        // Style officials-survey grouping header
        $from = $this->firstOfficialsSurveyCol . $this->currentRow;
        $to = $this->lastOfficialsSurveyCol . $this->currentRow;
        $groupingSpan = "$from:$to";
        $this->objPHPExcel
            ->getActiveSheet()
            ->mergeCells($groupingSpan)
            ->getStyle($groupingSpan)->applyFromArray([
                'font' => ['bold' => true],
                'alignment' => $this->align('center'),
                'borders' => [
                    'top' => $this->getBorder(),
                    'left' => $this->getBorder(),
                    'right' => $this->getBorder()
                ]
            ]);

        // Style organizations-survey grouping header
        $groupingSpan = "{$this->firstOrgSurveyCol}{$this->currentRow}:{$this->lastOrgSurveyCol}{$this->currentRow}";
        $this->objPHPExcel
            ->getActiveSheet()
            ->mergeCells($groupingSpan)
            ->getStyle($groupingSpan)->applyFromArray([
                'font' => ['bold' => true],
                'alignment' => $this->align('center'),
                'borders' => [
                    'top' => $this->getBorder(),
                    'left' => $this->getBorder(),
                    'right' => $this->getBorder()
                ]
            ]);
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
     * Writes and styles grouping headers for each survey's internal alignment
     *
     * @param string $version Report version (ocra or admin)
     * @return void
     */
    private function writeColGroupingHeaders($version)
    {
        if ($version != 'admin') {
            return;
        }

        $this->currentRow++;

        // Add right-borders
        $cellsForRightBorder = [
            $this->lastGeneralCol . $this->currentRow,
            $this->lastOfficialsSurveyCol . $this->currentRow,
            $this->lastOrgSurveyCol . $this->currentRow
        ];
        foreach ($cellsForRightBorder as $cell) {
            $this->objPHPExcel->getActiveSheet()
                ->getStyle("$cell:$cell")
                ->applyFromArray([
                    'borders' => ['right' => $this->getBorder()]
                ]);
        }

        // Add bottom-borders (later erased by any column group headers)
        $from = $this->firstOfficialsSurveyCol . $this->currentRow;
        $to = $this->lastCol . $this->currentRow;
        $this->objPHPExcel->getActiveSheet()
            ->getStyle("$from:$to")
            ->applyFromArray([
                'borders' => ['bottom' => $this->getBorder()]
            ]);

        if ($version == 'admin') {
            $this->writePwrrrAlignmentGroupingHeaders();
            $this->writeIntAlignmentGroupingHeaders();
            $this->writeAwareGroupingHeaders();
        }
    }

    /**
     * Writes and styles "internal alignment" grouping headers
     *
     * @return void
     */
    private function writeIntAlignmentGroupingHeaders()
    {
        // Write
        $firstIntAlignmentCol = $this->beforeSurveysColCount + $this->intAlignmentColOffset;
        $this->write(
            $firstIntAlignmentCol,
            $this->currentRow,
            'Internal Alignment'
        );
        $secondIntAlignmentCol = $firstIntAlignmentCol + $this->officialsColCount;
        $this->write(
            $secondIntAlignmentCol,
            $this->currentRow,
            'Internal Alignment'
        );

        // Style
        $surveysTable = TableRegistry::get('Surveys');
        $sectors = $surveysTable->getSectors();
        $intAlignmentColCount = count($sectors) + 1; // Sectors + "Overall"

        $from = $this->getColumnKey($firstIntAlignmentCol) . $this->currentRow;
        $to = $this->getColumnKey($firstIntAlignmentCol + $intAlignmentColCount - 1) . $this->currentRow;
        $this->styleColGroupHeader("$from:$to");

        $from = $this->getColumnKey($secondIntAlignmentCol) . $this->currentRow;
        $to = $this->getColumnKey($secondIntAlignmentCol + $intAlignmentColCount - 1) . $this->currentRow;
        $this->styleColGroupHeader("$from:$to");
    }

    /**
     * Writes and styles "PWRRR alignment" grouping headers
     *
     * @return void
     */
    private function writePwrrrAlignmentGroupingHeaders()
    {
        // Write
        $firstPwrrrAlignmentCol = $this->beforeSurveysColCount + $this->pwrrrAlignmentColOffset;
        $this->write(
            $firstPwrrrAlignmentCol,
            $this->currentRow,
            'PWRRR Alignment'
        );
        $secondPwrrrAlignmentCol = $firstPwrrrAlignmentCol + $this->officialsColCount;
        $this->write(
            $secondPwrrrAlignmentCol,
            $this->currentRow,
            'PWRRR Alignment'
        );

        // Style
        $from = $this->getColumnKey($firstPwrrrAlignmentCol) . $this->currentRow;
        $to = $this->getColumnKey($firstPwrrrAlignmentCol + 1) . $this->currentRow;
        $this->styleColGroupHeader("$from:$to");

        $from = $this->getColumnKey($secondPwrrrAlignmentCol) . $this->currentRow;
        $to = $this->getColumnKey($secondPwrrrAlignmentCol + 1) . $this->currentRow;
        $this->styleColGroupHeader("$from:$to");
    }

    /**
     * Writes and styles "aware of comprehensive plan" grouping header
     *
     * @return void
     */
    private function writeAwareGroupingHeaders()
    {
        // Write
        $awareCol = $this->beforeSurveysColCount + $this->awareColOffset;
        $this->write(
            $awareCol,
            $this->currentRow,
            'Aware of Plan'
        );

        // Style
        $from = $this->getColumnKey($awareCol) . $this->currentRow;
        $to = $this->getColumnKey($awareCol + 1) . $this->currentRow;
        $this->styleColGroupHeader("$from:$to");
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
        $from = 'A' . $this->currentRow;
        $to = $this->lastCol . $this->currentRow;
        $this->objPHPExcel->getActiveSheet()
            ->getStyle("$from:$to")
            ->applyFromArray([
                'alignment' => $this->align('center'),
                'borders' => ['bottom' => $this->getBorder()],
                'font' => ['bold' => true]
            ]);

        $from = 'A' . $this->currentRow;
        $to = $this->lastGeneralCol . $this->currentRow;
        $this->applyBorders($from, $to, ['left', 'top']);

        $from = $this->firstOfficialsSurveyCol . $this->currentRow;
        $to = $this->lastOfficialsSurveyCol . $this->currentRow;
        $this->applyBorders($from, $to, ['left', 'right']);

        $from = $this->firstOrgSurveyCol . $this->currentRow;
        $to = $this->lastOrgSurveyCol . $this->currentRow;
        $this->applyBorders($from, $to, ['left', 'right']);

        $from = $this->firstColAfterSurveys . $this->currentRow;
        $to = $this->lastCol . $this->currentRow;
        $this->applyBorders($from, $to, ['right', 'top']);
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
     * @param string $version Report version (ocra or admin)
     * @return void
     */
    private function writeAllDataCells($report, $version)
    {
        $surveysTable = TableRegistry::get('Surveys');
        $sectors = $surveysTable->getSectors();

        foreach ($report as $community) {
            // Build array of values to write
            $cells = [
                $community['name'],
                $community['parentArea'],
                $community['parentAreaFips']
            ];
            foreach (['official_survey', 'organization_survey'] as $surveyType) {
                $survey = $community[$surveyType];
                $cells[] = $survey['invitations'];
                $cells[] = $survey['responses'];
                $cells[] = $survey['responseRate'];
                if ($version == 'ocra') {
                    $cells[] = ($survey['alignments']['vsLocal'] || $survey['alignments']['vsParent']) ?
                        'Yes' :
                        'No';
                } elseif ($version == 'admin') {
                    $cells[] = $survey['alignments']['vsLocal'];
                    $cells[] = $survey['alignments']['vsParent'];
                    foreach ($sectors as $sector) {
                        $cells[] = $survey['internalAlignment'][$sector];
                    }
                    $cells[] = $survey['internalAlignment']['total'];
                    if ($surveyType == 'official_survey') {
                        $cells[] = $survey['awareOfPlanCount'];
                        $cells[] = $survey['unawareOfPlanCount'];
                    }
                }
                if ($surveyType == 'official_survey') {
                    $cells[] = $community['presentationsGiven']['a'];
                    $cells[] = $community['presentationsGiven']['b'];
                } else {
                    $cells[] = $community['presentationsGiven']['c'];
                }
                $cells[] = $survey['status'];
            }
            $cells[] = $community['notes'];

            // Write values to PHPExcel object
            $this->currentRow++;
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
        }
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
                $to = $this->lastCol . $row;
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
        $from = 'A' . $this->firstDataRow;
        $to = $this->lastOrgSurveyCol . $this->currentRow;
        $this->objPHPExcel->getActiveSheet()
            ->getStyle("$from:$to")
            ->applyFromArray([
                'alignment' => $this->align('left')
            ]);
        $this->applyBorders($from, $to, ['outline']);

        $from = $this->firstColAfterSurveys . $this->firstDataRow;
        $to = $this->lastCol . $this->currentRow;
        $this->objPHPExcel->getActiveSheet()
            ->getStyle("$from:$to")
            ->applyFromArray([
                'alignment' => $this->align('left')
            ]);
        $this->applyBorders($from, $to, ['outline']);

        $from = $this->firstOfficialsSurveyCol . $this->firstDataRow;
        $to = $this->firstOfficialsSurveyCol . $this->currentRow;
        $this->applyBorders($from, $to, ['left']);

        $from = $this->firstOrgSurveyCol . $this->firstDataRow;
        $to = $this->firstOrgSurveyCol . $this->currentRow;
        $this->applyBorders($from, $to, ['left']);
    }

    /**
     * Writes values to the the header cells above columns
     *
     * @return void
     */
    private function writeColumnTitles()
    {
        $this->currentRow++;
        foreach ($this->columnTitles as $col => $colTitle) {
            $this->write($col, $this->currentRow, $colTitle);
        }
    }

    /**
     * Sets various private properties that store the count of certain column types
     *
     * @return void
     */
    private function setColumnCounts()
    {
        $this->officialsColCount = count($this->surveyColumnHeaders['officials']);
        $this->orgsColCount = count($this->surveyColumnHeaders['organizations']);
        $this->afterSurveysColCount = count($this->afterSurveysColTitles);
        $this->beforeSurveysColCount =
            count($this->columnTitles)
            - $this->officialsColCount
            - $this->orgsColCount
            - $this->afterSurveysColCount;
        $this->totalColCount = $this->beforeSurveysColCount + $this->officialsColCount + $this->orgsColCount + $this->afterSurveysColCount;
    }

    /**
     * Sets various private properties that store the keys of certain columns
     *
     * @return void
     */
    private function setColumnKeys()
    {
        $this->lastCol = $this->getColumnKey($this->totalColCount - 1);
        $this->lastGeneralCol = $this->getColumnKey($this->beforeSurveysColCount - 1);
        $this->firstOfficialsSurveyCol = $this->getColumnKey($this->beforeSurveysColCount);
        $this->lastOfficialsSurveyCol = $this->getColumnKey($this->beforeSurveysColCount + $this->officialsColCount - 1);
        $this->firstOrgSurveyCol = $this->getColumnKey($this->beforeSurveysColCount + $this->officialsColCount);
        $this->lastOrgSurveyCol = $this->getColumnKey($this->beforeSurveysColCount + $this->officialsColCount + $this->orgsColCount - 1);
        $this->firstColAfterSurveys = $this->getColumnKey($this->beforeSurveysColCount + $this->officialsColCount + $this->orgsColCount);
    }

    /**
     * Sets the width of spreadsheet cells
     *
     * @return void
     */
    private function setCellWidth()
    {
        // Set the width of all columns (except the last) to fit their content
        for ($n = 0; $n < $this->totalColCount - 1; $n++) {
            $colLetter = $this->getColumnKey($n);
            $this->objPHPExcel->getActiveSheet()->getColumnDimension($colLetter)->setAutoSize(true);
        }

        // Set the width of the last column (notes) to a fixed width
        $colLetter = $this->getColumnKey($this->totalColCount - 1);
        $this->objPHPExcel->getActiveSheet()->getColumnDimension($colLetter)->setWidth(30);
    }
}
