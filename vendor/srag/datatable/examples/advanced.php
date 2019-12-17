<?php

use ILIAS\UI\Renderer;
use srag\DataTable\OpencastPageComponent\Component\Column\Column as ColumnInterface;
use srag\DataTable\OpencastPageComponent\Component\Data\Data as DataInterface;
use srag\DataTable\OpencastPageComponent\Component\Data\Row\RowData;
use srag\DataTable\OpencastPageComponent\Component\Format\Format;
use srag\DataTable\OpencastPageComponent\Component\Settings\Settings;
use srag\DataTable\OpencastPageComponent\Component\Settings\Sort\SortField;
use srag\DataTable\OpencastPageComponent\Implementation\Column\Column;
use srag\DataTable\OpencastPageComponent\Implementation\Column\Formatter\AbstractActionsFormatter;
use srag\DataTable\OpencastPageComponent\Implementation\Column\Formatter\DefaultFormatter;
use srag\DataTable\OpencastPageComponent\Implementation\Column\Formatter\LinkColumnFormatter;
use srag\DataTable\OpencastPageComponent\Implementation\Data\Data;
use srag\DataTable\OpencastPageComponent\Implementation\Data\Fetcher\AbstractDataFetcher;
use srag\DataTable\OpencastPageComponent\Implementation\Data\Row\PropertyRowData;
use srag\DataTable\OpencastPageComponent\Implementation\Format\CSVFormat;
use srag\DataTable\OpencastPageComponent\Implementation\Format\ExcelFormat;
use srag\DataTable\OpencastPageComponent\Implementation\Format\HTMLFormat;
use srag\DataTable\OpencastPageComponent\Implementation\Format\PDFFormat;
use srag\DataTable\OpencastPageComponent\Implementation\Table;

/**
 * @return string
 */
function advanced() : string
{
    global $DIC;

    $DIC->ctrl()->saveParameterByClass(ilSystemStyleDocumentationGUI::class, "node_id");

    $action_url = $DIC->ctrl()->getLinkTargetByClass(ilSystemStyleDocumentationGUI::class, "", "", false, false);

    $table = (new Table("example_datatable_advanced", $action_url, "Advanced example data table", [
        (new Column($DIC, "obj_id", "Id"))->withDefaultSelected(false),
        (new Column($DIC, "title", "Title"))->withFormatter(new LinkColumnFormatter($DIC))->withDefaultSort(true),
        (new Column($DIC, "type", "Type"))->withFormatter(new AdvancedExampleFormatter($DIC)),
        (new Column($DIC, "description", "Description"))->withDefaultSelected(false)->withSortable(false),
        (new Column($DIC, "actions", "Actions"))->withFormatter(new AdvancedExampleActionsFormatter($DIC))
    ], new AdvancedExampleDataFetcher($DIC)
    ))->withFilterFields([
        "title" => $DIC->ui()->factory()->input()->field()->text("Title"),
        "type"  => $DIC->ui()->factory()->input()->field()->text("Type")
    ])->withFormats([
        new CSVFormat($DIC),
        new ExcelFormat($DIC),
        new PDFFormat($DIC),
        new HTMLFormat($DIC)
    ])->withMultipleActions([
        "Action" => $action_url
    ]);

    $info_text = $DIC->ui()->factory()->legacy("");

    $action_row_id = $table->getBrowserFormat()->getActionRowId($table->getTableId());
    if ($action_row_id !== "") {
        $info_text = $info_text = $DIC->ui()->factory()->messageBox()->info("Row id: " . $action_row_id);
    }

    $mutliple_action_row_ids = $table->getBrowserFormat()->getMultipleActionRowIds($table->getTableId());
    if (!empty($mutliple_action_row_ids)) {
        $info_text = $DIC->ui()->factory()->messageBox()->info("Row ids: " . implode(", ", $mutliple_action_row_ids));
    }

    return $DIC->ui()->renderer()->render([$info_text, $table]);
}

/**
 * Class AdvancedExampleFormatter
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
class AdvancedExampleFormatter extends DefaultFormatter
{

    /**
     * @inheritDoc
     */
    public function formatRowCell(Format $format, $value, ColumnInterface $column, RowData $row, string $table_id, Renderer $renderer) : string
    {
        $type = parent::formatRowCell($format, $value, $column, $row, $table_id, $renderer);

        switch ($format->getFormatId()) {
            case Format::FORMAT_BROWSER:
            case Format::FORMAT_PDF:
            case Format::FORMAT_HTML:
                return $renderer->render([
                    $this->dic->ui()->factory()->symbol()->icon()->custom(ilObject::_getIcon($row->getRowId(), "small"), $type),
                    $this->dic->ui()->factory()->legacy($type)
                ]);

            default:
                return $type;
        }
    }
}

/**
 * Class AdvancedExampleActionsFormatter
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
class AdvancedExampleActionsFormatter extends AbstractActionsFormatter
{

    /**
     * @inheritDoc
     */
    public function getActions(RowData $row) : array
    {
        $action_url = $this->dic->ctrl()->getLinkTargetByClass(ilSystemStyleDocumentationGUI::class, "", "", false, false);

        return [
            $this->dic->ui()->factory()->button()->shy("Action", $action_url)
        ];
    }
}

/**
 * Class AdvancedExampleDataFetcher
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
class AdvancedExampleDataFetcher extends AbstractDataFetcher
{

    /**
     * @inheritDoc
     */
    public function fetchData(Settings $settings) : DataInterface
    {
        $sql = 'SELECT *' . $this->getQuery($settings);

        $result = $this->dic->database()->query($sql);

        $rows = [];
        while (!empty($row = $this->dic->database()->fetchObject($result))) {
            $row->title_link = ilLink::_getLink(current(ilObject::_getAllReferences($row->obj_id)));

            $rows[] = new PropertyRowData(strval($row->obj_id), $row);
        }

        $sql = 'SELECT COUNT(obj_id) AS count' . $this->getQuery($settings, true);

        $result = $this->dic->database()->query($sql);

        $max_count = intval($result->fetchAssoc()["count"]);

        return new Data($rows, $max_count);
    }


    /**
     * @param Settings $settings
     * @param bool     $max_count
     *
     * @return string
     */
    protected function getQuery(Settings $settings, bool $max_count = false) : string
    {
        $sql = ' FROM object_data';

        $field_values = array_filter($settings->getFilterFieldValues());

        if (!empty($field_values)) {
            $sql .= ' WHERE ' . implode(' AND ', array_map(function (string $key, string $value) : string {
                    return $this->dic->database()->like($key, ilDBConstants::T_TEXT, '%' . $value . '%');
                }, array_keys($field_values), $field_values));
        }

        if (!$max_count) {
            if (!empty($settings->getSortFields())) {
                $sql .= ' ORDER BY ' . implode(", ", array_map(function (SortField $sort_field) : string {
                        return $this->dic->database()->quoteIdentifier($sort_field->getSortField()) . ' ' . ($sort_field->getSortFieldDirection()
                            === SortField::SORT_DIRECTION_DOWN ? 'DESC' : 'ASC');
                    }, $settings->getSortFields()));
            }

            if (!empty($settings->getOffset()) && !empty($settings->getRowsCount())) {
                $this->dic->database()->setLimit($settings->getRowsCount(), $settings->getOffset());
            }
        }

        return $sql;
    }
}
