<?php

namespace srag\DataTable\OpencastPageComponent\Implementation\Column\Formatter;

use ILIAS\UI\Renderer;
use ilLearningProgressBaseGUI;
use srag\DataTable\OpencastPageComponent\Component\Column\Column;
use srag\DataTable\OpencastPageComponent\Component\Data\Row\RowData;
use srag\DataTable\OpencastPageComponent\Component\Format\Format;

/**
 * Class LearningProgressFormatter
 *
 * @package srag\DataTable\OpencastPageComponent\Implementation\Column\Formatter
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
class LearningProgressFormatter extends DefaultFormatter
{

    /**
     * @inheritDoc
     */
    public function formatRowCell(Format $format, $status, Column $column, RowData $row, string $table_id, Renderer $renderer) : string
    {
        $img = ilLearningProgressBaseGUI::_getImagePathForStatus($status);
        $text = ilLearningProgressBaseGUI::_getStatusText($status);

        return $this->dic->ui()->renderer()->render([
            $this->dic->ui()
                ->factory()
                ->icon()
                ->custom($img, $text),
            $this->dic->ui()->factory()->legacy($text)
        ]);
    }
}
