<?php

namespace srag\DataTable\OpencastPageComponent\Implementation\Data\Row;

/**
 * Class PropertyRowData
 *
 * @package srag\DataTable\OpencastPageComponent\Implementation\Data\Row
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
class PropertyRowData extends AbstractRowData
{

    /**
     * @inheritDoc
     */
    public function __invoke(string $key)
    {
        return $this->getOriginalData()->{$key};
    }
}
