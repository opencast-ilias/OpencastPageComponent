<?php

namespace srag\CustomInputGUIs\OpencastPageComponent\TabsInputGUI;

use ilFormPropertyGUI;
use srag\CustomInputGUIs\OpencastPageComponent\PropertyFormGUI\PropertyFormGUI;
use srag\DIC\OpencastPageComponent\DICTrait;

/**
 * Class MultilangualTabsInputGUI
 *
 * @package srag\CustomInputGUIs\OpencastPageComponent\TabsInputGUI
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
class MultilangualTabsInputGUI
{

    use DICTrait;


    /**
     * @param array $items
     * @param bool  $default_language
     * @param bool  $default_required
     *
     * @return array
     */
    public static function generate(array $items, bool $default_language = false, bool $default_required = true) : array
    {
        foreach (self::getLanguages($default_language) as $lang_key => $lang_title) {
            $tab_items = [];

            foreach ($items as $item_key => $item) {
                $tab_item = $item;

                if ($default_required && $lang_key === "default") {
                    $tab_item[PropertyFormGUI::PROPERTY_REQUIRED] = true;
                }

                $tab_items[$item_key . "_" . $lang_key] = $tab_item;
            }

            $tab = [
                PropertyFormGUI::PROPERTY_CLASS    => TabsInputGUITab::class,
                PropertyFormGUI::PROPERTY_SUBITEMS => $tab_items,
                "setTitle"                         => $lang_title,
                "setActive"                        => ($lang_key === ($default_language ? "default" : self::dic()->language()->getLangKey()))
            ];

            $tabs[] = $tab;
        }

        return $tabs;
    }


    /**
     * @param TabsInputGUI        $tabs
     * @param ilFormPropertyGUI[] $inputs
     * @param bool                $default_language
     * @param bool                $default_required
     */
    public static function generateLegacy(TabsInputGUI $tabs, array $inputs, bool $default_language = false, bool $default_required = true)/*:void*/
    {
        foreach (self::getLanguages($default_language) as $lang_key => $lang_title) {
            $tab = new TabsInputGUITab();
            $tab->setTitle($lang_title);
            $tab->setActive($lang_key === ($default_language ? "default" : self::dic()->language()->getLangKey()));

            foreach ($inputs as $input) {
                $tab_input = clone $input;

                if ($default_required && $lang_key === "default") {
                    $input->setRequired(true);
                }

                $tab_input->setPostVar($input->getPostVar() . "_" . $lang_key);
                $tab->addInput($tab_input);
            }

            $tabs->addTab($tab);
        }
    }


    /**
     * @param bool $default
     *
     * @return array
     */
    public static function getLanguages(bool $default = false) : array
    {
        $lang_keys = self::dic()->language()->getInstalledLanguages();

        if ($default) {
            array_unshift($lang_keys, "default");
        }

        return array_combine($lang_keys, array_map("strtoupper", $lang_keys));
    }


    /**
     * @param array       $values
     * @param string|null $lang_key
     * @param bool        $use_default_if_not_set
     *
     * @return mixed
     */
    public static function getValueForLang(array $values,/*?*/ string $lang_key = null, bool $use_default_if_not_set = true)
    {
        if (empty($lang_key)) {
            $lang_key = self::dic()->language()->getLangKey();
        }

        if (!empty(($values[$lang_key]))) {
            return $values[$lang_key];
        }

        if ($use_default_if_not_set) {
            return $values["default"];
        } else {
            return $values[$lang_key];
        }
    }


    /**
     * @param array  $values
     * @param mixed  $value
     * @param string $lang_key
     */
    public static function setValueForLang(array &$values, $value, string $lang_key)/*:void*/
    {
        $values[$lang_key] = $value;
    }


    /**
     * MultilangualTabsInputGUI constructor
     */
    private function __construct()
    {

    }
}

