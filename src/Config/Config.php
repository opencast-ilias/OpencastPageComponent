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
class Config
{
    public const TABLE_NAME = "copg_pgcp_ocpc_config";
    public const KEY_DEFAULT_WIDTH = "default_width";
    public const DEFAULT_WIDTH = 640;
    public const KEY_DEFAULT_HEIGHT = "default_height";
    public const DEFAULT_HEIGHT = 480;
    public const KEY_DEFAULT_AS_LINK = "default_as_link";

    public function __construct(private string $name, private mixed $value = null)
    {
    }

    /**
     * @deprecated This is only for legacy reasons. Do not use this method! Use the ConfigRepository directly
     */
    public static function getField(string $key)
    {
        $repo = new ConfigRepository();
        return $repo->get($key, null)->getValue();
    }

    /**
     * @deprecated This is only for legacy reasons. Do not use this method! Use the ConfigRepository directly
     */
    public static function setField(string $key, $value): void
    {
        $repo = new ConfigRepository();
        $config = $repo->get($key, null)->getValue();
        $config->setValue($value);
        $repo->store($config);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getValue()
    {
        return $this->value;
    }

}
