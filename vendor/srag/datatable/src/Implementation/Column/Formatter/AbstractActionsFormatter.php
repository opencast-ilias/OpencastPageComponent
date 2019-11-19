<?php

namespace srag\DataTable\OpencastPageComponent\Implementation\Column\Formatter;

use Closure;
use ILIAS\UI\Component\Button\Shy;
use ILIAS\UI\Implementation\Component\Button\Button;
use ILIAS\UI\Renderer;
use srag\DataTable\OpencastPageComponent\Component\Column\Column;
use srag\DataTable\OpencastPageComponent\Component\Data\Row\RowData;
use srag\DataTable\OpencastPageComponent\Component\Format\BrowserFormat;
use srag\DataTable\OpencastPageComponent\Component\Format\Format;
use srag\DataTable\OpencastPageComponent\Component\Table;

/**
 * Class AbstractActionsFormatter
 *
 * @package srag\DataTable\OpencastPageComponent\Implementation\Column\Formatter
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
abstract class AbstractActionsFormatter extends DefaultFormatter
{

    /**
     * @inheritDoc
     *
     * @param BrowserFormat $format
     */
    public function formatRowCell(Format $format, $value, Column $column, RowData $row, string $table_id, Renderer $renderer) : string
    {
        return $renderer->render($this->dic->ui()->factory()->dropdown()
            ->standard(array_map(function (Shy $button) use ($format, $row, $table_id): Shy {
                return Closure::bind(function () use ($button, $format, $row, $table_id)/*:void*/ {
                    if (!empty($this->action) && empty($this->triggered_signals["click"])) {
                        $this->action = $format->getActionUrlWithParams($this->action, [Table::ACTION_GET_VAR => $row->getRowId()], $table_id);
                    }

                    return $this;
                }, $button, Button::class)();
            }, $this->getActions($row)))->withLabel($column->getTitle()));
    }


    /**
     * @param RowData $row
     *
     * @return Shy[]
     */
    protected abstract function getActions(RowData $row) : array;
}
