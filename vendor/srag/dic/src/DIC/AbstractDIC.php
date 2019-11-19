<?php

namespace srag\DIC\OpencastPageComponent\DIC;

use ILIAS\DI\Container;
use srag\DIC\OpencastPageComponent\Database\DatabaseDetector;
use srag\DIC\OpencastPageComponent\Database\DatabaseInterface;

/**
 * Class AbstractDIC
 *
 * @package srag\DIC\OpencastPageComponent\DIC
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
abstract class AbstractDIC implements DICInterface
{

    /**
     * @var Container
     */
    protected $dic;


    /**
     * @inheritDoc
     */
    public function __construct(Container &$dic)
    {
        $this->dic = &$dic;
    }


    /**
     * @inheritdoc
     */
    public function database() : DatabaseInterface
    {
        return DatabaseDetector::getInstance($this->databaseCore());
    }
}
