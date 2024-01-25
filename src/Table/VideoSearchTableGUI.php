<?php

use ILIAS\DI\Container;
use srag\CustomInputGUIs\OpencastPageComponent\TableGUI\TableGUI;
use srag\Plugins\Opencast\Model\Config\PluginConfig;
use srag\Plugins\Opencast\Model\Event\Event;
use srag\Plugins\Opencast\Model\Event\EventAPIRepository;
use srag\Plugins\Opencast\Model\Metadata\Definition\MDFieldDefinition;
use srag\Plugins\Opencast\Model\Series\SeriesRepository;
use srag\Plugins\Opencast\Model\Series\SeriesAPIRepository;
use srag\Plugins\Opencast\Model\User\xoctUser;
use srag\Plugins\Opencast\DI\OpencastDIC;

/**
 * Class VideoSearchTableGUI
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
class VideoSearchTableGUI extends ilTable2GUI
{
    public const PLUGIN_CLASS_NAME = ilOpencastPageComponentPlugin::class;
    public const GET_PARAM_EVENT_ID = 'event_id';
    public const ID_PREFIX = 'oc_pc_';
    public const ROW_TEMPLATE = '/templates/html/table_row.html';
    public const F_TEXTFILTER = 'textFilter';
    public const F_SERIES = 'series';
    public const F_START_FROM = 'start_from';
    public const F_START_TO = 'start_to';
    public const F_START = 'start';
    /**
     * @var array
     */
    protected $filter_fields = [];
    /**
     * @var \ilOpencastPageComponentPlugin
     */
    protected $plugin;
    /**
     * @var Container
     */
    protected $dic;
    /**
     * @var string
     */
    protected $command_url;
    /**
     * @var array
     */
    protected $filter = [];
    /**
     * @var ilOpenCastPlugin
     */
    protected $opencast_plugin;
    /**
     * @var EventAPIRepository
     */
    protected $event_repository;
    /**
     * @var SeriesRepository
     */
    private $series_repository;

    public function __construct(
        $parent_gui,
        string $parent_cmd,
        Container $dic,
        string $command_url
    ) {
        global $opencastContainer;
        $this->dic = $dic;
        $this->plugin = ilOpencastPageComponentPlugin::getInstance();
        $this->command_url = $command_url;
        $this->opencast_plugin = ilOpenCastPlugin::getInstance();
        $opencast_dic = OpencastDIC::getInstance();

        if (method_exists($opencast_dic, 'event_repository')) {
            $this->event_repository = $opencast_dic->event_repository();
        } elseif (!empty($opencastContainer)) {
            $this->event_repository = $opencastContainer[EventAPIRepository::class];
        }

        if (method_exists($opencast_dic, 'series_repository')) {
            $this->series_repository = $opencast_dic->series_repository();
        } elseif (!empty($opencastContainer)) {
            $this->series_repository = $opencastContainer->get(SeriesAPIRepository::class);
        }

        parent::__construct($parent_gui, $parent_cmd);
        $this->initId();    // this is necessary so the offset and order can be determined
        $this->initTitle();
        $this->initColumns();
        $this->setExternalSegmentation(true);
        $this->setExternalSorting(true);
        $this->determineOffsetAndOrder();
        $this->setLimit(10);
        $this->setRowTemplate($this->plugin->getDirectory() . '/templates/html/table_row.html');
        $this->dic->ui()->mainTemplate()->addCss($this->plugin->getDirectory() . '/templates/css/table.css');
        $this->dic->ui()->mainTemplate()->addJavaScript($this->plugin->getDirectory() . '/templates/js/table.js');
        $this->setEnableNumInfo(false);
        $this->setShowRowsSelector(false);
        $this->setFormAction($this->dic->ctrl()->getFormAction($this->parent_obj));
        $this->initFilter2();
    }

    protected function initColumns(): void
    {
        foreach ($this->getSelectableColumns() as $column) {
            if ($this->isColumnSelected($column["id"])) {
                $this->addColumn($column["txt"], ($column["sort"] ?? $column["id"] ?? ''));
            }
        }
    }

    #[ReturnTypeWillChange]
    protected function fillRow($a_set): void
    {
        /** @var Event $object */
        $object = $a_set['object'];
        if ($object->getProcessingState() == Event::STATE_SUCCEEDED) {
            $this->tpl->setVariable('ADDITIONAL_CSS_CLASSES', 'ocpc_table_row_selectable');
        }

        $this->tpl->setVariable(
            'CMD_URL', $this->command_url . '&' . self::GET_PARAM_EVENT_ID . '=' . $a_set['identifier']
        );

        $this->tpl->setCurrentBlock("column");

        foreach ($this->getSelectableColumns() as $column) {
            if ($this->isColumnSelected($column["id"])) {
                $column = $this->getColumnValue($column["id"], $a_set);

                if (!empty($column)) {
                    $this->tpl->setVariable("COLUMN", $column);
                } else {
                    $this->tpl->setVariable("COLUMN", " ");
                }

                $this->tpl->parseCurrentBlock();
            }
        }
    }

    #[ReturnTypeWillChange]
    public function getSelectableColumns(): array
    {
        return [
            'thumbnail' => [
                'txt' => $this->opencast_plugin->txt('event_preview'),
                'id' => 'thumbnail',
                'default' => true
            ],
            'title' => ['txt' => $this->opencast_plugin->txt('event_title'), 'id' => 'title', 'default' => true],
            'description' => [
                'txt' => $this->plugin->txt('event_description'),
                'id' => 'description',
                'default' => true
            ],
            'series' => ['txt' => $this->opencast_plugin->txt('event_series'), 'id' => 'series', 'default' => true],
            'start' => ['txt' => $this->plugin->txt('event_start'), 'id' => 'start', 'default' => true],
            'location' => [
                'txt' => $this->opencast_plugin->txt('event_location'),
                'id' => 'location',
                'default' => true
            ],
        ];
    }

    protected function getColumnValue(string $column, array $row, int $format = null): string
    {
        switch ($column) {
            case 'thumbnail':
                /** @var Event $object */
                $object = $row['object'];

                return '<img height="107.5px" width="200px" src="' . $object->publications()->getThumbnailUrl() . '">';
            case 'title':
                /** @var Event $object */
                $object = $row['object'];
                $renderer = new xoctEventRenderer($object);
                return $row['title'] . $renderer->getStateHTML();
            case 'description':
                return $row['description'];
            case 'series':
                /** @var Event $object */
                $object = $row['object'];
                $series_object = $this->series_repository->find($object->getSeries());
                return $series_object->getMetadata()->getField(MDFieldDefinition::F_TITLE)->getValue()
                    . ' (...' . substr($series_object->getIdentifier(), -4, 4) . ')';
            case 'start':
                if (!isset($row['startDate'])) {
                    return '-';
                }
                $startDate = $row['startDate'];
                $strtotime = strtotime($startDate);
                return date('d.m.Y H:i', $strtotime);
            case 'location':
                return $row['location'];
        }
        return '';
    }

    #[ReturnTypeWillChange]
    protected function getSelectableColumns2(): array
    {
        return [
            'thumbnail' => [
                'txt' => $this->opencast_plugin->txt('event_preview'),
                'id' => 'thumbnail',
                'default' => true
            ],
            'title' => ['txt' => $this->opencast_plugin->txt('event_title'), 'id' => 'title', 'default' => true],
            'description' => [
                'txt' => $this->opencast_plugin->txt('event_description'),
                'id' => 'description',
                'default' => true
            ],
            'series' => ['txt' => $this->opencast_plugin->txt('event_series'), 'id' => 'series', 'default' => true],
            'start' => ['txt' => $this->opencast_plugin->txt('event_start'), 'id' => 'start', 'default' => true],
            'location' => [
                'txt' => $this->opencast_plugin->txt('event_location'),
                'id' => 'location',
                'default' => true
            ],
        ];
    }

    public function initializeData(): void
    {
        // the api doesn't deliver a max count, so we fetch (limit + 1) to see if there should be a 'next' page
        try {
            $common_idp = true;
            PluginConfig::getConfig(PluginConfig::F_COMMON_IDP);
            $xoct_user = xoctUser::getInstance($this->dic->user());
            $identifier = $xoct_user->getIdentifier();
            if ($identifier === '') {
                throw new xoctException(xoctException::NO_USER_MAPPING);
            }

            $events = (array) $this->event_repository->getFiltered(
                $this->buildFilterArray(),
                $common_idp ? '' : $xoct_user->getIdentifier(),
                $common_idp ? [$xoct_user->getUserRoleName()] : [],
                $this->getOffset(),
                $this->getLimit() + 1,
            );
        } catch (xoctException $e) {
            xoctLog::getInstance()->write($e->getMessage());
            $events = [];
            if ($e->getCode() !== xoctException::API_CALL_STATUS_403) {
                $this->dic->ui()->mainTemplate()->setOnScreenMessage(
                    'failure',
                    sprintf($this->plugin->txt('msg_failed_loading_events'), $e->getMessage())
                );
            }
        }
        $this->setMaxCount($this->getOffset() + count($events));
        if (count($events) === ($this->getLimit() + 1)) {
            array_pop($events);
        }
        $this->setData($events);
    }

    /**
     * @return array{status: string, textFilter?: mixed, series?: mixed, start?: string}
     */
    protected function buildFilterArray(): array
    {
        $filter = [];
        $filter['status'] = 'EVENTS.EVENTS.STATUS.PROCESSED';

        $title_filter = $this->filter[self::F_TEXTFILTER];

        if ($title_filter !== null) {
            $filter[self::F_TEXTFILTER] = $title_filter;
        }
        $series_filter = $this->filter[self::F_SERIES] ?? null;
        if ($series_filter !== null) {
            $filter[self::F_SERIES] = $series_filter;
        }

        /** @var ilDateTime|null $start_filter_from */
        /** @var ilDateTime|null $start_filter_to */
        $start_filter_from = $this->filter[self::F_START_FROM] ?? false;
        $start_filter_to = $this->filter[self::F_START_TO] ?? false;
        if ($start_filter_from || $start_filter_to) {
            $filter['start'] = ($start_filter_from ? $start_filter_from->get(
                    IL_CAL_FKT_DATE, 'Y-m-d\TH:i:s'
                ) : '1970-01-01T00:00:00')
                . '/' . ($start_filter_to ? $start_filter_to->get(
                    IL_CAL_FKT_DATE, 'Y-m-d\T23:59:59'
                ) : '2200-01-01T00:00:00');
        }

        return $filter;
    }

    #[ReturnTypeWillChange]
    private function initFilter2(): void // ilTable2GUI has it's own initFilter method final
    {
        $this->setFilterCommand(ilOpencastPageComponentPluginGUI::CMD_APPLY_FILTER);
        $this->setDefaultFilterVisiblity(true);
        $this->setDisableFilterHiding(true);

        $this->initFilterFields();

        if (!is_array($this->filter_fields)) {
            throw new TableGUIException("\$filters needs to be an array!", TableGUIException::CODE_INVALID_FIELD);
        }

        foreach ($this->filter_fields as $key => $field) {
            if (!is_array($field)) {
                throw new TableGUIException("\$field needs to be an array!", TableGUIException::CODE_INVALID_FIELD);
            }

            if ($field[PropertyFormGUI::PROPERTY_NOT_ADD]) {
                continue;
            }

            $item = Items::getItem($key, $field, $this, $this);

            /*if (!($item instanceof ilTableFilterItem)) {
                throw new TableGUIException("\$item must be an instance of ilTableFilterItem!", TableGUIException::CODE_INVALID_FIELD);
            }*/

            $this->filter_cache[$key] = $item;

            $this->addFilterItem($item);

            if ($this->hasSessionValue($item->getFieldId())) { // Supports filter default values
                $item->readFromSession();
            }
        }
    }

    protected function initFilterFields(): void
    {
        $title = $this->addFilterItemByMetaType(
            self::F_TEXTFILTER, self::FILTER_TEXT, false, $this->plugin->txt(self::F_TEXTFILTER)
        );
        $this->filter[self::F_TEXTFILTER] = $title->getValue();

        $series = $this->addFilterItemByMetaType(
            self::F_SERIES, self::FILTER_SELECT, false, $this->opencast_plugin->txt('event_series')
        );
        try {
            $series->setOptions($this->getSeriesFilterOptions());
        } catch (xoctException $e) {
            $this->main_tpl->setOnScreenMessage('failure', $e->getMessage());
        }
        $this->filter[self::F_SERIES] = $series->getValue();

        $range = new ilDateDurationInputGUI($this->opencast_plugin->txt('event_start'), 'date_range');
        $range->setAllowOpenIntervals(true);
        $range->setStartText('');
        $range->setEndText('');
        $this->addFilterItem($range, false);
        $range->readFromSession();
        try {
            $range = $range->getValue();
        } catch (Throwable $e) {
            $range = [];
        }

        $start = $range['start'] ?? null;
        $this->filter[self::F_START_FROM] = $start === null ? null : new ilDateTime($start, IL_CAL_UNIX);
        $end = $range['end'] ?? null;
        $this->filter[self::F_START_TO] = $end === null ? null : new ilDateTime($end, IL_CAL_UNIX);
    }

    protected function initId(): void
    {
        $this->setId(self::ID_PREFIX . $this->dic->user()->getId());
    }

    protected function initTitle(): void
    {
        $this->setTitle($this->plugin->txt('table_title'));
    }

    protected function initAction(): void
    {
        $this->setFormAction($this->dic->ctrl()->getFormAction($this->parent_obj));
    }

    /**
     * @return array<string, string>
     */
    protected function getSeriesFilterOptions(): array
    {
        $series_options = ['' => '-'];
        $xoctUser = xoctUser::getInstance($this->dic->user());
        $this->series_repository->getOwnSeries($xoctUser);
        if (xoctUser::getInstance($this->dic->user())->getIdentifier() == '') {
            throw new xoctException(xoctException::NO_USER_MAPPING);
        }
        foreach ($this->series_repository->getAllForUser($xoctUser->getUserRoleName()) as $series) {
            $series_options[$series->getIdentifier()] =
                $series->getMetadata()->getField(MDFieldDefinition::F_TITLE)->getValue()
                . ' (...' . substr($series->getIdentifier(), -4, 4) . ')';
        }

        natcasesort($series_options);

        return $series_options;
    }

}
