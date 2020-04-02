<?php
require_once __DIR__ . "/../vendor/autoload.php";
require_once './Customizing/global/plugins/Services/Repository/RepositoryObject/OpenCast/vendor/autoload.php';

use srag\DIC\OpenCast\Exception\DICException;
use srag\DIC\OpencastPageComponent\DICTrait;
use srag\Plugins\Opencast\UI\Input\EventFormGUI;
use srag\Plugins\Opencast\UI\Input\Plupload;
use srag\Plugins\OpencastPageComponent\Authorization\TokenRepository;
use srag\Plugins\OpencastPageComponent\Utils\OpencastPageComponentTrait;

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

    use DICTrait;
    use OpencastPageComponentTrait;
    const PLUGIN_CLASS_NAME = ilOpencastPageComponentPlugin::class;
    const TOKEN = 'token';
    const CMD_UPLOAD_CHUNKS = EventFormGUI::PARENT_CMD_UPLOAD_CHUNKS;
    const CMD_CREATE = EventFormGUI::PARENT_CMD_CREATE;
    const CMD_CANCEL = EventFormGUI::PARENT_CMD_CANCEL;
    const P_GET_RETURN_LINK = 'return_link';


    /**
     * ocpcRouterGUI constructor.
     */
    public function __construct()
    {
        xoctConf::setApiSettings();
    }


    /**
     */
    public function executeCommand()
    {
        $cmd = self::dic()->ctrl()->getCmd();
        $next_class = self::dic()->ctrl()->getNextClass();
        switch ($next_class) {
            case strtolower(xoctPlayerGUI::class):
                if (!$this->checkPlayerAccess()) {
                    ilUtil::sendFailure('Access Denied.');
                    self::dic()->ctrl()->returnToParent($this);
                }
                xoctConf::setApiSettings();
                $xoctPlayerGUI = new xoctPlayerGUI();
                $xoctPlayerGUI->streamVideo();
                break;
            default:
                switch ($cmd) {
                    case self::CMD_UPLOAD_CHUNKS:
                    case self::CMD_CREATE:
                    case self::CMD_CANCEL:
                        $return_link = filter_input(INPUT_GET, self::P_GET_RETURN_LINK, FILTER_SANITIZE_STRING);
                        self::dic()->ctrl()->setParameter($this, self::P_GET_RETURN_LINK, urlencode($return_link));
                        $this->{$cmd}();
                }
        }
    }


    /**
     * @return bool
     */
    protected function checkPlayerAccess() : bool
    {
        $token = filter_input(INPUT_GET, self::TOKEN, FILTER_SANITIZE_STRING);
        $event_id = filter_input(INPUT_GET, xoctPlayerGUI::IDENTIFIER, FILTER_SANITIZE_STRING);

        return (new TokenRepository())->checkToken(self::dic()->user()->getId(), $event_id, $token);
    }


    /**
     *
     * @throws ilException
     */
    protected function uploadChunks()
    {
        $xoctPlupload = new Plupload();
        $xoctPlupload->handleUpload();
    }


    /**
     * @throws ReflectionException
     * @throws DICException
     * @throws ilDateTimeException
     * @throws ilException
     * @throws ilTimeZoneException
     * @throws xoctException
     */
    protected function create()
    {
        $xoctUser = xoctUser::getInstance(self::dic()->user());
        $xoctEventFormGUI = new EventFormGUI($this, new xoctEvent());

        $xoctAclStandardSets = new xoctAclStandardSets($xoctUser->getOwnerRoleName() ? array($xoctUser->getOwnerRoleName(), $xoctUser->getUserRoleName()) : array());
        $xoctEventFormGUI->getObject()->setAcl($xoctAclStandardSets->getAcls());

        if ($xoctEventFormGUI->saveObject()) {
            ilUtil::sendSuccess(self::plugin()->translate('msg_created'), true);
            $this->cancel();
        }
        $xoctEventFormGUI->setValuesByPost();
        self::dic()->mainTemplate()->getStandardTemplate();
        self::dic()->mainTemplate()->setContent($xoctEventFormGUI->getHTML());
        self::dic()->mainTemplate()->show();
    }


    /**
     *
     */
    protected function cancel()
    {
        $return_url = filter_input(INPUT_GET, self::P_GET_RETURN_LINK, FILTER_SANITIZE_STRING);
        self::dic()->ctrl()->redirectToURL(htmlspecialchars_decode($return_url));
    }


    /**
     * @param string $var
     *
     * @return string
     */
    public function txt(string $var) : string
    {
        return ilOpenCastPlugin::getInstance()->txt('event_' . $var);
    }
}