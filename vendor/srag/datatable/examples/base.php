<?php

use srag\DataTable\OpencastPageComponent\Implementation\Column\Column;
use srag\DataTable\OpencastPageComponent\Implementation\Data\Fetcher\StaticDataFetcher;
use srag\DataTable\OpencastPageComponent\Implementation\Table;

/**
 * @return string
 */
function base() : string
{
    global $DIC;

    $data = array_map(function (int $index) : stdClass {
        return (object) [
            "column1" => $index,
            "column2" => "text $index",
            "column3" => ($index % 2 === 0 ? "true" : "false")
        ];
    }, range(0, 25));

    $DIC->ctrl()->saveParameterByClass(ilSystemStyleDocumentationGUI::class, "node_id");

    $action_url = $DIC->ctrl()->getLinkTargetByClass(ilSystemStyleDocumentationGUI::class, "", "", false, false);

    $table = new Table("example_datatable_actions", $action_url, "Example data table", [
        new Column($DIC, "column1", "Column 1"),
        new Column($DIC, "column2", "Column 2"),
        new Column($DIC, "column3", "Column 3")
    ], new StaticDataFetcher($DIC, $data));

    return $DIC->ui()->renderer()->render($table);
}
