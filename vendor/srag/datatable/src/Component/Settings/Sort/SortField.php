<?php

namespace srag\DataTable\OpencastPageComponent\Component\Settings\Sort;

use JsonSerializable;
use stdClass;

/**
 * Interface SortField
 *
 * @package srag\DataTable\OpencastPageComponent\Component\Settings\Sort
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
interface SortField extends JsonSerializable
{

    /**
     * @var int
     */
    const SORT_DIRECTION_UP = 1;
    /**
     * @var int
     */
    const SORT_DIRECTION_DOWN = 2;


    /**
     * @return string
     */
    public function getSortField() : string;


    /**
     * @param string $sort_field
     *
     * @return self
     */
    public function withSortField(string $sort_field) : self;


    /**
     * @return int
     */
    public function getSortFieldDirection() : int;


    /**
     * @param string $sort_field_direction
     *
     * @return self
     */
    public function withSortFieldDirection(int $sort_field_direction) : self;


    /**
     * @return stdClass
     */
    public function jsonSerialize() : stdClass;
}
