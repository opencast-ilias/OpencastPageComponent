<?php

use ILIAS\DI\Container;
use srag\CustomInputGUIs\OpencastPageComponent\TableGUI\TableGUI;
use srag\DIC\OpencastPageComponent\Exception\DICException;
use srag\Plugins\Opencast\Model\API\Event\EventRepository;

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
     * @var EventRepository
     */
    protected $event_repository;


    /**
     * VideoSearchTableGUI constructor.
     *
     * @param           $parent_gui
     * @param string    $parent_cmd
     * @param Container $dic
     * @param string    $command_url will be executed when choosing a video (with the event_id as GET parameter)
     *
     * @throws DICException
     */
    public function __construct($parent_gui, $parent_cmd, $dic, $command_url)
    {
        $this->dic = $dic;
        $this->command_url = $command_url;
        $this->opencast_plugin = ilOpenCastPlugin::getInstance();
        $this->event_repository = new EventRepository($dic);
        $this->initId();    // this is necessary so the offset and order can be determined
        $this->setLimit(10);
        $this->limit_determined = true;
        $this->setExternalSegmentation(true);
        $this->setExternalSorting(true);
        $this->determineOffsetAndOrder();
        parent::__construct($parent_gui, $parent_cmd);
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
        /** @var xoctEvent $object */
        $object = $row['object'];
        if ($object->getProcessingState() == xoctEvent::STATE_SUCCEEDED) {
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
     * @param int   $format
     *
     * @return false|mixed|string
     * @throws xoctException
     * @inheritDoc
     */
    protected function getColumnValue(string $column, /*array*/ $row, int $format = self::DEFAULT_FORMAT) : string
    {
        switch ($column) {
            case 'thumbnail':
                /** @var xoctEvent $object */
                $object = $row['object'];

                return '<img height="107.5px" width="200px" src="' . $object->publications()->getThumbnailUrl() . '">';
            case 'title':
                /** @var xoctEvent $object */
                $object = $row['object'];
                $renderer = new xoctEventRenderer($object);
                return $row['title'] . $renderer->getStateHTML();
            case 'description':
                return $row['description'];
            case 'series':
                /** @var xoctEvent $object */
                $object = $row['object'];

                return $object->getSeries();
            case 'start':
                return date('d.m.Y H:i', $row['start_unix']);
            case 'location':
                return $row['location'];
        }
    }


    /**
     * @inheritDoc
     */
    protected function getSelectableColumns2() : array
    {
        return [
            'thumbnail'   => ['txt' => $this->opencast_plugin->txt('event_preview'), 'id' => 'thumbnail', 'default' => true],
            'title'       => ['txt' => $this->opencast_plugin->txt('event_title'), 'id' => 'title', 'default' => true],
            'description' => ['txt' => $this->opencast_plugin->txt('event_description'), 'id' => 'description', 'default' => true],
            'series'      => ['txt' => $this->opencast_plugin->txt('event_series'), 'id' => 'series', 'default' => true],
            'start'       => ['txt' => $this->opencast_plugin->txt('event_start'), 'id' => 'start', 'default' => true],
            'location'    => ['txt' => $this->opencast_plugin->txt('event_location'), 'id' => 'location', 'default' => true],
        ];
    }


    /**
     * @inheritDoc
     */
    protected function initData()
    {
        // the api doesn't deliver a max count, so we fetch (limit + 1) to see if there should be a 'next' page
        try {
            $common_idp = xoctConf::getConfig(xoctConf::F_COMMON_IDP);
            $events = (array) $this->event_repository->getFiltered(
                $this->buildFilterArray(),
                $common_idp ? xoctUser::getInstance($this->dic->user())->getIdentifier() : '',
                $common_idp ? [] : [xoctUser::getInstance($this->dic->user())->getUserRoleName()],
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
    protected function buildFilterArray() : array
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
        $series->setOptions($this->getSeriesFilterOptions());
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
        foreach (xoctSeries::getAllForUser(xoctUser::getInstance($this->dic->user())->getUserRoleName()) as $serie) {
            $series_options[$serie->getIdentifier()] = $serie->getTitle() . ' (...' . substr($serie->getIdentifier(), -4, 4) . ')';
        }

        natcasesort($series_options);

        return $series_options;
    }
}
