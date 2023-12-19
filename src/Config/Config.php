<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/docs/LICENSE */

namespace srag\Plugins\OpencastPageComponent\Config;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class Config
{

    public const TABLE_NAME = "copg_pgcp_ocpc_config";
    public const KEY_DEFAULT_WIDTH = "default_width";
    public const KEY_DEFAULT_HEIGHT = "default_height";
    public const KEY_DEFAULT_AS_LINK = "default_as_link";
    /**
     * @var string
     */
    private $name;
    /**
     * @var mixed
     */
    private $value;

    public function __construct(string $name, $value = null)
    {
        $this->name = $name;
        $this->value = $value;
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
    public static function setField(string $key, $value)
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
