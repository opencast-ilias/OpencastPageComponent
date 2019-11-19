<?php

namespace srag\DataTable\OpencastPageComponent\Implementation\Column\Formatter;

use ILIAS\UI\Renderer;
use srag\DataTable\OpencastPageComponent\Component\Column\Column;
use srag\DataTable\OpencastPageComponent\Component\Data\Row\RowData;
use srag\DataTable\OpencastPageComponent\Component\Format\Format;

/**
 * Class LinkColumnFormatter
 *
 * @package srag\DataTable\OpencastPageComponent\Implementation\Column\Formatter
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
class LinkColumnFormatter extends DefaultFormatter
{

    /**
     * @inheritDoc
     */
    public function formatRowCell(Format $format, $title, Column $column, RowData $row, string $table_id, Renderer $renderer) : string
    {
        $link = $row($column->getKey() . "_link");

        if (empty($title) || empty($link)) {
            return $title;
        }

        return $renderer->render($this->dic->ui()->factory()->link()->standard($title, $link));
    }
}
