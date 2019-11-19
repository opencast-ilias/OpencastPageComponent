<?php

namespace srag\CustomInputGUIs\OpencastPageComponent\CheckboxInputGUI;

use ilCheckboxInputGUI;
use ilTableFilterItem;
use srag\DIC\OpencastPageComponent\DICTrait;

/**
 * Class CheckboxInputGUI
 *
 * @package srag\CustomInputGUIs\OpencastPageComponent\CheckboxInputGUI
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
class CheckboxInputGUI extends ilCheckboxInputGUI implements ilTableFilterItem
{

    use DICTrait;
}
