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

namespace srag\Plugins\OpencastPageComponent\Views;

use ILIAS\UI\Factory;
use srag\Plugins\Opencast\UI\Integration\Integration;
use ILIAS\UI\Component\Component;
use srag\Plugins\Opencast\Container\Container;
use srag\Plugins\Opencast\Model\Event\EventAPIRepository;
use srag\Plugins\OpencastPageComponent\Translator;
use ILIAS\Data\URI;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class InsertOrReplace implements ViewElement
{
    public const PROP_EVENT_ID = 'event_id';
    private Factory $ui_factory;
    private EventAPIRepository $event_repository;

    public function __construct(
        private Container $container,
        private Translator $translator,
        private Integration $integration,
        private URI $current_url,
        private URI $target_url
    ) {
        $this->ui_factory = $this->container->ilias()->ui()->factory();
    }

    public function get(): Component|array
    {
        return $this->ui_factory->panel()->standard(
            $this->translator->translate('table_title'),
            $this->integration->mine()->asDataTableWithFilters(
                $this->current_url,
                $this->target_url,
                self::PROP_EVENT_ID
            )
        );
    }

}
