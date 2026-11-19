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
    public const TOKEN_VALIDITY = 3 * 60 * 60;

    /**
     * A buffer after which the token remains valid in order to reuse it during this time.
     */
    public const TOKEN_BUFFER = 10;

    /**
     * A secondary use constant token.
     */
    public const SECONDARY_TOKEN = 'secondary_token';

    /**
     * @param        $usr_id int
     *
     *
     */
    public function create(int $usr_id, string $event_id): TokenAR
    {
        $token_AR = new TokenAR();
        $token_AR->setUsrId($usr_id);
        $token_AR->setEventId($event_id);
        $token_AR->setValidUntilUnix(time() + self::TOKEN_VALIDITY);
        $token_AR->setToken(new Token());
        $token_AR->create();

        return $token_AR;
    }

    public function checkToken(
        int $usr_id,
        string $event_id,
        string $token,
        bool $needs_secondary = false,
        ?int $custom_token_buffer = null
    ): bool {
        /** @var TokenAR $token_AR */
        $token_AR = TokenAR::where(['usr_id' => $usr_id, 'event_id' => $event_id, 'token' => $token])->first();
        $valid = !is_null($token_AR) && ($token_AR->getValidUntilUnix() >= time());
        if ($valid && $needs_secondary) {
            $secondary_token_buffer = self::TOKEN_VALIDITY;
            if ($custom_token_buffer && self::TOKEN_VALIDITY < $custom_token_buffer) {
                $secondary_token_buffer = $custom_token_buffer;
            }
            $secondary_token_buffer = $secondary_token_buffer + 5; // 5 seconds margin!
            $token_AR->setValidUntilUnix(time() + $secondary_token_buffer);
            $token_AR->setToken(new Token(self::SECONDARY_TOKEN));
            $token_AR->update();
        }
        $this->cleanUpTokens();
        return $valid;
    }

    public function cleanUpTokens(): void
    {
        /** @var TokenAR $token */
        foreach (TokenAR::where(['valid_until_unix' => time()], ['valid_until_unix' => '<'])->get() as $token) {
            $token->delete();
        }
    }
}
