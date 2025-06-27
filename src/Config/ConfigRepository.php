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

namespace srag\Plugins\OpencastPageComponent\Config;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class ConfigRepository
{
    public const TABLE_NAME = "copg_pgcp_ocpc_config";
    /**
     * @var \ilDBInterface
     */
    private $db;

    public function __construct()
    {
        global $DIC;
        $this->db = $DIC->database();
    }

    public function has(string $key): bool
    {
        return $this->db->queryF("SELECT * FROM " . self::TABLE_NAME . " WHERE name = %s", ["text"], [$key])
                        ->numRows() > 0;
    }

    public function store(Config $config): Config
    {
        if ($this->has($config->getName())) {
            $this->db->update(
                self::TABLE_NAME,
                ["value" => ["text", $config->getValue()]],
                ["name" => ["text", $config->getName()]]
            );
        } else {
            $this->db->insert(
                self::TABLE_NAME,
                ["name" => ["text", $config->getName()], "value" => ["text", $config->getValue()]]
            );
        }
        return $config;
    }

    public function get(string $key, $default = null): Config
    {
        if ($this->has($key)) {
            $result = $this->db->queryF("SELECT * FROM " . self::TABLE_NAME . " WHERE name = %s", ["text"], [$key]);
            $row = $this->db->fetchAssoc($result);
            return new Config($row["name"], $row["value"]);
        }
        return new Config($key, $default);
    }

}
