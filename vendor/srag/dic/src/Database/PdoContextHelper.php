<?php

namespace srag\DIC\OpencastPageComponent\Database;

use ilDBPdo;
use ilDBPdoInterface;
use PDO;
use srag\DIC\OpencastPageComponent\Exception\DICException;

/**
 * Class PdoContextHelper
 *
 * @package srag\DIC\OpencastPageComponent\Database
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 *
 * @internal
 */
final class PdoContextHelper extends ilDBPdo
{

    /**
     * @param ilDBPdoInterface $db
     *
     * @return PDO
     *
     * @throws DICException PdoContextHelper only supports ilDBPdo!
     *
     * @internal
     */
    public static function getPdo(ilDBPdoInterface $db) : PDO
    {
        if (!($db instanceof ilDBPdo)) {
            throw new DICException("PdoContextHelper only supports ilDBPdo!");
        }

        return $db->pdo;
    }


    /**
     * PdoContextHelper constructor
     */
    private function __construct()
    {

    }


    /**
     * @inheritdoc
     */
    public function initHelpers()
    {

    }
}
