<?php
namespace App\Reports;

class Spreadsheet
{
    private $columnTitles;
    private $currentRow;
    private $objPHPExcel;

    /**
     * Spreadsheet constructor
     */
    public function __construct()
    {
        $includePath = ROOT . DS . 'vendor' . DS . 'phpoffice' . DS . 'phpexcel' . DS . 'Classes';
        require_once  $includePath . DS . 'PHPExcel.php';
        \PHPExcel_Cell::setValueBinder(new \PHPExcel_Cell_AdvancedValueBinder());
        $this->objPHPExcel = new \PHPExcel();
        $this->objPHPExcel->setActiveSheetIndex(0);
        $this->setDefaultStyles();
        $this->currentRow = 1;
    }

    /**
     * Sets the width of spreadsheet cells
     *
     * @param array $widthsByColTitle array of column title => width, e.g. ['vs Local Area' => 9, ...]
     * @return $this
     */
    public function setCellWidth($widthsByColTitle = [])
    {
        foreach ($widthsByColTitle as $title => $width) {
            $colNum = array_search($title, $this->columnTitles);
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

        return $this;
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
     * Returns the letter corresponding to the rightmost populated column
     *
     * @return string
     */
    private function getLastColumnLetter()
    {
        $columnCount = count($this->columnTitles) - 1;

        return $this->getColumnKey($columnCount);
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
     * Sets the author metadata for this spreadsheet
     *
     * @param string $author Author of spreadsheet
     * @return $this
     */
    public function setAuthor($author)
    {
        $this->objPHPExcel->getProperties()
            ->setCreator($author)
            ->setLastModifiedBy($author);

        return $this;
    }

    /**
     * Sets the title metadata for this spreadsheet
     *
     * @param string $title Title of spreadsheet
     * @return $this
     */
    public function setMetadataTitle($title)
    {
        $this->objPHPExcel->getProperties()
            ->setTitle($title)
            ->setSubject($title);

        return $this;
    }

    /**
     * Sets the title that's displayed on the current sheet's tab
     *
     * @param $title
     * @return $this
     */
    public function setActiveSheetTitle($title)
    {
        $this->objPHPExcel->getActiveSheet()->setTitle($title);

        return $this;
    }

    /**
     * Sets the columnTitles property for this spreadsheet
     *
     * @param array $columnTitles Array of column titles
     * @return $this
     */
    public function setColumnTitles($columnTitles)
    {
        $this->columnTitles = $columnTitles;

        return $this;
    }

    /**
     * Returns this spreadsheet's PHPExcel object
     *
     * @return \PHPExcel
     */
    public function get()
    {
        return $this->objPHPExcel;
    }

    /**
     * Writes and styles a title at the top of the current sheet
     *
     * @param string $title Sheet title
     * @return $this
     */
    public function writeSheetTitle($title)
    {
        $this->write(0, $this->currentRow, $title);

        // Style title
        $this->objPHPExcel->getActiveSheet()->getStyle('A1:A1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 24
            ]
        ]);
        $lastCol = $this->getLastColumnLetter();
        $span = "A1:{$lastCol}1";
        $this->objPHPExcel->getActiveSheet()->mergeCells($span);

        return $this;
    }

    /**
     * Writes a value to the spreadsheet
     *
     * @param int $colNum Column number
     * @param int $rowNum Row number
     * @param string $value Value
     * @return $this
     */
    private function write($colNum, $rowNum, $value)
    {
        $this->objPHPExcel
            ->getActiveSheet()
            ->setCellValueByColumnAndRow($colNum, $rowNum, $value);

        return $this;
    }

    /**
     * Writes a series of values to the fields in the current row
     *
     * @param $row
     * @return $this
     */
    public function writeRow($row)
    {
        foreach ($row as $columnNumber => $value) {
            if ($value === null) {
                continue;
            }

            $this->write($columnNumber, $this->currentRow, $value);
        }

        return $this;
    }

    /**
     * Increments the currentRow property
     *
     * @return $this
     */
    public function nextRow()
    {
        $this->currentRow++;

        return $this;
    }

    /**
     * Applies styles to a specified span of cells in the current row,
     * or the entire row if $fromCol and $toCol are omitted
     *
     * @param array $styles Array of PHPExcel compatible style data
     * @param int $fromCol First column number
     * @param int|bool $toCol Last column number
     * @return $this
     */
    public function styleRow($styles, $fromCol = 0, $toCol = false)
    {
        $fromCell = $this->getColumnKey($fromCol) . $this->currentRow;
        $toColLetter = ($toCol === false) ? $this->getLastColumnLetter() : $this->getColumnKey($toCol);
        $toCell =  $toColLetter . $this->currentRow;

        $this->objPHPExcel->getActiveSheet()
            ->getStyle("$fromCell:$toCell")
            ->applyFromArray($styles);

        return $this;
    }
}