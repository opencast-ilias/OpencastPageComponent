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

declare(strict_types=1);

namespace srag\Plugins\OpencastPageComponent\Views;

use srag\Plugins\Opencast\Model\Event\EventRepository;
use srag\Plugins\Opencast\Model\Event\Event;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class EventDimensions
{

    protected array $ratio_cache = [];

    public function __construct(
        private EventRepository $repository
    ) {
    }

    public function determineForEventId(string $event_id): ?Dimensions
    {
        if (isset($this->ratio_cache[$event_id])) {
            return $this->ratio_cache[$event_id];
        }
        $event = $this->repository->find($event_id);
        return $this->determineForEvent($event);
    }

    public function determineForEvent(Event $event): ?Dimensions
    {
        if (isset($this->ratio_cache[$event->getIdentifier()])) {
            return $this->ratio_cache[$event->getIdentifier()];
        }

        try {
            $player_publication = $event->publications()->getPlayerPublication();
            if ($player_publication === null) {
                return $this->ratio_cache[$event->getIdentifier()] = null;
            }
            $height = $player_publication->getHeight();
            $width = $player_publication->getWidth();

            return $this->ratio_cache[$event->getIdentifier()] = new Dimensions(
                $width,
                $height
            );
        } catch (\Throwable $t) {
            return $this->ratio_cache[$event->getIdentifier()] = null;
        }
    }

}
