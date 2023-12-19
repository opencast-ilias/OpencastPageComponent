<?php

namespace srag\Plugins\OpencastPageComponent\Legacy\ActiveRecordConfig;

use srag\CustomInputGUIs\OpencastPageComponent\TableGUI\TableGUI;

/**
 * Class ActiveRecordConfigTableGUI
 *
 * @package    srag\Plugins\OpencastPageComponent\Legacy\ActiveRecordConfig
 *
 * @author     studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 *
 * @deprecated Please use TableGUI from CustomInputGUIs instead
 */
abstract class ActiveRecordConfigTableGUI extends TableGUI
{

    /**
     * @var string
     *
     * @deprecated
     */
    public const LANG_MODULE = ActiveRecordConfigGUI::LANG_MODULE_CONFIG;

    /**
     * @var string
     *
     * @deprecated
     */
    protected $tab_id;

    /**
     * ActiveRecordConfigTableGUI constructor
     *
     * @param \srag\Plugins\OpencastPageComponent\Legacy\ActiveRecordConfig\ActiveRecordConfigGUI $parent
     * @param string                                                                              $parent_cmd
     * @param string                                                                              $tab_id
     *
     * @deprecated
     */
    public function __construct(ActiveRecordConfigGUI $parent, string $parent_cmd, string $tab_id)
    {
        $this->tab_id = $tab_id;

        parent::__construct($parent, $parent_cmd);
    }

    /**
     * @inheritDoc
     *
     * @deprecated
     */
    protected function initFilterFields()/*: void*/
    {
        $this->setFilterCommand(ActiveRecordConfigGUI::CMD_APPLY_FILTER . "_" . $this->tab_id);
        $this->setResetCommand(ActiveRecordConfigGUI::CMD_RESET_FILTER . "_" . $this->tab_id);
    }

    /**
     * @inheritDoc
     *
     * @deprecated
     */
    protected function initId()/*: void*/
    {
    }

    /**
     * @inheritDoc
     *
     * @deprecated
     */
    protected function initTitle()/*: void*/
    {
        $this->setTitle($this->txt($this->tab_id));
    }
}
