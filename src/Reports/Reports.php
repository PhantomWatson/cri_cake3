<?php
namespace App\Reports;


use Cake\Network\Exception\InternalErrorException;
use Cake\ORM\TableRegistry;
use Cake\Utility\Hash;

class Reports
{
    public function getReport()
    {
        $report = [];

        $communitiesTable = TableRegistry::get('Communities');
        $communities = $communitiesTable->find('all')
            ->select([
                'id', 'name', 'score', 'notes'
            ])
            ->where(['dummy' => 0])
            ->contain([
                'ParentAreas' => function($q) {
                    return $q->select(['id', 'name', 'fips']);
                },
                'OfficialSurvey' => function($q) {
                    return $q->select(['id', 'alignment']);
                },
                'OrganizationSurvey' => function($q) {
                    return $q->select(['id', 'alignment']);
                }
            ])
            ->order(['Communities.name' => 'ASC']);

        $respondentsTable = TableRegistry::get('Respondents');
        $respondents = $respondentsTable->find('all')
            ->select(['id', 'approved', 'invited', 'survey_id'])
            ->contain([
                'Responses' => function ($q) {
                    return $q->select(['id', 'respondent_id']);
                }
            ])
            ->toArray();
        $respondents = Hash::combine($respondents, '{n}.id', '{n}', '{n}.survey_id');

        $responsesTable = TableRegistry::get('Responses');
        $surveysTable = TableRegistry::get('Surveys');
        $sectors = $surveysTable->getSectors();
        foreach ($communities as $community) {
            // Collect general information about this community
            $report[$community->id] = [
                'name' => $community->name,
                'parentArea' => $community->parent_area->name,
                'parentAreaFips' => $community->parent_area->fips,
                'presentationsGiven' => [
                    'a' => 'No',
                    'b' => 'No',
                    'c' => 'No'
                ],
                'notes' => $community->notes
            ];

            // Collect information about survey responses and alignment
            $surveyTypes = [
                'official_survey' => $community->official_survey,
                'organization_survey' => $community->organization_survey,
            ];
            foreach ($surveyTypes as $key => $survey) {
                $invitationCount = 0;
                $approvedResponseCount = 0;
                $responseRate = 'N/A';
                if ($survey && isset($respondents[$survey->id])) {
                    foreach ($respondents[$survey->id] as $respondent) {
                        if ($respondent->invited) {
                            $invitationCount++;
                        }
                        if ($respondent->approved && ! empty($respondent->responses)) {
                            $approvedResponseCount++;
                        }
                    }
                    if ($invitationCount) {
                        $responseRate = round(($approvedResponseCount / $invitationCount) * 100) . '%';
                    } else {
                        $responseRate = 'N/A';
                    }
                }

                // Format and sum internal alignment
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

                // Determine status
                $correspondingStep = ($key == 'official_survey') ? 2 : 3;
                if ($community->score < $correspondingStep) {
                    $status = 'Not started yet';
                } elseif ($community->score < ($correspondingStep + 1)) {
                    $status = 'In progress';
                } else {
                    $status = 'Complete';
                }

                // PWRRR alignment
                if ($survey) {
                    $alignment = $survey->alignment ? $survey->alignment . '%' : null;
                    $alignmentCalculated = $survey->alignment ? 'Yes' : 'No';
                } else {
                    $alignment = null;
                    $alignmentCalculated = 'No';
                }

                $report[$community->id][$key] = [
                    'invitations' => $invitationCount,
                    'responses' => $approvedResponseCount,
                    'responseRate' => $responseRate,
                    'alignment' => $alignment,
                    'alignmentCalculated' => $alignmentCalculated,
                    'internalAlignment' => $internalAlignment,
                    'status' => $status
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
        $report = $this->getReport();
        $objPHPExcel = $this->getPhpExcelObject();
        $surveysTable = TableRegistry::get('Surveys');
        $sectors = $surveysTable->getSectors();

        // Write metadata
        $title = ($version == 'ocra') ? 'CRI Report for OCRA - ' : 'CRI Admin Report - ';
        $title .= date('F j, Y');
        $author = 'Center for Business and Economic Research, Ball State University';
        $objPHPExcel->getProperties()
            ->setCreator($author)
            ->setLastModifiedBy($author)
            ->setTitle($title)
            ->setSubject($title)
            ->setDescription('');
        $objPHPExcel->getDefaultStyle()->getFont()->setName('Arial');
        $objPHPExcel->getDefaultStyle()->getFont()->setSize(11);

        // Prepare column headers
        $columnTitles = [
            'Community',
            'Area',
            'Area FIPS'
        ];
        $surveyColumnHeaders = [];
        $intAlignmentColOffset = null;
        foreach (['officials', 'organizations'] as $surveyType) {
            $surveyColumnHeaders[$surveyType] = [
                'Invitations',
                'Responses',
                'Completion Rate'
            ];
            $alignmentColHeader = ($version == 'ocra') ? 'Alignment Calculated' : 'Average Alignment';
            $surveyColumnHeaders[$surveyType][] = $alignmentColHeader;

            // Note how many columns come before internal alignment in each survey group
            if (! $intAlignmentColOffset) {
                $intAlignmentColOffset = count($surveyColumnHeaders[$surveyType]);
            }

            if ($version == 'admin') {
                foreach ($sectors as $sector) {
                    $surveyColumnHeaders[$surveyType][] = ucwords($sector);
                }
                $surveyColumnHeaders[$surveyType][] = 'Overall';
            }
            if ($surveyType == 'officials') {
                $surveyColumnHeaders[$surveyType][] = 'Presentation A Given';
                $surveyColumnHeaders[$surveyType][] = 'Presentation B Given';
            } else {
                $surveyColumnHeaders[$surveyType][] = 'Presentation C Given';
            }
            $surveyColumnHeaders[$surveyType][] = 'Status';
        }
        $generalColCount = count($columnTitles);
        $officialsColCount = count($surveyColumnHeaders['officials']);
        $orgsColCount = count($surveyColumnHeaders['organizations']);

        // Get column letters used for determining ranges to apply styles to
        $totalColCount = $generalColCount + $officialsColCount + $orgsColCount;
        $lastCol = $this->getColumnKey($totalColCount - 1);
        $lastGeneralCol = $this->getColumnKey($generalColCount - 1);
        $firstOrgSurveyCol = $this->getColumnKey($generalColCount + $officialsColCount);
        $firstOfficialsSurveyCol = $this->getColumnKey($generalColCount);
        $lastOfficialsSurveyCol = $this->getColumnKey($generalColCount + $officialsColCount - 1);

        // Write title
        $currentRow = 1;
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $currentRow, $title);

        // Style title
        $objPHPExcel->getActiveSheet()->getStyle('A1:A1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 24
            ]
        ]);
        $span = "A{$currentRow}:{$lastCol}{$currentRow}";
        $objPHPExcel->getActiveSheet()->mergeCells($span);

        // Write survey-type grouping headers
        $currentRow++;
        $objPHPExcel
            ->getActiveSheet()
            ->setCellValueByColumnAndRow($generalColCount, $currentRow, 'Community Leadership')
            ->setCellValueByColumnAndRow($generalColCount + $officialsColCount, $currentRow, 'Community Organizations');

        // Style officials-survey grouping header
        $groupingSpan = "{$firstOfficialsSurveyCol}{$currentRow}:{$lastOfficialsSurveyCol}{$currentRow}";
        $border = ['style' => \PHPExcel_Style_Border::BORDER_THIN];
        $centerAligned = ['horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER];
        $objPHPExcel
            ->getActiveSheet()
            ->mergeCells($groupingSpan)
            ->getStyle($groupingSpan)->applyFromArray([
                'font' => ['bold' => true],
                'alignment' => $centerAligned,
                'borders' => ['top' => $border, 'left' => $border, 'right' => $border]
            ]);

        // Style organizations-survey grouping header
        $groupingSpan = "{$firstOrgSurveyCol}{$currentRow}:{$lastCol}{$currentRow}";
        $objPHPExcel
            ->getActiveSheet()
            ->mergeCells($groupingSpan)
            ->getStyle($groupingSpan)->applyFromArray([
                'font' => ['bold' => true],
                'alignment' => $centerAligned,
                'borders' => ['top' => $border, 'left' => $border, 'right' => $border]
            ]);

        if ($version == 'admin') {
            // Write "internal alignment" grouping headers
            $currentRow++;
            $firstIntAlignmentCol = $generalColCount + $intAlignmentColOffset;
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($firstIntAlignmentCol, $currentRow, 'Internal Alignment');
            $secondIntAlignmentCol = $firstIntAlignmentCol + $officialsColCount;
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($secondIntAlignmentCol, $currentRow, 'Internal Alignment');

            // Style "internal alignment" grouping header row
            $intAlignmentColCount = count($sectors) + 1; // Sectors + "Overall"
            $intAlignmentGroups = [];
            $intAlignmentGroups[] =
                $this->getColumnKey($firstIntAlignmentCol) . $currentRow . ':' .
                $this->getColumnKey($firstIntAlignmentCol + $intAlignmentColCount - 1) . $currentRow;
            $intAlignmentGroups[] =
                $this->getColumnKey($secondIntAlignmentCol) . $currentRow . ':' .
                $this->getColumnKey($secondIntAlignmentCol + $intAlignmentColCount - 1) . $currentRow;
            foreach ($intAlignmentGroups as $span) {
                $objPHPExcel->getActiveSheet()
                    ->mergeCells($span)
                    ->getStyle($span)
                    ->applyFromArray([
                        'alignment' => $centerAligned,
                        'borders' => ['top' => $border, 'left' => $border, 'right' => $border],
                        'font' => ['bold' => true]
                    ]);
            }
            $cellsForRightBorder = [
                $lastGeneralCol.$currentRow,
                $lastOfficialsSurveyCol.$currentRow,
                $lastCol.$currentRow
            ];
            foreach ($cellsForRightBorder as $cell) {
                $objPHPExcel->getActiveSheet()
                    ->getStyle("$cell:$cell")
                    ->applyFromArray([
                        'borders' => ['right' => $border]
                    ]);
            }
            $spansForBottomBorder = [];
            $offset = $generalColCount + $intAlignmentColOffset;
            $spansForBottomBorder[] =
                "{$firstOfficialsSurveyCol}{$currentRow}:" .
                $this->getColumnKey($offset - 1) . $currentRow;
            $spansForBottomBorder[] =
                $this->getColumnKey($offset + $intAlignmentColCount) . $currentRow . ':' .
                $this->getColumnKey($offset + $officialsColCount - 1) . $currentRow;
            $spansForBottomBorder[] =
                $this->getColumnKey($offset + $officialsColCount + $intAlignmentColCount) . $currentRow . ':' .
                $lastCol . $currentRow;
            foreach ($spansForBottomBorder as $span) {
                $objPHPExcel->getActiveSheet()
                    ->getStyle($span)
                    ->applyFromArray([
                        'borders' => ['bottom' => $border]
                    ]);
            }
        }

        // Write column titles
        $currentRow++;
        $columnTitles = array_merge($columnTitles, $surveyColumnHeaders['officials']);
        $columnTitles = array_merge($columnTitles, $surveyColumnHeaders['organizations']);
        foreach ($columnTitles as $col => $colTitle) {
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col, $currentRow, $colTitle);
        }

        // Style column titles
        $objPHPExcel->getActiveSheet()
            ->getStyle("A{$currentRow}:{$lastCol}{$currentRow}")
            ->applyFromArray([
                'alignment' => $centerAligned,
                'borders' => ['bottom' => $border],
                'font' => ['bold' => true]
            ]);
        $objPHPExcel->getActiveSheet()
            ->getStyle("A{$currentRow}:{$lastGeneralCol}{$currentRow}")
            ->applyFromArray([
                'borders' => ['left' => $border, 'top' => $border]
            ]);
        $objPHPExcel->getActiveSheet()
            ->getStyle("{$firstOfficialsSurveyCol}{$currentRow}:{$lastOfficialsSurveyCol}{$currentRow}")
            ->applyFromArray([
                'borders' => ['left' => $border, 'right' => $border]
            ]);
        $objPHPExcel->getActiveSheet()
            ->getStyle("{$firstOrgSurveyCol}{$currentRow}:{$lastCol}{$currentRow}")
            ->applyFromArray([
                'borders' => ['left' => $border, 'right' => $border]
            ]);

        // Write data
        $firstDataRow = $currentRow + 1;
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
                    $cells[] = $survey['alignmentCalculated'];
                } elseif ($version == 'admin') {
                    $cells[] = $survey['alignment'];
                    foreach ($sectors as $sector) {
                        $cells[] = $survey['internalAlignment'][$sector];
                    }
                    $cells[] = $survey['internalAlignment']['total'];
                }
                if ($surveyType == 'official_survey') {
                    $cells[] = $community['presentationsGiven']['a'];
                    $cells[] = $community['presentationsGiven']['b'];
                } else {
                    $cells[] = $community['presentationsGiven']['c'];
                }
                $cells[] = $survey['status'];
            }

            // Write values to PHPExcel object
            $currentRow++;
            foreach ($cells as $col => $value) {
                // Non-percentage values
                if (strpos($value, '%') === false) {
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col, $currentRow, $value);

                    // Percentage values
                } else {
                    $cell = $this->getColumnKey($col) . $currentRow;
                    $objPHPExcel->getActiveSheet()->getCell($cell)->setValueExplicit(
                        $value,
                        \PHPExcel_Cell_DataType::TYPE_STRING
                    );
                }
            }
        }

        // Style data cells
        $objPHPExcel->getActiveSheet()
            ->getStyle("A{$firstDataRow}:{$lastCol}{$currentRow}")
            ->applyFromArray([
                'alignment' => ['horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_LEFT],
                'borders' => ['outline' => $border]
            ]);
        $objPHPExcel->getActiveSheet()
            ->getStyle("{$firstOfficialsSurveyCol}{$firstDataRow}:{$firstOfficialsSurveyCol}{$currentRow}")
            ->applyFromArray([
                'borders' => ['left' => $border]
            ]);
        $objPHPExcel->getActiveSheet()
            ->getStyle("{$firstOrgSurveyCol}{$firstDataRow}:{$firstOrgSurveyCol}{$currentRow}")
            ->applyFromArray([
                'borders' => ['left' => $border]
            ]);

        // Set the width of all columns to fit their content
        for ($n = 0; $n < $totalColCount; $n++) {
            $colLetter = $this->getColumnKey($n);
            $objPHPExcel->getActiveSheet()->getColumnDimension($colLetter)->setAutoSize(true);
        }

        return $objPHPExcel;
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
     * @param int $num
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
}