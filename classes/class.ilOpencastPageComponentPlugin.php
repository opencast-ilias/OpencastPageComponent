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

/**
 * Class ilOpencastPageComponentPlugin
 */
class ilOpencastPageComponentPlugin extends ilPageComponentPlugin
{
    public const PLUGIN_ID = "ocpc";
    public const PLUGIN_NAME = "OpencastPageComponent";

    public const REMOVE_PLUGIN_DATA_CONFIRM_CLASS_NAME = OpencastPageComponentRemoveDataConfirm::class;
    protected const MAIN_PLUGIN_VERSION_NEEDED = '9.2.0';
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

    public function __construct(ilDBInterface $db, ilComponentRepositoryWrite $component_repository, string $id)
    {
        parent::__construct($db, $component_repository, $id);
    }

    protected function beforeActivation(): bool
    {
        global $DIC;
        // check if main plugin available and active
        // additional version check for compatibility
        if (!isset($DIC['component.factory'])) {
            return false;
        }

        /** @var ilComponentFactory $component_factory */
        $component_factory = $DIC['component.factory'];
        /** @var $main_plugin ilOpencastPageComponentPlugin */
        try {
            $main_plugin = $component_factory->getPlugin('xoct');
        } catch (Throwable) {
            return false;
        }

        if (!$main_plugin->isActive() || !version_compare(
            $main_plugin->getVersion(),
            self::MAIN_PLUGIN_VERSION_NEEDED,
            ">="
        )) {
            throw new ilPluginException(
                'Please update and activate the OpenCast main plugin to version ' . self::MAIN_PLUGIN_VERSION_NEEDED . ' or higher.'
            );
        }

        return parent::beforeActivation();
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
