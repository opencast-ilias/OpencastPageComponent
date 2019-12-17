<?php

namespace srag\CustomInputGUIs\OpencastPageComponent\TabsInputGUI;

use ilFormPropertyGUI;
use srag\DIC\OpencastPageComponent\DICTrait;

/**
 * Class TabsInputGUITab
 *
 * @package srag\CustomInputGUIs\OpencastPageComponent\TabsInputGUI
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
class TabsInputGUITab
{

    use DICTrait;
    /**
     * @var bool
     */
    protected $active = false;
    /**
     * @var string
     */
    protected $info = "";
    /**
     * @var ilFormPropertyGUI[]
     */
    protected $inputs = [];
    /**
     * @var string
     */
    protected $title = "";


    /**
     * TabsInputGUITab constructor
     */
    public function __construct()
    {

    }


    /**
     * @param ilFormPropertyGUI $input
     */
    public function addInput(ilFormPropertyGUI $input)/*: void*/
    {
        $this->inputs[] = $input;
    }


    /**
     * @return string
     */
    public function getInfo() : string
    {
        return $this->info;
    }


    /**
     * @return ilFormPropertyGUI[]
     */
    public function getInputs() : array
    {
        return $this->inputs;
    }


    /**
     * @return string
     */
    public function getTitle() : string
    {
        return $this->title;
    }


    /**
     * @return bool
     */
    public function isActive() : bool
    {
        return $this->active;
    }


    /**
     * @param bool $active
     */
    public function setActive(bool $active)/* : void*/
    {
        $this->active = $active;
    }


    /**
     * @param string $info
     */
    public function setInfo(string $info)/* : void*/
    {
        $this->info = $info;
    }


    /**
     * @param ilFormPropertyGUI[] $inputs
     */
    public function setInputs(array $inputs)/* : void*/
    {
        $this->inputs = $inputs;
    }


    /**
     * @param string $title
     */
    public function setTitle(string $title)/* : void*/
    {
        $this->title = $title;
    }
}

