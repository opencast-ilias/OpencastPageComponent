<?php

namespace srag\Plugins\OpencastPageComponent\Authorization;

use ActiveRecord;

/**
 * Class Token
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
class TokenAR extends ActiveRecord
{
    const TABLE_NAME = 'ocpc_token';


    /**
     * @return string
     */
    public function getConnectorContainerName() {
        return self::TABLE_NAME;
    }



    /**
     * @var int
     *
     * @con_has_field    true
     * @con_fieldtype    integer
     * @con_length       8
     * @con_is_primary   true
     * @con_sequence     true
     */
    protected $id;

    /**
     * @var int
     *
     * @db_has_field        true
     * @db_is_notnull       true
     * @db_fieldtype        integer
     * @db_length           8
     */
    protected $usr_id;


    /**
     * @var string
     *
     * @db_is_notnull       true
     * @db_has_field        true
     * @db_fieldtype        text
     * @db_length           128
     */
    protected $event_id;

    /**
     * @var Token
     *
     * @db_has_field        true
     * @db_is_notnull       true
     * @db_fieldtype        text
     * @db_length           128
     */
    protected $token;

    /**
     * @var int
     *
     * @db_has_field        true
     * @db_is_notnull       true
     * @db_fieldtype        integer
     * @db_length           8
     */
    protected $valid_until_unix;


    /**
     * @return int
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getUsrId() {
        return $this->usr_id;
    }


    /**
     * @return Token
     */
    public function getToken() {
        return $this->token;
    }


    /**
     * @return int
     */
    public function getValidUntilUnix() {
        return $this->valid_until_unix;
    }


    /**
     * @param int $id
     */
    public function setId(int $id)
    {
        $this->id = $id;
    }


    /**
     * @param int $usr_id
     */
    public function setUsrId(int $usr_id)
    {
        $this->usr_id = $usr_id;
    }


    /**
     * @return string
     */
    public function getEventId() : string
    {
        return $this->event_id;
    }


    /**
     * @param string $event_id
     */
    public function setEventId(string $event_id)
    {
        $this->event_id = $event_id;
    }

    /**
     * @param Token $token
     */
    public function setToken(Token $token)
    {
        $this->token = $token;
    }


    /**
     * @param int $valid_until_unix
     */
    public function setValidUntilUnix(int $valid_until_unix)
    {
        $this->valid_until_unix = $valid_until_unix;
    }



    /**
     * @param $field_name
     *
     * @return string|null
     */
    public function sleep($field_name) {
        switch ($field_name) {
            case 'token':
                return $this->token->toString();
            default:
                return null;
        }
    }


    /**
     * @param $field_name
     * @param $field_value
     *
     * @return Token|null
     */
    public function wakeUp($field_name, $field_value) {
        switch ($field_name) {
            case 'token':
                return new Token($field_value);
            default:
                return null;
        }
    }
}