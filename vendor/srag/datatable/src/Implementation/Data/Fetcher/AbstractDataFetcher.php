<?php

namespace srag\DataTable\OpencastPageComponent\Implementation\Data\Fetcher;

use ILIAS\DI\Container;
use srag\DataTable\OpencastPageComponent\Component\Data\Fetcher\DataFetcher;
use srag\DataTable\OpencastPageComponent\Component\Table;

/**
 * Class AbstractDataFetcher
 *
 * @package srag\DataTable\OpencastPageComponent\Implementation\Data\Fetcher
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
abstract class AbstractDataFetcher implements DataFetcher
{

    /**
     * @var Container
     */
    protected $dic;


    /**
     * AbstractDataFetcher constructor
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
    public function getNoDataText(Table $component) : string
    {
        return $component->getPlugin()->translate("no_data", Table::LANG_MODULE);
    }


    /**
     * @inheritDoc
     */
    public function isFetchDataNeedsFilterFirstSet() : bool
    {
        return false;
    }
}
