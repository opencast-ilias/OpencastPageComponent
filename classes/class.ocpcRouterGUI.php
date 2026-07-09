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

use ILIAS\HTTP\Services;
use ILIAS\Refinery\Factory;
use ILIAS\DI\Container;
use srag\Plugins\Opencast\Model\Event\EventAPIRepository;
use srag\Plugins\Opencast\DI\OpencastDIC;
use srag\Plugins\OpencastPageComponent\Authorization\TokenRepository;
use srag\Plugins\Opencast\Container\Init;

/**
 * Class ocpcRouterGUI
 *
 * @author            Theodor Truffer <tt@studer-raimann.ch>
 *
 * @ilCtrl_Calls      ocpcRouterGUI: xoctPlayerGUI
 * @ilCtrl_isCalledBy ocpcRouterGUI: ilObjPluginDispatchGUI
 */
class ocpcRouterGUI
{
    public const TOKEN = 'token';

    /**
     * @var EventAPIRepository
     */
    private $event_repository;
    private OpencastDIC $legacy_container;
    /**
     * @var Container
     */
    private $dic;
    /**
     * @var \ilGlobalTemplateInterface
     */
    private $main_tpl;
    private ilOpenCastPlugin $opencast_plugin;

    private \srag\Plugins\Opencast\Container\Container $container;
    private Services $http;
    private Factory $refinery;

    public function __construct()
    {
        global $DIC;
        $this->dic = $DIC;
        $this->container = Init::init($DIC);
        $this->main_tpl = $DIC->ui()->mainTemplate();
        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();

        $this->opencast_plugin = $this->container->plugin();
        $this->legacy_container = $this->container->legacy();

        $this->event_repository = $this->container[EventAPIRepository::class];
    }

    public function executeCommand(): void
    {
        $next_class = $this->dic->ctrl()->getNextClass();

        $main_opencast_js_path = $this->opencast_plugin->getDirectory() . '/js/opencast/dist/index.js';
        if (file_exists($main_opencast_js_path)) {
            $this->dic->ui()->mainTemplate()->addJavaScript($main_opencast_js_path);
        }

        switch ($next_class) {
            case strtolower(xoctPlayerGUI::class):
                if (!$this->checkPlayerAccess()) {
                    $this->main_tpl->setOnScreenMessage('failure', 'Access Denied.');
                    $this->dic->ctrl()->returnToParent($this);
                }
                $xoctPlayerGUI = new xoctPlayerGUI(
                    $this->event_repository,
                    $this->legacy_container->paella_config_storage_service(),
                    $this->legacy_container->paella_config_service_factory()
                );
                $xoctPlayerGUI->streamVideo();
                break;
        }
    }

    protected function checkPlayerAccess(): bool
    {
        $token = $this->http->wrapper()->query()->has(self::TOKEN)
            ? $this->http->wrapper()->query()->retrieve(
                self::TOKEN,
                $this->refinery->kindlyTo()->string()
            )
            : '';

        $event_id = $this->http->wrapper()->query()->has(xoctPlayerGUI::IDENTIFIER)
            ? $this->http->wrapper()->query()->retrieve(
                xoctPlayerGUI::IDENTIFIER,
                $this->refinery->kindlyTo()->string()
            )
            : '';

        return (new TokenRepository())->checkToken($this->dic->user()->getId(), $event_id, $token);
    }
}
