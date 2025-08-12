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

use ILIAS\UI\Factory;
use srag\Plugins\Opencast\UI\Integration\Integration;
use ILIAS\UI\Component\Component;
use srag\Plugins\Opencast\Container\Container;
use srag\Plugins\Opencast\Model\Event\EventAPIRepository;
use srag\Plugins\OpencastPageComponent\Translator;
use Psr\Http\Message\RequestInterface;
use ILIAS\UI\Component\Input\Container\Form\Standard;
use ILIAS\Data\URI;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class Edit implements ViewElement
{
    public const RATIO_AS_PUBLICATION = -1;
    private Factory $ui_factory;
    private EventAPIRepository $event_repository;
    private array $ratio_option = [];

    public function __construct(
        private array $properties,
        private Container $container,
        private Translator $translator,
        private Integration $integration,
        private URI $post_uri,
        private URI $replace_uri
    ) {
        $this->ui_factory = $this->container->ilias()->ui()->factory();
        $this->event_repository = $this->container->get(EventAPIRepository::class);
        $this->ratio_option = [
            self::RATIO_AS_PUBLICATION => $this->translator->translate('ratio_as_publication'),
            (string) (16 / 9) => '16:9',
            (string) (4 / 3) => '4:3',
            (string) (1) => '1:1',
        ];
    }

    protected function getPropertiesForm(): Standard
    {
        $closest_ratio = $this->determineRatio();

        $ratio = $this->properties[\ilOpencastPageComponentPluginGUI::PROP_ASPECT_RATIO] ?? self::RATIO_AS_PUBLICATION;
        // check if $ratio is in $this->radio_options
        if (!array_key_exists($ratio, $this->ratio_option)) {
            $ratio = self::RATIO_AS_PUBLICATION;
        }

        return $this->ui_factory->input()->container()->form()->standard(
            (string) $this->post_uri,
            [
                \ilOpencastPageComponentPluginGUI::PROP_ASPECT_RATIO => $this->ui_factory
                    ->input()
                    ->field()
                    ->select(
                        $this->translator->translate('aspect_ratio'),
                        $this->ratio_option,
                        $this->translator->translate('aspect_ratio_info')
                    )
                    ->withValue((string) $closest_ratio)
                    ->withRequired(true),
                \ilOpencastPageComponentPluginGUI::PROP_WIDTH => $this->ui_factory
                    ->input()
                    ->field()
                    ->numeric(
                        $this->translator->translate('max_width'),
                        $this->translator->translate('max_width_info')
                    )
                    ->withValue($this->properties[\ilOpencastPageComponentPluginGUI::PROP_WIDTH] ?? null),
                \ilOpencastPageComponentPluginGUI::PROP_AS_LINK => $this->ui_factory
                    ->input()
                    ->field()
                    ->checkbox(
                        $this->translator->translate('link_thumbnail'),
                        $this->translator->translate('link_thumbnail_info')
                    )
                    ->withValue((bool) ($this->properties[\ilOpencastPageComponentPluginGUI::PROP_AS_LINK] ?? false)),
            ]
        );
    }

    public function get(): Component|array
    {
        $event_id = $this->properties[\ilOpencastPageComponentPluginGUI::PROP_EVENT_ID] ?? null;
        if ($event_id === null) {
            return [];
        }
        return [
            $this->integration->events()->asItemFromEventId(
                $event_id,
                $this->ui_factory->button()->standard(
                    $this->translator->translate('event_select_another'),
                    (string) $this->replace_uri,
                ),
                null,
                $this->translator->translate('event_currently_selected'),
            ),
            $this->ui_factory->panel()->standard(
                $this->translator->translate('event_properties'),
                $this->getPropertiesForm()
            )
        ];
    }

    public function getUpdatedProperties(RequestInterface $request): ?array
    {
        return $this->getPropertiesForm()->withRequest($request)->getData();
    }

    protected function determineRatio(): float
    {
        if (!empty($current = $this->properties[\ilOpencastPageComponentPluginGUI::PROP_ASPECT_RATIO] ?? null)) {
            return (float) $current;
        }

        $event = $this->event_repository->find($this->properties[\ilOpencastPageComponentPluginGUI::PROP_EVENT_ID]);
        $event->publications()->getThumbnailUrl();
        // try to determine aspect ratio of the thumbnail
        // get content of the thumbnail
        $thumbnail = file_get_contents($event->publications()->getThumbnailUrl());
        // get image size
        $size = getimagesizefromstring($thumbnail);
        // calculate aspect ratio
        $aspect_ratio = (float) ($size[0] / $size[1]);

        $ratios = array_map('floatval', array_keys($this->ratio_option));

        $closest_ratio = null;
        $closest_diff = PHP_FLOAT_MAX;

        foreach ($ratios as $ratio) {
            $diff = abs($ratio - $aspect_ratio);

            if ($diff < $closest_diff) {
                $closest_diff = $diff;
                $closest_ratio = $ratio;
            }
        }

        return $closest_ratio;
    }

}
