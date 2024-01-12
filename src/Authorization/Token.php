<?php

namespace srag\Plugins\OpencastPageComponent\Authorization;

/**
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class Token
{

    /**
     * @var string
     */
    protected $token;

    public function __construct(string $token = '')
    {
        if ($token === '') {
            $token = openssl_random_pseudo_bytes(16);
            $token = bin2hex($token);
        }
        $this->token = $token;
    }

    public function toString(): string
    {
        return $this->token;
    }
}
