<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/docs/LICENSE */

require_once __DIR__ . "/../vendor/autoload.php";
require_once __DIR__ . '/../../../../Repository/RepositoryObject/OpenCast/vendor/autoload.php';

use srag\DIC\OpencastPageComponent\Util\LibraryLanguageInstaller;
use srag\RemovePluginDataConfirm\OpencastPageComponent\PluginUninstallTrait;

/**
 * Class ilOpencastPageComponentPlugin
 */
class ilOpencastPageComponentPlugin extends ilPageComponentPlugin
{
    public const PLUGIN_ID = "ocpc";
    public const PLUGIN_NAME = "OpencastPageComponent";

    public const REMOVE_PLUGIN_DATA_CONFIRM_CLASS_NAME = OpencastPageComponentRemoveDataConfirm::class;
    /**
     * @var ilOpencastPageComponentPlugin|null
     */
    private static $cache;

    /**
     * @var self|null
     */
    protected static $instance;

    public static function getInstance(): ilOpencastPageComponentPlugin
    {
        global $DIC;
        if (isset(self::$cache)) {
            return self::$cache;
        }

        // check if we are in ILIAS 8 context
        if (isset($DIC['component.factory'])) {
            /** @var ilComponentFactory $component_factory */
            $component_factory = $DIC['component.factory'];
            /** @var $plugin ilOpencastPageComponentPlugin */
            return self::$cache = $component_factory->getPlugin('ocpc');
        }
        // otherwise we are in ILIAS 7 context
        return self::$cache = new self();
    }

    public function getPluginName(): string
    {
        return self::PLUGIN_NAME;
    }

    public function isValidParentType(string $a_type): bool
    {
        // Allow in all parent types
        return true;
    }

    protected function afterUninstall(): void
    {
        $this->db->dropTable("copg_pgcp_ocpc_config", false);
    }
}
