<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/docs/LICENSE */

use srag\Plugins\OpencastPageComponent\Config\ConfigForm;
use srag\Plugins\OpencastPageComponent\Config\ConfigRepository;

require_once __DIR__ . "/../vendor/autoload.php";

/**
 * @ilCtrl_isCalledBy ilOpencastPageComponentConfigGUI: ilObjComponentSettingsGUI
 */
class ilOpencastPageComponentConfigGUI extends ilPluginConfigGUI
{
    public const CMD_SAVE = "save";
    public const CMD_CONFIGURE = "configure";
    /**
     * @var \ilCtrl
     */
    private $ctrl;
    /**
     * @var \ilGlobalTemplateInterface
     */
    private $main_tpl;
    /**
     * @var \ilTabsGUI
     */
    private $tabs;
    /**
     * @var \ILIAS\DI\HTTPServices
     */
    protected $http;

    public function __construct()
    {
        global $DIC;
        $this->ctrl = $DIC->ctrl();
        $this->main_tpl = $DIC->ui()->mainTemplate();
        $this->tabs = $DIC->tabs();
        $this->http = $DIC->http();
    }

    protected function buildForm(): ConfigForm
    {
        return new ConfigForm(
            $this,
            self::CMD_SAVE,
            new ConfigRepository()
        );
    }

    #[ReturnTypeWillChange]
    public function performCommand($cmd): void
    {
        $this->tabs->activateTab(self::CMD_CONFIGURE);
        switch ($cmd) {
            case self::CMD_CONFIGURE:
            case self::CMD_SAVE:
                $this->$cmd();
                break;
        }
    }

    protected function configure(): void
    {
        $form = $this->buildForm();
        $this->main_tpl->setContent($form->getHTML());
    }

    protected function save(): void
    {
        $form = $this->buildForm();
        if (!$form->save($this->http->request())) {
            $this->main_tpl->setContent($form->getHTML());
            return;
        }
        $this->ctrl->redirectByClass(self::class, self::CMD_CONFIGURE);
    }

}
