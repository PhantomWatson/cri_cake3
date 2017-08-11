<?php
namespace App\Shell;

use App\Model\Entity\Community;
use App\Model\Table\ProductsTable;
use Cake\Console\Shell;
use Cake\Database\Expression\QueryExpression;
use Cake\ORM\Query;
use Cake\Utility\Hash;

class AutoAdvanceShell extends Shell
{
    /**
     * Display help for this console.
     *
     * @return \Cake\Console\ConsoleOptionParser
     */
    public function getOptionParser()
    {
        $parser = parent::getOptionParser();
        $parser->addSubcommand('run', [
            'help' => 'Auto-advance qualifying communities',
        ]);
        $parser->addSubcommand('status', [
            'help' => 'Display status of all communities and note which qualify for auto-advancement',
        ]);

        return $parser;
    }

    /**
     * Displays status of all communities and notes which qualify for auto-advancement
     *
     * @return void
     */
    public function status()
    {
        $this->loadModel('Communities');

        $communities = $this->Communities->find()
            ->select(['id', 'name', 'score'])
            ->contain([
                'OptOuts',
                'OfficialSurvey' => function ($q) {
                    /** @var Query $q */

                    return $q
                        ->select(['id', 'community_id', 'active'])
                        ->contain([
                            'Responses' => function ($q) {
                                /** @var Query $q */

                                return $q->limit(1);
                            }
                        ]);
                },
                'OrganizationSurvey' => function ($q) {
                    /** @var Query $q */

                    return $q
                        ->select(['id', 'community_id', 'active'])
                        ->contain([
                            'Responses' => function ($q) {
                                /** @var Query $q */

                                return $q->limit(1);
                            }
                        ]);
                },
                'Purchases' => function ($q) {
                    /** @var Query $q */

                    return $q
                        ->select(['id', 'product_id', 'community_id'])
                        ->where(function ($exp) {
                            /** @var QueryExpression $exp */

                            return $exp->isNull('refunded');
                        });
                },
            ])
            ->orderAsc('name');

        $statuses = [
            ['Community', 'Step', 'Advanceable']
        ];
        foreach ($communities as $community) {
            $statuses[] = [
                $community->name,
                $community->score,
                $this->isAdvanceable($community) ? 'Yes' : 'No'
            ];
        }

        $this->helper('Table')->output($statuses);
    }

    /**
     * Auto-advances qualifying communities
     *
     * @return void
     */
    public function run()
    {
        $this->out('Not written yet');
    }

    /**
     * Returns whether or not the provided community qualifies for automatic advancement
     *
     * @param Community $community Community entity
     * @return bool
     */
    private function isAdvanceable($community)
    {
        switch ($community->score) {
            case 1:
                return $this->qualifiedForStepTwo($community);
            case 2:
                return $this->qualifiedForStepThree($community);
            case 3:
                return $this->qualifiedForStepFour($community);
            default:
                return false;
        }
    }

    /**
     * Returns whether or not the community meets the requirements for advancement to Step Two
     *
     * @param Community $community Community Entity
     * @return bool
     */
    private function qualifiedForStepTwo($community)
    {
        $productsPurchased = Hash::extract($community->purchases, '{n}.product_id');

        // Required purchase not made
        if (!in_array(ProductsTable::OFFICIALS_SURVEY, $productsPurchased)) {
            return false;
        }

        // Survey has not been created / activated
        if (!$community->official_survey || !$community->official_survey->active) {
            return false;
        }

        return true;
    }

    /**
     * Returns whether or not the community meets the requirements for advancement to Step Three
     *
     * @param Community $community Community entity
     * @return bool
     */
    private function qualifiedForStepThree($community)
    {
        $productsPurchased = Hash::extract($community->purchases, '{n}.product_id');
        $optOuts = Hash::extract($community->opt_outs, '{n}.product_id');

        // Survey hasn't received responses yet
        if (!$community->official_survey->responses) {
            return false;
        }

        // Survey is still active
        if ($community->official_survey->active) {
            return false;
        }

        // Presentation A has not been scheduled
        if (!$community->presentation_a) {
            return false;
        }

        // Presentation A has not concluded
        if ($community->presentation_a->format('Y-m-d') <= date('Y-m-d')) {
            return false;
        }

        if (!in_array(ProductsTable::OFFICIALS_SUMMIT, $optOuts)) {
            // Presentation B has not been scheduled
            if (!$community->presentation_b) {
                return false;
            }

            // Presentation B has not concluded
            if ($community->presentation_b->format('Y-m-d') <= date('Y-m-d')) {
                return false;
            }
        }

        // Step Three has not been paid for
        if (!in_array(ProductsTable::ORGANIZATIONS_SURVEY, $productsPurchased)) {
            return false;
        }

        // Survey has not been created / activated
        if (!$community->organization_survey || !$community->organization_survey->active) {
            return false;
        }

        return true;
    }

    /**
     * Returns whether or not the community meets the requirements for advancement to Step Four
     *
     * @param Community $community Community entity
     * @return bool
     */
    private function qualifiedForStepFour($community)
    {
        $productsPurchased = Hash::extract($community->purchases, '{n}.product_id');
        $optOuts = Hash::extract($community->opt_outs, '{n}.product_id');

        // Survey hasn't received responses yet
        if (! $community->organization_survey->responses) {
            return false;
        }

        // Survey is still active
        if ($community->organization_survey->active) {
            return false;
        }

        // Presentation A has not been scheduled
        if (! $community->presentation_c) {
            return false;
        }

        // Presentation A has not concluded
        if ($community->presentation_c->format('Y-m-d') <= date('Y-m-d')) {
            return false;
        }

        if (!in_array(ProductsTable::ORGANIZATIONS_SUMMIT, $optOuts)) {
            // Presentation D has not been scheduled
            if (!$community->presentation_d) {
                return false;
            }

            // Presentation D has not concluded
            if ($community->presentation_d->format('Y-m-d') <= date('Y-m-d')) {
                return false;
            }
        }

        // Step Four has not been paid for
        if (! in_array(ProductsTable::POLICY_DEVELOPMENT, $productsPurchased)) {
            return false;
        }

        return true;
    }
}
