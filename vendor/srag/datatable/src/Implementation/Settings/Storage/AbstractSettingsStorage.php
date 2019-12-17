<?php

namespace srag\DataTable\OpencastPageComponent\Implementation\Settings\Storage;

use ILIAS\DI\Container;
use srag\DataTable\OpencastPageComponent\Component\Column\Column;
use srag\DataTable\OpencastPageComponent\Component\Settings\Settings as SettingsInterface;
use srag\DataTable\OpencastPageComponent\Component\Settings\Sort\SortField as SortFieldInterface;
use srag\DataTable\OpencastPageComponent\Component\Settings\Storage\SettingsStorage;
use srag\DataTable\OpencastPageComponent\Component\Table;
use srag\DataTable\OpencastPageComponent\Implementation\Settings\Sort\SortField;

/**
 * Class AbstractSettingsStorage
 *
 * @package srag\DataTable\OpencastPageComponent\Implementation\Settings\Storage
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
abstract class AbstractSettingsStorage implements SettingsStorage
{

    /**
     * @var Container
     */
    protected $dic;


    /**
     * AbstractSettingsStorage constructor
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
    public function handleDefaultSettings(SettingsInterface $settings, Table $component) : SettingsInterface
    {
        if (!$settings->isFilterSet() && empty($settings->getSortFields())) {
            $settings = $settings->withSortFields(array_map(function (Column $column) use ($component): SortFieldInterface {
                return new SortField($column->getKey(), $column->getDefaultSortDirection());
            }, array_filter($component->getColumns(), function (Column $column) : bool {
                return ($column->isSortable() && $column->isDefaultSort());
            })));
        }

        if (!$settings->isFilterSet() && empty($settings->getSelectedColumns())) {
            $settings = $settings->withSelectedColumns(array_map(function (Column $column) : string {
                return $column->getKey();
            }, array_filter($component->getColumns(), function (Column $column) : bool {
                return ($column->isSelectable() && $column->isDefaultSelected());
            })));
        }

        return $settings;
    }


    /**
     * @param string $string
     *
     * @return string
     */
    protected function strToCamelCase(string $string) : string
    {
        return str_replace("_", "", ucwords($string, "_"));
    }
}
