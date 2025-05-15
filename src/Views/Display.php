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
use srag\Plugins\Opencast\Model\Event\Event;
use srag\Plugins\Opencast\Model\Config\PluginConfig;
use srag\Plugins\OpencastPageComponent\Authorization\TokenRepository;
use srag\Plugins\Opencast\Model\Publication\Config\PublicationUsage;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class Display implements ViewElement
{
    private const PLUGIN_DIRECTORY = './Customizing/global/plugins/Services/COPage/PageComponent/OpencastPageComponent';
    private Factory $ui_factory;
    private EventAPIRepository $event_repository;
    private ?Event $event = null;
    private array $ratio_option = [];
    private int $height = 1;
    private int $width = 1;
    private float $publication_ratio = 16 / 9;
    private ?string $error = null;

    public function __construct(
        private array $properties,
        private Container $container,
        private Translator $translator,
        private Integration $integration,
        private string $mode
    ) {
        $this->ui_factory = $this->container->ilias()->ui()->factory();
        $this->event_repository = $this->container->get(EventAPIRepository::class);
        $this->container->ilias()->ui()->mainTemplate()->addCss(
            self::PLUGIN_DIRECTORY . '/templates/css/presentation.css'
        );

        try {
            $this->event = $this->event_repository->find(
                $this->properties[\ilOpencastPageComponentPluginGUI::PROP_EVENT_ID]
            );

            $player_publication = $this->event->publications()->getPlayerPublication();
            $this->height = $player_publication->getHeight();
            $this->width = $player_publication->getWidth();
            $this->publication_ratio = (float) $this->width / $this->height;
        } catch (\Throwable $t) {
            $this->error = $t->getMessage();
        }
    }

    public function get(): Component|array
    {
        $as_link = (bool) ($this->properties[\ilOpencastPageComponentPluginGUI::PROP_AS_LINK] ?? false);
        $max_width = (string) ($this->properties[\ilOpencastPageComponentPluginGUI::PROP_WIDTH] ?? '');
        $max_width = $max_width !== '' ? $max_width . 'px' : 'auto';

        $configured_aspect_ratio = (float) ($this->properties[\ilOpencastPageComponentPluginGUI::PROP_ASPECT_RATIO] ?? Edit::RATIO_AS_PUBLICATION);
        $ratio = $configured_aspect_ratio === Edit::RATIO_AS_PUBLICATION
            ? $this->publication_ratio
            : $configured_aspect_ratio;

        $tpl = new \ilTemplate(self::PLUGIN_DIRECTORY . '/templates/html/tpl.container.html', true, true);
        $tpl->setVariable('MAX_WIDTH', $max_width);
        $tpl->setVariable('RATIO', $ratio);

        $content = match (true) {
            $this->event === null => $this->getExceptionHTML($this->properties),
            $as_link || $this->mode !== \ilOpencastPageComponentPluginGUI::MODE_PRESENTATION => $this->getStandardElementHTML(
                $this->mode,
                $this->properties,
                $this->event
            ),
            default => $this->getIframeHTML($this->properties, $this->event),
        };

        $tpl->setVariable('CONTENT', $content);

        return $this->ui_factory->legacy(
            $tpl->get()
        );

    }

    // Moved from old class

    protected function getIframeHTML(array $properties, Event $event): string
    {
        $tpl = new \ilTemplate(self::PLUGIN_DIRECTORY . '/templates/html/component_as_iframe.html', true, true);
        $this->container->ilias()->ui()->mainTemplate()->addCss(
            self::PLUGIN_DIRECTORY . '/templates/css/presentation.css'
        );
        $tpl->setVariable('SRC', $this->getPlayerLink($event));

        return $tpl->get();
    }

    protected function getStandardElementHTML(string $mode, array $properties, Event $event): string
    {
        $renderer = new \xoctEventRenderer($event);
        $use_modal = (PluginConfig::getConfig(PluginConfig::F_USE_MODALS));
        $tpl = new \ilTemplate(self::PLUGIN_DIRECTORY . '/templates/html/component_as_link.html', true, true);
        $tpl->setVariable('THUMBNAIL_URL', $event->publications()->getThumbnailUrl());

        if ($mode === \ilOpencastPageComponentPluginGUI::MODE_PRESENTATION || $mode === \ilOpencastPageComponentPluginGUI::MODE_PREVIEW) {
            $tpl->setVariable('TARGET', '_blank');
            $tpl->setVariable('VIDEO_LINK', $use_modal ? '#' : $this->getPlayerLink($event));
            $tpl->touchBlock('overlay');
            if ($use_modal) {
                $tpl->setVariable('MODAL', $renderer->getPlayerModal()->getHTML());
                $tpl->setVariable('MODAL_LINK', $renderer->getModalLink());
            }
        } else {
            $tpl->setVariable('VIDEO_LINK', '#');
        }

        return $tpl->get();
    }

    protected function getExceptionHTML(array $properties): string
    {
        return '<span>' . $this->error . '</span>';
    }

    protected function getPlayerLink(Event $event): string
    {
        if (PluginConfig::getConfig(PluginConfig::F_INTERNAL_VIDEO_PLAYER) || $event->isLiveEvent()) {
            $token = (new TokenRepository())->create(
                $this->container->ilias()->user()->getId(),
                $event->getIdentifier()
            );
            $this->container->ilias()->ctrl()->clearParametersByClass(\xoctPlayerGUI::class);
            $this->container->ilias()->ctrl()->setParameterByClass(
                \ocpcRouterGUI::class,
                \ocpcRouterGUI::TOKEN,
                $token->getToken()->toString()
            );
            $this->container->ilias()->ctrl()->setParameterByClass(
                \ocpcRouterGUI::class,
                \xoctPlayerGUI::IDENTIFIER,
                $event->getIdentifier()
            );
            $this->container->ilias()->ctrl()->setParameterByClass(
                \xoctPlayerGUI::class,
                \xoctPlayerGUI::IDENTIFIER,
                $event->getIdentifier()
            );
            return $this->container->ilias()->ctrl()->getLinkTargetByClass(
                [\ilObjPluginDispatchGUI::class, \ocpcRouterGUI::class, \xoctPlayerGUI::class],
                \xoctPlayerGUI::CMD_STREAM_VIDEO
            );
        }

        $url = $event->publications()->getFirstPublicationMetadataForUsage(
            PublicationUsage::find(PublicationUsage::USAGE_PLAYER)
        )->getUrl();
        if (PluginConfig::getConfig(PluginConfig::F_SIGN_PLAYER_LINKS)) {
            return \xoctSecureLink::signPlayer($url);
        }

        return $url;
    }

}
