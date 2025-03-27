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
 */

declare(strict_types=1);

namespace srag\Plugins\OpencastPageComponent;

use srag\Plugins\Opencast\Util\Locale\Translator as MainTranslator;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class Translator
{
    public function __construct(
        private \ilOpencastPageComponentPlugin $plugin,
        private MainTranslator $main_translator
    ) {
    }

    public function translate(string $key): string
    {
        if ($this->main_translator->has($key)) {
            return $this->main_translator->translate($key);
        }
        return $this->plugin->txt($key);
    }

}
