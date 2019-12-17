<?php

namespace srag\DataTable\OpencastPageComponent\Implementation\Data\Row;

/**
 * Class GetterRowData
 *
 * @package srag\DataTable\OpencastPageComponent\Implementation\Data\Row
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
class GetterRowData extends AbstractRowData
{

    /**
     * @param string $string
     *
     * @return string
     */
    protected function strToCamelCase(string $string) : string
    {
        return str_replace("_", "", ucwords($string, "_"));
    }


    /**
     * @inheritDoc
     */
    public function __invoke(string $key)
    {
        if (method_exists($this->getOriginalData(), $method = "get" . $this->strToCamelCase($key))) {
            return $this->getOriginalData()->{$method}();
        }

        if (method_exists($this->getOriginalData(), $method = "is" . $this->strToCamelCase($key))) {
            return $this->getOriginalData()->{$method}();
        }

        return null;
    }
}
