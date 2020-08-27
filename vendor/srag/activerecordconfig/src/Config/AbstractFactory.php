<?php

namespace srag\ActiveRecordConfig\OpencastPageComponent\Config;

use srag\DIC\OpencastPageComponent\DICTrait;

/**
 * Class AbstractFactory
 *
 * @package srag\ActiveRecordConfig\OpencastPageComponent\Config
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
abstract class AbstractFactory
{

    use DICTrait;

    /**
     * AbstractFactory constructor
     */
    protected function __construct()
    {

    }


    /**
     * @return Config
     */
    public function newInstance() : Config
    {
        $config = new Config();

        return $config;
    }
}
