<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/docs/LICENSE */

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
            $this->db->update(self::TABLE_NAME, ["value" => ["text", $config->getValue()]],
                ["name" => ["text", $config->getName()]]);
        } else {
            $this->db->insert(
                self::TABLE_NAME, ["name" => ["text", $config->getName()], "value" => ["text", $config->getValue()]]
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
