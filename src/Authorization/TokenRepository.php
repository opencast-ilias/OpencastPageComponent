<?php

namespace srag\Plugins\OpencastPageComponent\Authorization;

/**
 * Class TokenRepository
 *
 * @package srag\Plugins\OpencastPageComponent\Authorization
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class TokenRepository
{

    /**
     * validity in seconds
     */
    const TOKEN_VALIDITY = 3 * 60 * 60;


    /**
     * @param        $usr_id int
     *
     * @param string $event_id
     *
     * @return TokenAR
     */
    public function create(int $usr_id, string $event_id) : TokenAR
    {
        $token_AR = new TokenAR();
        $token_AR->setUsrId($usr_id);
        $token_AR->setEventId($event_id);
        $token_AR->setValidUntilUnix(time() + self::TOKEN_VALIDITY);
        $token_AR->setToken(new Token());
        $token_AR->create();

        return $token_AR;
    }


    /**
     * @param int    $usr_id
     * @param string $event_id
     * @param string $token
     *
     * @return bool
     */
    public function checkToken(int $usr_id, string $event_id, string $token) : bool
    {
        /** @var TokenAR $token_AR */
        $token_AR = TokenAR::where(['usr_id' => $usr_id, 'event_id' => $event_id, 'token' => $token])->first();
        $valid = !is_null($token_AR) && ($token_AR->getValidUntilUnix() >= time());
        $this->cleanUpTokens();
        return $valid;
    }


    /**
     *
     */
    public function cleanUpTokens()
    {
        /** @var TokenAR $token */
        foreach (TokenAR::where(['valid_until_unix' => time()], ['valid_until_unix' => '<'])->get() as $token) {
            $token->delete();
        }
    }
}