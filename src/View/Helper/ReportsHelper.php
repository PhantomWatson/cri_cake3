<?php
namespace App\View\Helper;

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
            'Average Alignment',
        ];
        foreach ($sectors as $sector) {
            $cells[] = ucwords($sector);
        }
        $cells[] = 'Overall';
        if ($type == 'officials') {
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
        $numericColumns = array_merge($numericColumns, [
            'Overall',
            'Invitations',
            'Responses',
            'Completion Rate',
            'Average Alignment'
        ]);
        foreach ($cells as $cell) {
            // Build CSS class string
            $class = 'survey';
            if (in_array(strtolower($cell), $sectors) || $cell == 'Overall') {
                $class .= ' int-alignment';
            }
            if ($cell == 'Production') {
                $class .= ' int-alignment-left-edge';
            } elseif ($cell == 'Overall') {
                $class .= ' int-alignment-right-edge';
            } elseif ($cell == 'Status') {
                $class .= ' survey-status';
            }

            // Add "data type" data attribute, used for sorting
            $dataType = in_array($cell, $numericColumns) ? 'float' : 'string';

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

            $retval .= "<th class=\"{$class}\" data-survey-type=\"{$type}\" data-sort=\"{$dataType}\">";
            $retval .= $cell;
            $retval .= '</th>';
        }
        $retval .=
            '<th class="minimized-status-header" data-survey-type="' . $type . '">' .
            '<button class="survey-toggler btn btn-link" data-survey-type="' . $type . '">' .
            (($type == 'officials') ? 'Community Leadership Status' : 'Community Organizations Status') .
            '</button>' .
            '</th>';

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
