<?php

namespace srag\DataTable\OpencastPageComponent\Implementation\Column\Formatter;

use ilExcel;
use ILIAS\UI\Renderer;
use srag\DataTable\OpencastPageComponent\Component\Column\Column;
use srag\DataTable\OpencastPageComponent\Component\Data\Row\RowData;
use srag\DataTable\OpencastPageComponent\Component\Format\Format;

/**
 * Class DefaultFormatter
 *
 * @package srag\DataTable\OpencastPageComponent\Implementation\Column\Formatter
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
class DefaultFormatter extends AbstractFormatter
{

    /**
     * @inheritDoc
     */
    public function formatHeaderCell(Format $format, Column $column, string $table_id, Renderer $renderer) : string
    {
        $title = $column->getTitle();

        switch ($format->getFormatId()) {
            case Format::FORMAT_PDF:
                return "<b>{$title}</b>";

            case Format::FORMAT_EXCEL:
                /**
                 * @var ilExcel $tpl
                 */ $tpl = $format->getTemplate()->tpl;
                $cord = $tpl->getColumnCoord($format->getTemplate()->current_col) . $format->getTemplate()->current_row;
                $tpl->setBold($cord . ":" . $cord);

                return $title;

            default:
                return $title;
        }
    }


    /**
     * @inheritDoc
     */
    public function formatRowCell(Format $format, $value, Column $column, RowData $row, string $table_id, Renderer $renderer) : string
    {
        $value = strval($value);

        switch ($format->getFormatId()) {
            case Format::FORMAT_BROWSER:
                if ($value === "") {
                    $value = "&nbsp;";
                }

                return $value;

            default:
                return $value;
        }
    }
}
