<?php
require_once __DIR__ . "/../vendor/autoload.php";

use srag\DIC\OpencastPageComponent\DICTrait;
use srag\Plugins\OpencastPageComponent\Authorization\TokenRepository;
use srag\Plugins\OpencastPageComponent\Utils\OpencastPageComponentTrait;

/**
 * Class ocpcRouterGUI
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 *
 * @ilCtrl_Calls ocpcRouterGUI: xoctPlayerGUI
 * @ilCtrl_isCalledBy ocpcRouterGUI: ilObjPluginDispatchGUI
 */
class ocpcRouterGUI
{
    use DICTrait;
    use OpencastPageComponentTrait;
    const PLUGIN_CLASS_NAME = ilOpencastPageComponentPlugin::class;
    const TOKEN = 'token';


    /**
     */
    public function executeCommand()
    {
        $next_class = self::dic()->ctrl()->getNextClass();
        switch ($next_class) {
            case strtolower(xoctPlayerGUI::class):
                if (!$this->checkAccess()) {
                    ilUtil::sendFailure('Access Denied.');
                    self::dic()->ctrl()->returnToParent($this);
                }
                $xoctPlayerGUI = new xoctPlayerGUI();
                // self::dic()->ctrl()->forwardCommand($xoctPlayerGUI);
                $xoctPlayerGUI->streamVideo();
                break;
        }
    }


    /**
     * @return bool
     */
    protected function checkAccess() : bool
    {
        $token = filter_input(INPUT_GET, self::TOKEN, FILTER_SANITIZE_STRING);
        $event_id = filter_input(INPUT_GET, xoctPlayerGUI::IDENTIFIER, FILTER_SANITIZE_STRING);
        return (new TokenRepository())->checkToken(self::dic()->user()->getId(), $event_id, $token);
    }
}