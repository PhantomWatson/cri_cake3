<?php
namespace App\Maps;

use App\Model\Entity\Community;
use App\Model\Table\DeliverablesTable;
use App\Model\Table\DeliveriesTable;
use App\Model\Table\ProductsTable;
use App\Model\Table\PurchasesTable;
use App\Model\Table\SurveysTable;
use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\Log\Log;
use Cake\Network\Exception\InternalErrorException;
use Cake\ORM\TableRegistry;
use Cake\Utility\Text;

/**
 * Class Map
 * @package App\Maps
 * @property Community $community
 */
class Map
{
    const STEP_ONE_PROSPECT = 'Prospective community';
    const STEP_ONE_ENROLLED = 'Enrolled';
    const STEP_TWO_SURVEY = 'Leadership alignment survey phase';
    const STEP_TWO_ANALYSIS = 'Leadership alignment analysis phase';
    const STEP_TWO_CLOSED = 'Leadership alignment complete';
    const STEP_THREE_SURVEY = 'Community org alignment survey phase';
    const STEP_THREE_ANALYSIS = 'Community org alignment analysis phase';
    const STEP_THREE_CLOSED = 'Community org alignment complete';
    const STEP_FOUR_PENDING = 'Policy development in progress';
    const STEP_FOUR_CLOSED = 'Policy development complete';
    public $community;

    /**
     * Map constructor.
     *
     * @param Community $community Community entity
     */
    public function __construct($community)
    {
        $this->community = $community;
    }

    /**
     * Returns the color for the marker for the current community, or NULL if this community should not be displayed
     *
     * @return string|null
     */
    public function getCommunityColor()
    {
        $colors = $this->getColors();

        $phase = $this->getCommunityPhase();

        return $phase ? $colors[$phase] : null;
    }

    /**
     * Returns the phase that the current community is in, or NULL if the community should not be displayed
     *
     * @return null|string
     */
    public function getCommunityPhase()
    {
        switch ($this->community->score) {
            case 1:
                return $this->getStepOnePhase();
            case 2:
                return $this->getStepTwoPhase();
            case 3:
                return $this->getStepThreePhase();
            case 4:
                return $this->getStepFourPhase();
            default:
                return null;
        }
    }

    /**
     * Returns the step one map phase that the community is currently in
     *
     * @return string
     */
    public function getStepOnePhase()
    {
        if (!$this->community->active) {
            return null;
        }

        /** @var PurchasesTable $purchasesTable */
        $purchasesTable = TableRegistry::get('Purchases');
        $productId = ProductsTable::OFFICIALS_SURVEY;
        $isPurchased = $purchasesTable->isPurchased($productId, $this->community->id);

        return $isPurchased ? self::STEP_ONE_ENROLLED : self::STEP_ONE_PROSPECT;
    }

    /**
     * Returns the step two map phase that the community is currently in
     *
     * @return string
     */
    public function getStepTwoPhase()
    {
        /** @var SurveysTable $surveysTable */
        $surveysTable = TableRegistry::get('Surveys');
        $surveyId = $surveysTable->getSurveyId($this->community->id, 'official');
        $hasResponses = $surveysTable->hasResponses($surveyId);
        $isActive = $surveysTable->isActive($surveyId);
        $step = 2;

        if ($hasResponses && !$isActive) {
            if ($this->arePaidPresentationsGiven($step)) {
                return self::STEP_TWO_CLOSED;
            }

            return self::STEP_TWO_ANALYSIS;
        }

        return self::STEP_TWO_SURVEY;
    }

    /**
     * Returns the step three map phase that the community is currently in
     *
     * @return string
     */
    public function getStepThreePhase()
    {
        /** @var SurveysTable $surveysTable */
        $surveysTable = TableRegistry::get('Surveys');
        $surveyId = $surveysTable->getSurveyId($this->community->id, 'official');
        $hasResponses = $surveysTable->hasResponses($surveyId);
        $isActive = $surveysTable->isActive($surveyId);
        $step = 3;

        if ($hasResponses && !$isActive) {
            if ($this->arePaidPresentationsGiven($step)) {
                return self::STEP_THREE_CLOSED;
            }

            return self::STEP_THREE_ANALYSIS;
        }

        return self::STEP_THREE_SURVEY;
    }

    /**
     * Returns the step four map phase that the community is currently in
     *
     * @return string
     */
    public function getStepFourPhase()
    {
        /** @var DeliveriesTable $deliveriesTable */
        $deliveriesTable = TableRegistry::get('Deliveries');
        $deliverableId = DeliverablesTable::POLICY_DEVELOPMENT;
        $delivered = $deliveriesTable->isRecorded($this->community->id, $deliverableId);

        if ($delivered) {
            return self::STEP_FOUR_CLOSED;
        }

        return self::STEP_FOUR_PENDING;
    }

    /**
     * Returns true if all presentations that have been paid for in the specified step have been scheduled and completed
     *
     * @param int $step Either 2 or 3
     * @return bool
     * @throws InternalErrorException
     */
    private function arePaidPresentationsGiven($step)
    {
        if ($step == 2) {
            $presentations = [
                'a' => ProductsTable::OFFICIALS_SURVEY,
                'b' => ProductsTable::OFFICIALS_SUMMIT
            ];
        } elseif ($step == 3) {
            $presentations = [
                'c' => ProductsTable::ORGANIZATIONS_SURVEY,
                'd' => ProductsTable::ORGANIZATIONS_SUMMIT
            ];
        } else {
            throw new InternalErrorException('Invalid CRI Step specified: ' . $step);
        }

        /** @var PurchasesTable $purchasesTable */
        $purchasesTable = TableRegistry::get('Purchases');
        $purchases = $purchasesTable->getAllForCommunity($this->community->id);
        foreach ($purchases as $purchase) {
            if (in_array($purchase->product_id, $presentations)) {
                $presentationLetter = array_search($purchase->product_id, $presentations);
                if (!$this->presentationHasPassed($presentationLetter)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Returns true if the current community's specified presentation has been scheduled and that date has passed
     *
     * @param string $presentationLetter a, b, c, or d
     * @return bool
     */
    private function presentationHasPassed($presentationLetter)
    {
        $date = $this->community->{"presentation_$presentationLetter"};

        return $date != null && $date <= date('Y-m-d');
    }

    /**
     * Returns an array of phase => color
     *
     * @return array
     */
    public static function getColors()
    {
        return [
            self::STEP_ONE_PROSPECT => '#ffedc2',
            self::STEP_ONE_ENROLLED => '#ffce34',
            self::STEP_TWO_SURVEY => '#dfefdd',
            self::STEP_TWO_ANALYSIS => '#9dd09a',
            self::STEP_TWO_CLOSED => '#23ae49',
            self::STEP_THREE_SURVEY => '#d4e4f5',
            self::STEP_THREE_ANALYSIS => '#93c2e8',
            self::STEP_THREE_CLOSED => '#0088ce',
            self::STEP_FOUR_PENDING => '#a287a7',
            self::STEP_FOUR_CLOSED => '#510856'
        ];
    }

    /**
     * Returns an array of numerically-indexed phase names
     *
     * @return array
     */
    public static function getPhases()
    {
        return [
            self::STEP_ONE_PROSPECT,
            self::STEP_ONE_ENROLLED,
            self::STEP_TWO_SURVEY,
            self::STEP_TWO_ANALYSIS,
            self::STEP_TWO_CLOSED,
            self::STEP_THREE_SURVEY,
            self::STEP_THREE_ANALYSIS,
            self::STEP_THREE_CLOSED,
            self::STEP_FOUR_PENDING,
            self::STEP_FOUR_CLOSED
        ];
    }

    /**
     * Returns an array of data for a Google Charts GeoChart
     *
     * @return array
     */
    public static function getMapData()
    {
        $communitiesTable = TableRegistry::get('Communities');
        $communities = $communitiesTable->find()
            ->where(['dummy' => false])
            ->orderAsc('name')
            ->all();

        $phases = Map::getPhases();
        $mapData = [
            [
                '',
                '',
                'Community Name',
                'Phase',
                'Is County',
                [
                    'type' => 'string',
                    'role' => 'tooltip'
                ]
            ]
        ];
        foreach ($communities as $i => $community) {
            $map = new Map($community);
            $phase = $map->getCommunityPhase();
            if (!$phase) {
                continue;
            }
            $phaseNum = array_search($phase, $phases);
            $coordinates = self::getCoordinates($community->name);
            if ($coordinates) {
                $mapData[] = [
                    $coordinates['lat'],
                    $coordinates['lng'],
                    $community->name,
                    $phaseNum + 1,
                    (stripos($community->name, ' county') === false) ? 0 : 1,
                    $phase
                ];
            }
        }

        return $mapData;
    }

    /**
     * Returns an array of latitude and longitude coordinates for the provided location, or false if not resolvable
     *
     * @param string $locationName A location name resolvable by Google Maps Geocoding API
     * @return array|bool
     */
    public static function getCoordinates($locationName)
    {
        return Cache::remember('coordinates-' . Text::slug($locationName), function () use ($locationName) {
            $locationName .= ', Indiana, USA';
            $url = 'https://maps.googleapis.com/maps/api/geocode/json?';
            $url .= 'address=' . urlencode($locationName);
            $url .= '&key=' . Configure::read('google_maps_api_key');
            $response = file_get_contents($url);
            $geocode = json_decode($response);

            if ($geocode->status == 'OK') {
                return [
                    'lat' => $geocode->results[0]->geometry->location->lat,
                    'lng' => $geocode->results[0]->geometry->location->lng
                ];
            }

            Log::write('error', 'Location name not resolvable to GPS coordinates: ' . $locationName);

            return false;
        });
    }
}
