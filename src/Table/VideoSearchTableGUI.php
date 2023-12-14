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
class VideoSearchTableGUI extends TableGUI
{

    const PLUGIN_CLASS_NAME = ilOpencastPageComponentPlugin::class;
    const GET_PARAM_EVENT_ID = 'event_id';
    const ID_PREFIX = 'oc_pc_';
    const ROW_TEMPLATE = '/templates/html/table_row.html';
    const F_TEXTFILTER = 'textFilter';
    const F_SERIES = 'series';
    const F_START_FROM = 'start_from';
    const F_START_TO = 'start_to';
    const F_START = 'start';
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


    public function __construct($parent_gui,
                                string $parent_cmd,
                                Container $dic,
                                string $command_url)
    {
        global $opencastContainer;
        $this->dic = $dic;
        $this->command_url = $command_url;
        $this->opencast_plugin = ilOpenCastPlugin::getInstance();
        $opencast_dic = OpencastDIC::getInstance();

        if (method_exists($opencast_dic, 'event_repository')) {
            $this->event_repository = $opencast_dic->event_repository();
        } else if (!empty($opencastContainer)) {
            $this->event_repository = $opencastContainer[EventAPIRepository::class];
        }

        if (method_exists($opencast_dic, 'series_repository')) {
            $this->series_repository = $opencast_dic->series_repository();
        } else if (!empty($opencastContainer)) {
            $this->series_repository = $opencastContainer->get(SeriesAPIRepository::class);
        }

        $this->initId();    // this is necessary so the offset and order can be determined
        $this->setExternalSegmentation(true);
        $this->setExternalSorting(true);
        $this->determineOffsetAndOrder();
        parent::__construct($parent_gui, $parent_cmd);
        $this->setLimit(10);
        $this->setRowTemplate(self::plugin()->directory() . '/templates/html/table_row.html');
        $this->dic->ui()->mainTemplate()->addCss(self::plugin()->directory() . '/templates/css/table.css');
        $this->dic->ui()->mainTemplate()->addJavaScript(self::plugin()->directory() . '/templates/js/table.js');
        $this->setEnableNumInfo(false);
        $this->setShowRowsSelector(false);
    }


    /**
     * @param array|object $row
     *
     * @throws xoctException
     */
    protected function fillRow(/*array*/ $row)/*: void*/
    {
        /** @var Event $object */
        $object = $row['object'];
        if ($object->getProcessingState() == Event::STATE_SUCCEEDED) {
            $this->tpl->setVariable('ADDITIONAL_CSS_CLASSES', 'ocpc_table_row_selectable');
        }

        $this->tpl->setCurrentBlock("column");

        $this->tpl->setVariable('CMD_URL', $this->command_url . '&' . self::GET_PARAM_EVENT_ID . '=' . $row['identifier']);

        foreach ($this->getSelectableColumns() as $column) {
            if ($this->isColumnSelected($column["id"])) {
                $column = $this->getColumnValue($column["id"], $row);

                if (!empty($column)) {
                    $this->tpl->setVariable("COLUMN", $column);
                } else {
                    $this->tpl->setVariable("COLUMN", " ");
                }

                $this->tpl->parseCurrentBlock();
            }
        }
    }


    /**
     * @param       $column
     * @param array $row
     * @param int $format
     *
     * @return false|mixed|string
     * @throws xoctException
     * @inheritDoc
     */
    protected function getColumnValue(string $column, /*array*/ $row, int $format = self::DEFAULT_FORMAT): string
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
                $series_title = $series_object->getMetadata()->getField(MDFieldDefinition::F_TITLE)->getValue()
                    . ' (...' . substr($series_object->getIdentifier(), -4, 4) . ')';
                return $series_title;
            case 'start':
                if (!isset($row['startDate'])) {
                    return '-';
                }
                $startDate = $row['startDate'];
                $strtotime = strtotime($startDate);
                $date = date('d.m.Y H:i', $strtotime);
                return $date;
            case 'location':
                return $row['location'];
        }
    }


    /**
     * @inheritDoc
     */
    protected function getSelectableColumns2(): array
    {
        return [
            'thumbnail' => ['txt' => $this->opencast_plugin->txt('event_preview'), 'id' => 'thumbnail', 'default' => true],
            'title' => ['txt' => $this->opencast_plugin->txt('event_title'), 'id' => 'title', 'default' => true],
            'description' => ['txt' => $this->opencast_plugin->txt('event_description'), 'id' => 'description', 'default' => true],
            'series' => ['txt' => $this->opencast_plugin->txt('event_series'), 'id' => 'series', 'default' => true],
            'start' => ['txt' => $this->opencast_plugin->txt('event_start'), 'id' => 'start', 'default' => true],
            'location' => ['txt' => $this->opencast_plugin->txt('event_location'), 'id' => 'location', 'default' => true],
        ];
    }


    /**
     * @inheritDoc
     */
    public function initializeData()
    {
        // the api doesn't deliver a max count, so we fetch (limit + 1) to see if there should be a 'next' page
        try {
            $common_idp = PluginConfig::getConfig(PluginConfig::F_COMMON_IDP);
            if (xoctUser::getInstance($this->dic->user())->getIdentifier() == '') {
                throw new xoctException(xoctException::NO_USER_MAPPING);
            }
            $events = (array)$this->event_repository->getFiltered(
                $this->buildFilterArray(),
                $common_idp ? '' : xoctUser::getInstance($this->dic->user())->getIdentifier(),
                $common_idp ? [xoctUser::getInstance($this->dic->user())->getUserRoleName()] : [],
                $this->getOffset(),
                $this->getLimit() + 1
            );
        } catch (xoctException $e) {
            xoctLog::getInstance()->write($e->getMessage());
            $events = [];
            if ($e->getCode() !== xoctException::API_CALL_STATUS_403) {
                ilUtil::sendFailure(self::plugin()->translate('failed_loading_events', 'msg', [$e->getMessage()]));
            }
        }
        $this->setMaxCount($this->getOffset() + count($events));
        if (count($events) == ($this->getLimit() + 1)) {
            array_pop($events);
        }
        $this->setData($events);
    }


    /**
     * @return array
     */
    protected function buildFilterArray(): array
    {
        $filter = ['status' => 'EVENTS.EVENTS.STATUS.PROCESSED'];

        if ($title_filter = $this->filter[self::F_TEXTFILTER]) {
            $filter[self::F_TEXTFILTER] = $title_filter;
        }

        if ($series_filter = $this->filter[self::F_SERIES]) {
            $filter[self::F_SERIES] = $series_filter;
        }

        /** @var $start_filter_from ilDateTime */
        /** @var $start_filter_to ilDateTime */
        $start_filter_from = $this->filter[self::F_START_FROM];
        $start_filter_to = $this->filter[self::F_START_TO];
        if ($start_filter_from || $start_filter_to) {
            $filter['start'] = ($start_filter_from ? $start_filter_from->get(IL_CAL_FKT_DATE, 'Y-m-d\TH:i:s') : '1970-01-01T00:00:00')
                . '/' . ($start_filter_to ? $start_filter_to->get(IL_CAL_FKT_DATE, 'Y-m-d\T23:59:59') : '2200-01-01T00:00:00');
        }

        return $filter;
    }


    /**
     * @inheritDoc
     */
    protected function initFilterFields()
    {
        $title = $this->addFilterItemByMetaType(self::F_TEXTFILTER, self::FILTER_TEXT, false, self::plugin()->translate(self::F_TEXTFILTER));
        $this->filter[self::F_TEXTFILTER] = $title->getValue();

        $series = $this->addFilterItemByMetaType(self::F_SERIES, self::FILTER_SELECT, false, $this->opencast_plugin->txt('event_series'));
        try {
            $series->setOptions($this->getSeriesFilterOptions());
        } catch (xoctException $e) {
            ilUtil::sendFailure($e->getMessage());
        }
        $this->filter[self::F_SERIES] = $series->getValue();

        $start = $this->addFilterItemByMetaType(self::F_START, self::FILTER_DATE_RANGE, false, $this->opencast_plugin->txt('event_start'));
        $this->filter[self::F_START_FROM] = $start->getValue()['from'];
        $this->filter[self::F_START_TO] = $start->getValue()['to'];
    }


    /**
     * @inheritDoc
     */
    protected function initId()
    {
        $this->setId(self::ID_PREFIX . $this->dic->user()->getId());
    }


    /**
     * @inheritDoc
     */
    protected function initTitle()
    {
        $this->setTitle(self::plugin()->translate('table_title'));
    }


    /**
     *
     */
    protected function initAction()/*: void*/
    {
        $this->setFormAction($this->dic->ctrl()->getFormAction($this->parent_obj));
    }


    /**
     * @return array
     */
    protected function getSeriesFilterOptions()
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

    /**
     * public method initializeData is used here, because we want to initialize the data after the constructor (for manual limit)
     */
    protected function initData()
    {
    }
}
