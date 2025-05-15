<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

use srag\Plugins\OpencastPageComponent\Config\ConfigForm;
use srag\Plugins\OpencastPageComponent\Config\ConfigRepository;

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

    public function performCommand(string $cmd): void
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
