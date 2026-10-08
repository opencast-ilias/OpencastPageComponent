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
use srag\Plugins\Opencast\Util\OutputResponse;

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
    use OutputResponse;
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

    /**
     * @var \srag\Plugins\Opencast\Container\Container
     */
    private \srag\Plugins\Opencast\Container\Container $container;

    /**
     * @var Services
     */
    private Services $http;

    /**
     * @var Factory
     */
    private Factory $refinery;

    /**
     * @var API
     */
    protected API $api;

    public function __construct()
    {
        global $DIC;
        $this->dic = $DIC;
        $this->container = Init::init($DIC);
        $this->api = $this->container[API::class];
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
                $is_refresh_token = $cmd === self::CMD_REFRESH_JWT_ASYNC;
                if (!$this->checkPlayerAccess($is_refresh_token)) {
                    $this->main_tpl->setOnScreenMessage('failure', 'Access Denied.');
                    $this->dic->ctrl()->returnToParent($this);
                    break;
                }
                if ($is_refresh_token) {
                    $this->{$cmd}();
                    break;
                }
                try {
                    $xoctPlayerGUI = new xoctPlayerGUI(
                        $this->event_repository,
                        $this->legacy_container->paella_config_storage_service(),
                        $this->legacy_container->paella_config_service_factory()
                    );

                    $xoctPlayerGUI->streamVideo();
                } catch (\Throwable $th) {
                    $message = $th->getMessage();
                    if (
                        $message &&
                        str_contains($message, '401') ||
                        str_contains($message, '403')
                    ) {
                        $message = 'Error: Access Denied.';
                    }
                    $this->main_tpl->setOnScreenMessage('failure', $message);
                    echo $message;
                }
                break;
        }
    }

    /**
     * Generates/Refreshes the JWT access token.
     *
     * @return void
     */
    protected function refreshJwtAsync()
    {
        $event_id = $this->http->wrapper()->query()->has(xoctPlayerGUI::IDENTIFIER)
            ? $this->http->wrapper()->query()->retrieve(
                xoctPlayerGUI::IDENTIFIER,
                $this->refinery->kindlyTo()->string()
            )
            : '';
        $response = [
            'status' => 'error',
            'message' => 'Unable to generate access token.',
        ];
        if (
            $event_id &&
            method_exists($this->api, 'isJWTActivated') &&
            $this->api->isJWTActivated()
        ) {
            $refreshed_token = $this->api->refreshTokenForEvent($event_id);
            if ($refreshed_token) {
                $response = [
                    'status' => 'OK',
                    'newToken' => $refreshed_token,
                ];
            } else {
                $response['message'] = 'Invalid token!';
            }
        }

        $response_json_encoded = json_encode($response);
        $this->sendReponse($response_json_encoded);
    }

    protected function checkPlayerAccess(bool $check_secondary = false): bool
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

        $apply_secondary = !$check_secondary;
        if ($check_secondary) {
            $token = TokenRepository::SECONDARY_TOKEN;
        }

        return (new TokenRepository())->checkToken($this->dic->user()->getId(), $event_id, $token, $apply_secondary);
    }
}
