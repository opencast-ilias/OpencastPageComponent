<?php

namespace srag\CustomInputGUIs\OpencastPageComponent;

use srag\CustomInputGUIs\OpencastPageComponent\ViewControlModeUI\ViewControlModeUI;
use srag\DIC\OpencastPageComponent\DICTrait;

/**
 * Class CustomInputGUIs
 *
 * @package srag\CustomInputGUIs\OpencastPageComponent
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
final class CustomInputGUIs
{

    use DICTrait;

    /**
     * @var self|null
     */
    protected static $instance = null;


    /**
     * CustomInputGUIs constructor
     */
    private function __construct()
    {

    }


    /**
     * @return self
     */
    public static function getInstance() : self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }


    /**
     * @return ViewControlModeUI
     */
    public function viewControlMode() : ViewControlModeUI
    {
        return new ViewControlModeUI();
    }
}
