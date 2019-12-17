<?php

namespace srag\DataTable\OpencastPageComponent\Implementation\Data\Fetcher;

use ILIAS\DI\Container;
use srag\DataTable\OpencastPageComponent\Component\Data\Data as DataInterface;
use srag\DataTable\OpencastPageComponent\Component\Data\Row\RowData;
use srag\DataTable\OpencastPageComponent\Component\Settings\Settings;
use srag\DataTable\OpencastPageComponent\Component\Settings\Sort\SortField;
use srag\DataTable\OpencastPageComponent\Implementation\Data\Data;
use srag\DataTable\OpencastPageComponent\Implementation\Data\Row\PropertyRowData;
use stdClass;

/**
 * Class StaticDataFetcher
 *
 * @package srag\DataTable\OpencastPageComponent\Implementation\Data\Fetcher
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
class StaticDataFetcher extends AbstractDataFetcher
{

    /**
     * @var object[]
     */
    protected $data;


    /**
     * @inheritDoc
     *
     * @param object[] $data
     */
    public function __construct(Container $dic, array $data)
    {
        parent::__construct($dic);

        $this->data = $data;
    }


    /**
     * @inheritDoc
     */
    public function fetchData(Settings $settings) : DataInterface
    {
        $data = array_filter($this->data, function (stdClass $data) use ($settings): bool {
            $match = true;

            foreach ($settings->getFilterFieldValues() as $key => $value) {
                if (!empty($value)) {
                    switch (true) {
                        case is_array($value):
                            $match = in_array($data->{$key}, $value);
                            break;

                        case is_integer($data->{$key}):
                        case is_float($data->{$key}):
                            $match = ($data->{$key} === intval($value));
                            break;

                        case is_string($data->{$key}):
                            $match = (stripos($data->{$key}, $value) !== false);
                            break;

                        default:
                            $match = ($data->{$key} === $value);
                            break;
                    }

                    if (!$match) {
                        break;
                    }
                }
            }

            return $match;
        });

        usort($data, function (stdClass $o1, stdClass $o2) use ($settings): int {
            foreach ($settings->getSortFields() as $sort_field) {
                $s1 = strval($o1->{$sort_field->getSortField()});
                $s2 = strval($o2->{$sort_field->getSortField()});

                $i = strnatcmp($s1, $s2);

                if ($sort_field->getSortFieldDirection() === SortField::SORT_DIRECTION_DOWN) {
                    $i *= -1;
                }

                if ($i !== 0) {
                    return $i;
                }
            }

            return 0;
        });

        $max_count = count($data);

        $data = array_slice($data, $settings->getOffset(), $settings->getRowsCount());

        $data = array_map(function (stdClass $row) : RowData {
            return new PropertyRowData(strval($row->column1), $row);
        }, $data);

        return new Data($data, $max_count);
    }
}
