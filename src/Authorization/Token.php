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
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class Token
{
    protected string $token;

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
