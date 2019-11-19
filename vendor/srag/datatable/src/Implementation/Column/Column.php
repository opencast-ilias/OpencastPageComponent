<?php

namespace srag\DataTable\OpencastPageComponent\Implementation\Column;

use ILIAS\DI\Container;
use srag\DataTable\OpencastPageComponent\Component\Column\Column as ColumnInterface;
use srag\DataTable\OpencastPageComponent\Component\Column\Formatter\Formatter;
use srag\DataTable\OpencastPageComponent\Component\Settings\Sort\SortField;
use srag\DataTable\OpencastPageComponent\Implementation\Column\Formatter\AbstractActionsFormatter;
use srag\DataTable\OpencastPageComponent\Implementation\Column\Formatter\DefaultFormatter;

/**
 * Class Column
 *
 * @package srag\DataTable\OpencastPageComponent\Implementation\Column
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
class Column implements ColumnInterface
{

    /**
     * @var Container
     */
    protected $dic;
    /**
     * @var string
     */
    protected $key = "";
    /**
     * @var string
     */
    protected $title = "";
    /**
     * @var Formatter
     */
    protected $formatter;
    /**
     * @var bool
     */
    protected $sortable = true;
    /**
     * @var bool
     */
    protected $default_sort = false;
    /**
     * @var int
     */
    protected $default_sort_direction = SortField::SORT_DIRECTION_UP;
    /**
     * @var bool
     */
    protected $selectable = true;
    /**
     * @var bool
     */
    protected $default_selected = true;
    /**
     * @var bool
     */
    protected $exportable = true;


    /**
     * @inheritDoc
     */
    public function __construct(Container $dic, string $key, string $title)
    {
        $this->dic = $dic;

        $this->key = $key;

        $this->title = $title;

        $this->formatter = new DefaultFormatter($this->dic);
    }


    /**
     * @inheritDoc
     */
    public function getKey() : string
    {
        return $this->key;
    }


    /**
     * @inheritDoc
     */
    public function withKey(string $key) : ColumnInterface
    {
        $clone = clone $this;

        $clone->key = $key;

        return $clone;
    }


    /**
     * @inheritDoc
     */
    public function getTitle() : string
    {
        return $this->title;
    }


    /**
     * @inheritDoc
     */
    public function withTitle(string $title) : ColumnInterface
    {
        $clone = clone $this;

        $clone->title = $title;

        return $clone;
    }


    /**
     * @inheritDoc
     */
    public function getFormatter() : Formatter
    {
        return $this->formatter;
    }


    /**
     * @inheritDoc
     */
    public function withFormatter(Formatter $formatter) : ColumnInterface
    {
        $clone = clone $this;

        $clone->formatter = $formatter;

        if ($clone->formatter instanceof AbstractActionsFormatter) {
            $clone->sortable = false;
            $clone->selectable = false;
            $clone->exportable = false;
        }

        return $clone;
    }


    /**
     * @inheritDoc
     */
    public function isSortable() : bool
    {
        return $this->sortable;
    }


    /**
     * @inheritDoc
     */
    public function withSortable(bool $sortable = true) : ColumnInterface
    {
        $clone = clone $this;

        $clone->sortable = $sortable;

        return $clone;
    }


    /**
     * @inheritDoc
     */
    public function isDefaultSort() : bool
    {
        return $this->default_sort;
    }


    /**
     * @inheritDoc
     */
    public function withDefaultSort(bool $default_sort = false) : ColumnInterface
    {
        $clone = clone $this;

        $clone->default_sort = $default_sort;

        return $clone;
    }


    /**
     * @inheritDoc
     */
    public function getDefaultSortDirection() : int
    {
        return $this->default_sort_direction;
    }


    /**
     * @inheritDoc
     */
    public function withDefaultSortDirection(int $default_sort_direction = SortField::SORT_DIRECTION_UP) : ColumnInterface
    {
        $clone = clone $this;

        $clone->default_sort_direction = $default_sort_direction;

        return $clone;
    }


    /**
     * @inheritDoc
     */
    public function isSelectable() : bool
    {
        return $this->selectable;
    }


    /**
     * @inheritDoc
     */
    public function withSelectable(bool $selectable = true) : ColumnInterface
    {
        $clone = clone $this;

        $clone->selectable = $selectable;

        return $clone;
    }


    /**
     * @inheritDoc
     */
    public function isDefaultSelected() : bool
    {
        return $this->default_selected;
    }


    /**
     * @inheritDoc
     */
    public function withDefaultSelected(bool $default_selected = true) : ColumnInterface
    {
        $clone = clone $this;

        $clone->default_selected = $default_selected;

        return $clone;
    }


    /**
     * @inheritDoc
     */
    public function isExportable() : bool
    {
        return $this->exportable;
    }


    /**
     * @inheritDoc
     */
    public function withExportable(bool $exportable = true) : ColumnInterface
    {
        $clone = clone $this;

        $clone->exportable = $exportable;

        return $clone;
    }
}
