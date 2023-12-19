<?php

require_once __DIR__ . "/../vendor/autoload.php";
require_once __DIR__ . '/../../../../Repository/RepositoryObject/OpenCast/vendor/autoload.php';

use ILIAS\DI\Container;
use srag\DIC\OpenCast\Exception\DICException;
use srag\DIC\OpencastPageComponent\DICTrait;
use srag\Plugins\Opencast\Model\ACL\ACLUtils;
use srag\Plugins\Opencast\Model\Config\PluginConfig;
use srag\Plugins\Opencast\Model\Event\EventAPIRepository;
use srag\Plugins\Opencast\Model\Series\SeriesRepository;
use srag\Plugins\Opencast\Model\Series\SeriesAPIRepository;
use srag\Plugins\Opencast\Model\Event\Request\UploadEventRequest;
use srag\Plugins\Opencast\Model\Event\Request\UploadEventRequestPayload;
use srag\Plugins\Opencast\Model\Metadata\Definition\MDDataType;
use srag\Plugins\Opencast\Model\Metadata\Definition\MDFieldDefinition;
use srag\Plugins\Opencast\Model\Metadata\Metadata;
use srag\Plugins\Opencast\Model\Metadata\MetadataField;
use srag\Plugins\Opencast\Model\WorkflowParameter\Config\WorkflowParameter;
use srag\Plugins\Opencast\Model\WorkflowParameter\Processing;
use srag\Plugins\Opencast\Model\User\xoctUser;
use srag\Plugins\Opencast\Model\TermsOfUse\ToUManager;
use srag\Plugins\Opencast\UI\EventFormBuilder;
use srag\Plugins\Opencast\UI\Input\Plupload;
use srag\Plugins\Opencast\DI\OpencastDIC;
use srag\Plugins\Opencast\Util\FileTransfer\UploadStorageService;
use srag\Plugins\OpencastPageComponent\Authorization\TokenRepository;

/**
 * Class ocpcRouterGUI
 *
 * @author            Theodor Truffer <tt@studer-raimann.ch>
 *
 * @ilCtrl_Calls      ocpcRouterGUI: xoctPlayerGUI, xoctFileUploadHandlerGUI
 * @ilCtrl_isCalledBy ocpcRouterGUI: ilObjPluginDispatchGUI
 */
class ocpcRouterGUI
{
    public const TOKEN = 'token';
    public const CMD_UPLOAD_CHUNKS = 'uploadChunks';
    public const CMD_CREATE = 'create';
    public const CMD_CANCEL = 'cancel';
    public const P_GET_RETURN_LINK = 'return_link';

    /**
     * @var EventAPIRepository
     */
    private $event_repository;
    /**
     * @var SeriesRepository
     */
    private $series_repository;
    /**
     * @var ACLUtils
     */
    private $acl_utils;
    /**
     * @var \srag\Plugins\Opencast\DI\OpencastDIC
     */
    private $opencast_dic;
    /**
     * @var Container
     */
    private $dic;
    /**
     * @var \ilGlobalTemplateInterface
     */
    private $main_tpl;
    /**
     * @var \ilOpencastPageComponentPlugin
     */
    private $plugin;

    public function __construct()
    {
        global $DIC;
        global $DIC, $opencastContainer;
        $this->plugin = ilOpencastPageComponentPlugin::getInstance();
        $this->main_tpl = $DIC->ui()->mainTemplate();
        $this->dic = $DIC;
        $this->opencast_dic = OpencastDIC::getInstance();
        $this->opencast_dic->overwriteService(
            'upload_handler',
            function (): \xoctFileUploadHandlerGUI {
                return new xoctFileUploadHandlerGUI(
                    $this->opencast_dic->upload_storage_service(),
                    $this->dic->ctrl()->getLinkTargetByClass(
                        [ilObjPluginDispatchGUI::class, ocpcRouterGUI::class, xoctFileUploadHandlerGUI::class], 'upload'
                    ),
                    $this->dic->ctrl()->getLinkTargetByClass(
                        [ilObjPluginDispatchGUI::class, ocpcRouterGUI::class, xoctFileUploadHandlerGUI::class], 'info'
                    ),
                    $this->dic->ctrl()->getLinkTargetByClass(
                        [ilObjPluginDispatchGUI::class, ocpcRouterGUI::class, xoctFileUploadHandlerGUI::class], 'remove'
                    )
                );
            }
        );

        $this->event_repository = $opencastContainer[EventAPIRepository::class];
        $this->series_repository = $opencastContainer->get(SeriesAPIRepository::class);
//        PluginConfig::setApiSettings();
    }

    public function executeCommand(): void
    {
        PluginConfig::setApiSettings();
        $cmd = $this->dic->ctrl()->getCmd();
        $next_class = $this->dic->ctrl()->getNextClass();
        switch ($next_class) {
            case strtolower(xoctFileUploadHandlerGUI::class):
                $fileUploadHandler = new xoctFileUploadHandlerGUI(
                    new UploadStorageService(
                        $this->dic->filesystem()->temp(),
                        $this->dic->upload()
                    )
                );
                $this->dic->ctrl()->forwardCommand($fileUploadHandler);
                break;
            case strtolower(xoctPlayerGUI::class):
                if (!$this->checkPlayerAccess()) {
                    $this->main_tpl->setOnScreenMessage('failure', 'Access Denied.');
                    $this->dic->ctrl()->returnToParent($this);
                }
                $xoctPlayerGUI = new xoctPlayerGUI(
                    $this->event_repository,
                    $this->opencast_dic->paella_config_storage_service(),
                    $this->opencast_dic->paella_config_service_factory()
                );
                $xoctPlayerGUI->streamVideo();
                break;
            default:
                switch ($cmd) {
                    case self::CMD_UPLOAD_CHUNKS:
                    case self::CMD_CREATE:
                    case self::CMD_CANCEL:
                        $return_link = filter_input(INPUT_GET, self::P_GET_RETURN_LINK, FILTER_SANITIZE_STRING);
                        $this->dic->ctrl()->setParameter($this, self::P_GET_RETURN_LINK, urlencode($return_link));
                        $this->{$cmd}();
                }
        }
    }

    protected function checkPlayerAccess(): bool
    {
        $token = filter_input(INPUT_GET, self::TOKEN, FILTER_SANITIZE_STRING);
        $event_id = filter_input(INPUT_GET, xoctPlayerGUI::IDENTIFIER, FILTER_SANITIZE_STRING);

        return (new TokenRepository())->checkToken($this->dic->user()->getId(), $event_id, $token);
    }

    protected function create(): void
    {
        $xoctUser = xoctUser::getInstance($this->dic->user());

        $form_action_by_class = $this->dic->ctrl()->getFormActionByClass(
            [ilObjPluginDispatchGUI::class, ocpcRouterGUI::class],
            self::CMD_CREATE
        );

        $with_terms_of_use = !ToUManager::hasAcceptedToU($this->dic->user()->getId());

        $form = $this->opencast_dic->event_form_builder()->upload(
            $form_action_by_class,
            $with_terms_of_use
        );

        $form = $form->withRequest($this->dic->http()->request());

        $data = $form->getData();

        if ($with_terms_of_use) {
            $eula_accepted = $data[EventFormBuilder::F_ACCEPT_EULA][EventFormBuilder::F_ACCEPT_EULA];
            if (!$eula_accepted) {
                // this is necessary because the 'required'-function of the checkbox doesn't work currently
                // otherwise, $data would just be null
                $this->main_tpl->setOnScreenMessage(
                    'failure', self::plugin()->getPluginObject()->txt('event_error_alert_accpet_terms_of_use')
                );
                $this->dic->mainTemplate()->loadStandardTemplate();
                $this->dic->mainTemplate()->setContent($this->dic->ui()->renderer()->render($form));
                $this->dic->mainTemplate()->printToStdout();
                return;
            } else {
                ToUManager::setToUAccepted($this->dic->user()->getId());
            }
        }

        if (!$data) {
            $this->dic->ui()->mainTemplate()->loadStandardTemplate();
            $this->dic->ui()->mainTemplate()->setContent($this->dic->ui()->renderer()->render($form));
            $this->dic->ui()->mainTemplate()->printToStdout();
            return;
        }

        $series_id = $data['file'][MDFieldDefinition::F_IS_PART_OF];
        if ($series_id === 'own_series') {
            $series_id = $this->series_repository->getOrCreateOwnSeries($xoctUser)->getIdentifier();
        }
        /** @var Metadata $metadata */
        $metadata = $data['metadata']['object'];
        $metadata->addField(
            (new MetadataField(MDFieldDefinition::F_IS_PART_OF, MDDataType::text()))
                ->withValue($series_id)
        );

        $this->event_repository->upload(
            new UploadEventRequest(
                new UploadEventRequestPayload(
                    $metadata,
                    $this->opencast_dic->acl_utils()->getBaseACLForUser(xoctUser::getInstance($this->dic->user())),
                    new Processing(
                        PluginConfig::getConfig(PluginConfig::F_WORKFLOW),
                        $this->getDefaultWorkflowParameters($data['workflow_configuration']['object'] ?? null)
                    ),
                    xoctUploadFile::getInstanceFromFileArray($data['file']['file'])
                )
            )
        );
        $this->opencast_dic->upload_storage_service()->delete($data['file']['file']['id']);

        $this->main_tpl->setOnScreenMessage('success', $this->plugin->txt('msg_created'), true);
        $this->cancel();
    }

    /**
     * Get the default workflow parameters to pass as processing object when uploading/creating an event.
     *
     *
     */
    protected function getDefaultWorkflowParameters(/*?\stdClass*/ $from_data = null): \stdClass
    {
        $workflow_parameter = new WorkflowParameter();
        $default_parameter = $from_data ?? new stdClass();
        foreach ($workflow_parameter::get() as $param) {
            $id = $param->getId();

            // Here we only get the admin default workflow parameters.
            $default_value = $param->getDefaultValueAdmin();

            if (!isset($from_data->$id) && $default_value == WorkflowParameter::VALUE_ALWAYS_ACTIVE) {
                $default_parameter->$id = "true";
            }
        }
        return $default_parameter;
    }

    /**
     *
     */
    protected function cancel()
    {
        $return_url = filter_input(INPUT_GET, self::P_GET_RETURN_LINK, FILTER_SANITIZE_STRING);
        $this->dic->ctrl()->redirectToURL(htmlspecialchars_decode($return_url));
    }

    public function txt(string $var): string
    {
        return ilOpenCastPlugin::getInstance()->txt('event_' . $var);
    }
}
