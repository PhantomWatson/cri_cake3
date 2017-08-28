<?php
namespace App\Shell;

use App\Model\Entity\Community;
use App\Model\Table\ProductsTable;
use Cake\Console\Shell;
use Cake\Database\Expression\QueryExpression;
use Cake\Datasource\ResultSetInterface;
use Cake\Event\Event;
use Cake\Event\EventManager;
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
        $communities = $this->Communities->find('forAutoAdvancement')->all();

        $statuses = [
            ['Community', 'Step', 'Advanceable', 'Reason']
        ];
        foreach ($communities as $community) {
            $advanceable = $this->isAdvanceable($community);
            $statuses[] = [
                $community->name,
                $community->score,
                $advanceable === true ? 'Yes' : 'No',
                $advanceable === true ? null : $advanceable
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
        $this->loadModel('Communities');
        $communities = $this->Communities->find('forAutoAdvancement')->all();
        $communitiesAdvanceable = false;
        foreach ($communities as $community) {
            $advanceable = $this->isAdvanceable($community);
            if ($advanceable === true) {
                $communitiesAdvanceable = true;
                $this->advance($community);
            }
        }

        if (!$communitiesAdvanceable) {
            $this->out('<info>No advanceable communities found</info>');
        }
    }

    /**
     * Advances the provided community
     *
     * @param Community $community Community entity
     * @return bool
     */
    private function advance($community)
    {
        $this->loadModel('Communities');
        $oldStep = $community->score;
        $newStep = $oldStep + 1;
        $data = [
            'score' => $newStep
        ];
        $community = $this->Communities->patchEntity($community, $data);
        if ($this->Communities->save($community)) {
            $this->out('<success>Advanced ' . $community->name . ' to Step ' . $newStep . '</success>');

            // Dispatch event
            $eventName = 'Model.Community.afterScoreIncrease';
            $metadata = [
                'meta' => [
                    'previousScore' => $oldStep,
                    'newScore' => $newStep,
                    'communityId' => $community->id
                ]
            ];
            $event = new Event($eventName, $this, $metadata);
            EventManager::instance()->dispatch($event);
        } else {
            $this->out('<error>Error advancing ' . $community->name . ' to Step ' . $newStep . '</error>');
        }
    }

    /**
     * Returns whether or not the provided community qualifies for automatic advancement
     *
     * Returns boolean TRUE if it does, or a string explaining why it doesn't
     *
     * @param Community $community Community entity
     * @return bool|string
     */
    private function isAdvanceable($community)
    {
        if (!$community->active) {
            return 'Community is inactive';
        }

        switch ($community->score) {
            case 1:
                return $this->qualifiedForStepTwo($community);
            case 2:
                return $this->qualifiedForStepThree($community);
            case 3:
                return $this->qualifiedForStepFour($community);
            case 4:
                return 'Community is at final step';
            default:
                return 'Community is at an unrecognized step';
        }
    }

    /**
     * Returns whether or not the community meets the requirements for advancement to Step Two
     *
     * Returns boolean TRUE if it does, or a string explaining why it doesn't
     *
     * @param Community $community Community Entity
     * @return bool|string
     */
    private function qualifiedForStepTwo($community)
    {
        $productsPurchased = Hash::extract($community->purchases, '{n}.product_id');

        if (!in_array(ProductsTable::OFFICIALS_SURVEY, $productsPurchased)) {
            return 'Required purchase not made';
        }

        if (!$community->official_survey) {
            return 'Survey has not been created';
        }

        if (!$community->official_survey->active) {
            return 'Survey has not been activated';
        }

        return true;
    }

    /**
     * Returns whether or not the community meets the requirements for advancement to Step Three
     *
     * Returns boolean TRUE if it does, or a string explaining why it doesn't
     *
     * @param Community $community Community entity
     * @return bool|string
     */
    private function qualifiedForStepThree($community)
    {
        $productsPurchased = Hash::extract($community->purchases, '{n}.product_id');
        $optOuts = Hash::extract($community->opt_outs, '{n}.product_id');

        if (!$community->official_survey->responses) {
            return 'Survey hasn\'t received any approved responses yet';
        }

        if ($community->official_survey->active) {
            return 'Survey is still active';
        }

        if (!$community->presentation_a) {
            return 'Presentation A has not been scheduled';
        }

        if ($community->presentation_a->format('Y-m-d') <= date('Y-m-d')) {
            return 'Presentation A has not concluded';
        }

        if (!in_array(ProductsTable::OFFICIALS_SUMMIT, $optOuts)) {
            if (!$community->presentation_b) {
                return 'Presentation B has not been scheduled';
            }

            if ($community->presentation_b->format('Y-m-d') <= date('Y-m-d')) {
                return 'Presentation B has not concluded';
            }
        }

        if (!in_array(ProductsTable::ORGANIZATIONS_SURVEY, $productsPurchased)) {
            return 'Step Three has not been paid for';
        }

        if (!$community->organization_survey) {
            return 'Survey has not been created';
        }

        if (!$community->organization_survey->active) {
            return 'Survey has not been activated';
        }

        return true;
    }

    /**
     * Returns whether or not the community meets the requirements for advancement to Step Four
     *
     * Returns boolean TRUE if it does, or a string explaining why it doesn't
     *
     * @param Community $community Community entity
     * @return bool|string
     */
    private function qualifiedForStepFour($community)
    {
        $productsPurchased = Hash::extract($community->purchases, '{n}.product_id');
        $optOuts = Hash::extract($community->opt_outs, '{n}.product_id');

        if (! $community->organization_survey->responses) {
            return 'Survey hasn\'t received responses yet';
        }

        if ($community->organization_survey->active) {
            return 'Survey is still active';
        }

        if (! $community->presentation_c) {
            return 'Presentation C has not been scheduled';
        }

        if ($community->presentation_c->format('Y-m-d') <= date('Y-m-d')) {
            return 'Presentation C has not concluded';
        }

        if (!in_array(ProductsTable::ORGANIZATIONS_SUMMIT, $optOuts)) {
            if (!$community->presentation_d) {
                return 'Presentation D has not been scheduled';
            }

            if ($community->presentation_d->format('Y-m-d') <= date('Y-m-d')) {
                return 'Presentation D has not concluded';
            }
        }

        if (! in_array(ProductsTable::POLICY_DEVELOPMENT, $productsPurchased)) {
            return 'Step Four has not been paid for';
        }

        return true;
    }
}
