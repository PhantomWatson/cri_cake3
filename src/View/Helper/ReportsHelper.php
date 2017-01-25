<?php
namespace App\View\Helper;

use App\Reports\Reports;
use Cake\View\Helper;

class ReportsHelper extends Helper
{
    /**
     * Returns a set of <th> elements for the header of the
     * officials survey or organization survey columns
     *
     * @param array $sectors Sectors
     * @param string $type 'officials' or 'organizations'
     * @return string
     */
    public function surveyHeader($sectors, $type)
    {
        $cells = [
            'Invitations',
            'Responses',
            'Completion Rate',
            'vs Local Area',
            'vs Wider Area'
        ];
        foreach ($sectors as $sector) {
            $cells[] = ucwords($sector);
        }
        $cells[] = 'Overall';
        if ($type == 'officials') {
            $cells[] = 'Aware of Plan';
            $cells[] = 'Unaware / Unknown';
            $cells[] = 'Presentation A';
            $cells[] = 'Presentation B';
        } else {
            $cells[] = 'Presentation C';
        }
        $cells[] = 'Status';
        $retval = '';
        $numericColumns = $sectors;
        array_walk($numericColumns, function (&$sector) {
            $sector = ucwords($sector);
        });
        foreach ($cells as $cell) {
            // Build CSS class string
            $class = 'survey';
            if (in_array(strtolower($cell), $sectors) || $cell == 'Overall') {
                $class .= ' int-alignment';
                if ($cell == 'Overall') {
                    $class .= ' int-overall-alignment';
                } else {
                    $class .= ' int-alignment-details';
                }
            }
            if ($cell == 'Production') {
                $class .= ' int-alignment-left-edge';
            } elseif ($cell == 'Overall') {
                $class .= ' int-alignment-right-edge';
            } elseif ($cell == 'Status') {
                $class .= ' survey-status';
            }

            // Abbreviate
            $abbreviations = [
                'Production' => 'P',
                'Wholesale' => 'W',
                'Retail' => 'Ret',
                'Residential' => 'Res',
                'Recreation' => 'Rec'
            ];
            if (isset($abbreviations[$cell])) {
                $cell = $abbreviations[$cell];
            }

            $retval .= "<th class=\"$class\">$cell</th>";
        }
        $label = ($type == 'officials') ? 'Community Leadership Status' : 'Community Organizations Status';
        $button = "<button class=\"survey-toggler\">$label</button>";
        $retval .= "<th class=\"minimized-status-header\">$button</th>";

        return $retval;
    }

    /**
     * Returns the 'sort value' used to determine how to position
     * this cell when the table is being sorted by this cell's column
     *
     * @param string $value Value displayed in cell
     * @return string
     */
    public function sortValue($value)
    {
        $sortValue = str_replace('%', '', $value);
        if (! is_numeric($sortValue)) {
            $sortValue = -1;
        }

        return 'data-sort-value="' . $sortValue . '"';
    }
}
