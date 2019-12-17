<?php

namespace srag\DataTable\OpencastPageComponent\Component\Column\Formatter;

use ILIAS\UI\Renderer;
use srag\DataTable\OpencastPageComponent\Component\Column\Column;
use srag\DataTable\OpencastPageComponent\Component\Data\Row\RowData;
use srag\DataTable\OpencastPageComponent\Component\Format\Format;

/**
 * Interface Formatter
 *
 * @package srag\DataTable\OpencastPageComponent\Component\Column\Formatter
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
interface Formatter
{

    /**
     * @param Format   $format
     * @param Column   $column
     * @param string   $table_id
     * @param Renderer $renderer
     *
     * @return string
     */
    public function formatHeaderCell(Format $format, Column $column, string $table_id, Renderer $renderer) : string;


    /**
     * @param Format   $format
     * @param mixed    $value
     * @param Column   $column
     * @param RowData  $row
     * @param string   $table_id
     * @param Renderer $renderer
     *
     * @return string
     */
    public function formatRowCell(Format $format, $value, Column $column, RowData $row, string $table_id, Renderer $renderer) : string;
}
