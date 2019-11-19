<?php

namespace srag\DIC\OpencastPageComponent\DIC\Implementation;

use Collator;
use ilAccessHandler;
use ilAppEventHandler;
use ilAsqFactory;
use ilAuthSession;
use ilBenchmark;
use ilBookingManagerService;
use ilBrowser;
use ilComponentLogger;
use ilConditionService;
use ilCtrl;
use ilCtrlStructureReader;
use ilDBInterface;
use ilErrorHandling;
use ilExerciseFactory;
use ilGlobalTemplateInterface;
use ilHelpGUI;
use ILIAS;
use ILIAS\DI\BackgroundTaskServices;
use ILIAS\DI\Container;
use ILIAS\DI\HTTPServices;
use ILIAS\DI\LoggingServices;
use ILIAS\DI\UIServices;
use ILIAS\Filesystem\Filesystems;
use ILIAS\FileUpload\FileUpload;
use ILIAS\GlobalScreen\Services as GlobalScreenService;
use ILIAS\Refinery\Factory as RefineryFactory;
use ilIniFile;
use ilLanguage;
use ilLearningHistoryService;
use ilLocatorGUI;
use ilLoggerFactory;
use ilMailMimeSenderFactory;
use ilMailMimeTransportFactory;
use ilMainMenuGUI;
use ilNavigationHistory;
use ilNewsService;
use ilObjectDataCache;
use ilObjectDefinition;
use ilObjectService;
use ilObjUser;
use ilPluginAdmin;
use ilRbacAdmin;
use ilRbacReview;
use ilRbacSystem;
use ilSetting;
use ilStyleDefinition;
use ilTabsGUI;
use ilTaskService;
use ilToolbarGUI;
use ilTree;
use ilUIService;
use Session;
use srag\DIC\OpencastPageComponent\DIC\AbstractDIC;

/**
 * Class ILIAS60DIC
 *
 * @package srag\DIC\OpencastPageComponent\DIC\Implementation
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
final class ILIAS60DIC extends AbstractDIC
{

    /**
     * @inheritdoc
     */
    public function access() : ilAccessHandler
    {
        return $this->dic->access();
    }


    /**
     * @inheritdoc
     */
    public function appEventHandler() : ilAppEventHandler
    {
        return $this->dic->event();
    }


    /**
     * @inheritdoc
     */
    public function authSession() : ilAuthSession
    {
        return $this->dic["ilAuthSession"];
    }


    /**
     * @inheritdoc
     */
    public function backgroundTasks() : BackgroundTaskServices
    {
        return $this->dic->backgroundTasks();
    }


    /**
     * @inheritdoc
     */
    public function benchmark() : ilBenchmark
    {
        return $this->dic["ilBench"];
    }


    /**
     * @inheritdoc
     */
    public function bookingManager() : ilBookingManagerService
    {
        return $this->dic->bookingManager();
    }


    /**
     * @inheritdoc
     */
    public function browser() : ilBrowser
    {
        return $this->dic["ilBrowser"];
    }


    /**
     * @inheritdoc
     */
    public function clientIni() : ilIniFile
    {
        return $this->dic->clientIni();
    }


    /**
     * @inheritdoc
     */
    public function collator() : Collator
    {
        return $this->dic["ilCollator"];
    }


    /**
     * @inheritdoc
     */
    public function conditions() : ilConditionService
    {
        return $this->dic->conditions();
    }


    /**
     * @inheritdoc
     */
    public function ctrl() : ilCtrl
    {
        return $this->dic->ctrl();
    }


    /**
     * @inheritdoc
     */
    public function ctrlStructureReader() : ilCtrlStructureReader
    {
        return $this->dic["ilCtrlStructureReader"];
    }


    /**
     * @inheritdoc
     */
    public function databaseCore() : ilDBInterface
    {
        return $this->dic->database();
    }


    /**
     * @inheritdoc
     */
    public function error() : ilErrorHandling
    {
        return $this->dic["ilErr"];
    }


    /**
     * @inheritdoc
     */
    public function exercise() : ilExerciseFactory
    {
        return $this->dic->exercise();
    }


    /**
     * @inheritdoc
     */
    public function filesystem() : Filesystems
    {
        return $this->dic->filesystem();
    }


    /**
     * @inheritdoc
     */
    public function globalScreen() : GlobalScreenService
    {
        return $this->dic->globalScreen();
    }


    /**
     * @inheritdoc
     */
    public function help() : ilHelpGUI
    {
        return $this->dic->help();
    }


    /**
     * @inheritdoc
     */
    public function history() : ilNavigationHistory
    {
        return $this->dic["ilNavigationHistory"];
    }


    /**
     * @inheritdoc
     */
    public function http() : HTTPServices
    {
        return $this->dic->http();
    }


    /**
     * @inheritdoc
     */
    public function ilias() : ILIAS
    {
        return $this->dic["ilias"];
    }


    /**
     * @inheritdoc
     */
    public function iliasIni() : ilIniFile
    {
        return $this->dic->iliasIni();
    }


    /**
     * @inheritdoc
     */
    public function language() : ilLanguage
    {
        return $this->dic->language();
    }


    /**
     * @inheritdoc
     */
    public function learningHistory() : ilLearningHistoryService
    {
        return $this->dic->learningHistory();
    }


    /**
     * @inheritdoc
     */
    public function locator() : ilLocatorGUI
    {
        return $this->dic["ilLocator"];
    }


    /**
     * @inheritdoc
     */
    public function log() : ilComponentLogger
    {
        return $this->dic["ilLog"];
    }


    /**
     * @inheritdoc
     */
    public function logger() : LoggingServices
    {
        return $this->dic->logger();
    }


    /**
     * @inheritdoc
     */
    public function loggerFactory() : ilLoggerFactory
    {
        return $this->dic["ilLoggerFactory"];
    }


    /**
     * @inheritdoc
     */
    public function mailMimeSenderFactory() : ilMailMimeSenderFactory
    {
        return $this->dic["mail.mime.sender.factory"];
    }


    /**
     * @inheritdoc
     */
    public function mailMimeTransportFactory() : ilMailMimeTransportFactory
    {
        return $this->dic["mail.mime.transport.factory"];
    }


    /**
     * @inheritdoc
     */
    public function mainMenu() : ilMainMenuGUI
    {
        return $this->dic["ilMainMenu"];
    }


    /**
     * @inheritdoc
     */
    public function mainTemplate() : ilGlobalTemplateInterface
    {
        return $this->dic->ui()->mainTemplate();
    }


    /**
     * @inheritdoc
     */
    public function news() : ilNewsService
    {
        return $this->dic->news();
    }


    /**
     * @inheritdoc
     */
    public function objDataCache() : ilObjectDataCache
    {
        return $this->dic["ilObjDataCache"];
    }


    /**
     * @inheritdoc
     */
    public function objDefinition() : ilObjectDefinition
    {
        return $this->dic["objDefinition"];
    }


    /**
     * @inheritdoc
     */
    public function object() : ilObjectService
    {
        return $this->dic->object();
    }


    /**
     * @inheritdoc
     */
    public function pluginAdmin() : ilPluginAdmin
    {
        return $this->dic["ilPluginAdmin"];
    }


    /**
     * @inheritdoc
     */
    public function question() : ilAsqFactory
    {
        return $this->dic->question();
    }


    /**
     * @inheritdoc
     */
    public function rbacadmin() : ilRbacAdmin
    {
        return $this->dic->rbac()->admin();
    }


    /**
     * @inheritdoc
     */
    public function rbacreview() : ilRbacReview
    {
        return $this->dic->rbac()->review();
    }


    /**
     * @inheritdoc
     */
    public function rbacsystem() : ilRbacSystem
    {
        return $this->dic->rbac()->system();
    }


    /**
     * @inheritdoc
     */
    public function refinery() : RefineryFactory
    {
        return $this->dic->refinery();
    }


    /**
     * @inheritdoc
     */
    public function session() : Session
    {
        return $this->dic["sess"];
    }


    /**
     * @inheritdoc
     */
    public function settings() : ilSetting
    {
        return $this->dic->settings();
    }


    /**
     * @inheritdoc
     */
    public function systemStyle() : ilStyleDefinition
    {
        return $this->dic->systemStyle();
    }


    /**
     * @inheritdoc
     */
    public function tabs() : ilTabsGUI
    {
        return $this->dic->tabs();
    }


    /**
     * @inheritdoc
     */
    public function task() : ilTaskService
    {
        return $this->dic->task();
    }


    /**
     * @inheritdoc
     */
    public function toolbar() : ilToolbarGUI
    {
        return $this->dic->toolbar();
    }


    /**
     * @inheritdoc
     */
    public function tree() : ilTree
    {
        return $this->dic->repositoryTree();
    }


    /**
     * @inheritdoc
     */
    public function ui() : UIServices
    {
        return $this->dic->ui();
    }


    /**
     * @inheritdoc
     */
    public function uiService() : ilUIService
    {
        return $this->dic->uiService();
    }


    /**
     * @inheritdoc
     */
    public function upload() : FileUpload
    {
        return $this->dic->upload();
    }


    /**
     * @inheritdoc
     */
    public function user() : ilObjUser
    {
        return $this->dic->user();
    }


    /**
     * @inheritDoc
     */
    public function &dic() : Container
    {
        return $this->dic;
    }
}
