<?php

namespace srag\DataTable\OpencastPageComponent\Implementation\Format;

use ILIAS\DI\Container;
use ILIAS\UI\Renderer;
use ilUtil;
use srag\DataTable\OpencastPageComponent\Component\Column\Column;
use srag\DataTable\OpencastPageComponent\Component\Data\Data;
use srag\DataTable\OpencastPageComponent\Component\Data\Row\RowData;
use srag\DataTable\OpencastPageComponent\Component\Format\Format;
use srag\DataTable\OpencastPageComponent\Component\Settings\Settings;
use srag\DataTable\OpencastPageComponent\Component\Table;

/**
 * Class AbstractFormat
 *
 * @package srag\DataTable\OpencastPageComponent\Implementation\Format
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
abstract class AbstractFormat implements Format
{

    /**
     * @var Container
     */
    protected $dic;
    /**
     * @var object
     */
    protected $tpl;
    /**
     * @var callable
     */
    protected $get_template;


    /**
     * AbstractFormat constructor
     *
     * @param Container $dic
     */
    public function __construct(Container $dic)
    {
        $this->dic = $dic;
    }


    /**
     * @inheritDoc
     */
    public function getDisplayTitle(Table $component) : string
    {
        return $component->getPlugin()->translate("format_" . $this->getFormatId(), Table::LANG_MODULE);
    }


    /**
     * @inheritDoc
     */
    public function getOutputType() : int
    {
        return self::OUTPUT_TYPE_DOWNLOAD;
    }


    /**
     * @inheritDoc
     */
    public function getTemplate() : object
    {
        return $this->tpl;
    }


    /**
     * @return string
     */
    protected abstract function getFileExtension() : string;


    /**
     * @inheritDoc
     */
    public function render(callable $get_template, Table $component, ?Data $data, Settings $settings, Renderer $renderer) : string
    {
        $this->get_template = $get_template;

        $this->initTemplate($component, $data, $settings, $renderer);

        $columns = $this->getColumns($component, $settings);

        $this->handleColumns($component, $columns, $settings, $renderer);

        $this->handleRows($component, $columns, $data, $renderer);

        return $this->renderTemplate($component);
    }


    /**
     * @inheritDoc
     */
    public function deliverDownload(string $data, Table $component) : void
    {
        $filename = $component->getTitle() . "." . $this->getFileExtension();

        ilUtil::deliverData($data, $filename);
    }


    /**
     * @param Table    $component
     * @param Settings $settings
     *
     * @return Column[]
     */
    protected function getColumnsBase(Table $component, Settings $settings) : array
    {
        return array_filter($component->getColumns(), function (Column $column) use ($settings): bool {
            if ($column->isSelectable()) {
                return in_array($column->getKey(), $settings->getSelectedColumns());
            } else {
                return true;
            }
        });
    }


    /**
     * @param Table    $component
     * @param Settings $settings
     *
     * @return Column[]
     */
    protected function getColumnsForExport(Table $component, Settings $settings) : array
    {
        return array_filter($this->getColumnsBase($component, $settings), function (Column $column) : bool {
            return $column->isExportable();
        });
    }


    /**
     * @param Table    $component
     * @param Settings $settings
     *
     * @return Column[]
     */
    protected function getColumns(Table $component, Settings $settings) : array
    {
        return $this->getColumnsForExport($component, $settings);
    }


    /**
     * @param Table     $component
     * @param Data|null $data
     * @param Settings  $settings
     * @param Renderer  $renderer
     */
    protected abstract function initTemplate(Table $component, ?Data $data, Settings $settings, Renderer $renderer) : void;


    /**
     * @param Table    $component
     * @param Column[] $columns
     * @param Settings $settings
     * @param Renderer $renderer
     */
    protected function handleColumns(Table $component, array $columns, Settings $settings, Renderer $renderer) : void
    {
        foreach ($columns as $column) {
            $this->handleColumn($column->getFormatter()
                ->formatHeaderCell($this, $column, $component->getTableId(), $renderer), $component, $column, $settings, $renderer);
        }
    }


    /**
     * @param string   $formated_column
     * @param Table    $component
     * @param Column   $column
     * @param Settings $settings
     * @param Renderer $renderer
     *
     * @return mixed
     */
    protected abstract function handleColumn(string $formated_column, Table $component, Column $column, Settings $settings, Renderer $renderer);


    /**
     * @param Table     $component
     * @param Column[]  $columns
     * @param Data|null $data
     * @param Renderer  $renderer
     */
    protected function handleRows(Table $component, array $columns, ?Data $data, Renderer $renderer) : void
    {
        if ($data !== null) {
            foreach ($data->getData() as $row) {
                $this->handleRow($component, $columns, $row, $renderer);
            }
        }
    }


    /**
     * @param Table    $component
     * @param Column[] $columns
     * @param RowData  $row
     * @param Renderer $renderer
     */
    protected function handleRow(Table $component, array $columns, RowData $row, Renderer $renderer) : void
    {
        foreach ($columns as $column) {
            $this->handleRowColumn($column->getFormatter()
                ->formatRowCell($this, $row($column->getKey()), $column, $row, $component->getTableId(), $renderer));
        }
    }


    /**
     * @param string $formated_row_column
     */
    protected abstract function handleRowColumn(string $formated_row_column);


    /**
     * @param Table $component
     *
     * @return string
     */
    protected abstract function renderTemplate(Table $component) : string;
}
