<?php
declare(strict_types=1);

namespace App\Reports;

use App\Model\Table\ProductsTable;
use Cake\ORM\TableRegistry;
use Cake\Utility\Hash;

class Reports
{
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
        $surveysTable = TableRegistry::get('Surveys');

        foreach ($communities as $community) {
            // Collect general information about this community
            $report[$community->id] = [
                'name' => $community->name,
                'parentArea' => $community->parent_area->name,
                'presentationsGiven' => $this->getPresentationStatuses($community),
                'notes' => $community->notes,
                'recentActivity' => $community->activity_records,
            ];

            // Collect information about survey responses and alignment
            $surveyTypes = [
                'official_survey' => $community->official_survey,
                'organization_survey' => $community->organization_survey,
            ];
            foreach ($surveyTypes as $surveyKey => $survey) {
                $invitationCount = $this->getInvitationCount($survey, $respondents);
                $approvedResponseCount = $this->getApprovedResponseCount($survey, $respondents);
                $surveyType = str_replace('_survey', '', $surveyKey);
                $report[$community->id][$surveyKey] = [
                    'invitations' => $invitationCount,
                    'responses' => $approvedResponseCount,
                    'responseRate' => $this->getResponseRate($invitationCount, $approvedResponseCount),
                    'alignments' => [
                        'vsLocal' => $survey['alignment_vs_local'],
                        'vsParent' => $survey['alignment_vs_parent'],
                    ],
                    'internalAlignment' => $this->getInternalAlignment($survey),
                    'awareOfPlanCount' => $responsesTable->getApprovedAwareOfPlanCount($survey['id']),
                    'unawareOfPlanCount' => $responsesTable->getApprovedUnawareOfPlanCount($survey['id']),
                    'status' => $surveysTable->getStatusDescription($community, $surveyType),
                ];
            }
        }

        return $report;
    }

    /**
     * Returns an array of letter => status for each presentation
     *
     * @param \App\Model\Entity\Community $community Community entity
     * @return array
     */
    private function getPresentationStatuses($community)
    {
        $presentationsGiven = [];
        $optOutsTable = TableRegistry::get('OptOuts');
        $productIds = [
            'a' => ProductsTable::OFFICIALS_SURVEY,
            'b' => ProductsTable::OFFICIALS_SUMMIT,
            'c' => ProductsTable::ORGANIZATIONS_SURVEY,
            'd' => ProductsTable::ORGANIZATIONS_SUMMIT,
        ];
        foreach (['a', 'b', 'c', 'd'] as $letter) {
            if (isset($presentationsGiven[$letter])) {
                continue;
            }

            $optedOut = $optOutsTable->optedOut($community->id, $productIds[$letter]);

            if ($optedOut) {
                $presentationsGiven[$letter] = 'Opted out';

                // If a community opts out of a survey, its optional presentation becomes irrelevant
                if ($letter == 'a') {
                    $presentationsGiven['b'] = 'N/A';
                }
                if ($letter == 'c') {
                    $presentationsGiven['d'] = 'N/A';
                }

                continue;
            }

            $date = $community->{'presentation_' . $letter};
            if ($date) {
                $presentationsGiven[$letter] = $date->format('F j, Y');
                continue;
            }

            $presentationsGiven[$letter] = 'Not scheduled';
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
            return round($approvedResponseCount / $invitationCount * 100) . '%';
        } else {
            return 'N/A';
        }
    }

    /**
     * Returns the number of invitations that were sent out for this survey
     *
     * @param \App\Model\Entity\Survey $survey Survey entity
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
     * @param \App\Model\Entity\Survey $survey Survey entity
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
     * @param \App\Model\Entity\Survey $survey Survey entity
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
                },
            ])
            ->toArray();

        return Hash::combine($respondents, '{n}.id', '{n}', '{n}.survey_id');
    }
}
