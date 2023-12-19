<?php

namespace srag\Plugins\OpencastPageComponent\Legacy\ActiveRecordConfig\Config;

use srag\DIC\OpencastPageComponent\DICTrait;

/**
 * Class AbstractFactory
 *
 * @package srag\Plugins\OpencastPageComponent\Legacy\ActiveRecordConfig\Config
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
abstract class AbstractFactory
{

    /**
     * AbstractFactory constructor
     */
    protected function __construct()
    {
    }

    /**
     * @return Config
     */
    public function newInstance(): Config
    {
        $config = new Config();

        return $config;
    }
}
