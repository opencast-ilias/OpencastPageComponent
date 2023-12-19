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
    public const TABLE_NAME = 'ocpc_token';

    public function getConnectorContainerName(): string
    {
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
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getUsrId()
    {
        return $this->usr_id;
    }

    /**
     * @return Token
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @return int
     */
    public function getValidUntilUnix()
    {
        return $this->valid_until_unix;
    }

    public function setId(int $id)/*: void*/
    {
        $this->id = $id;
    }

    public function setUsrId(int $usr_id)/*: void*/
    {
        $this->usr_id = $usr_id;
    }

    public function getEventId(): string
    {
        return $this->event_id;
    }

    public function setEventId(string $event_id)/*: void*/
    {
        $this->event_id = $event_id;
    }

    public function setToken(Token $token)/*: void*/
    {
        $this->token = $token;
    }

    public function setValidUntilUnix(int $valid_until_unix)/*: void*/
    {
        $this->valid_until_unix = $valid_until_unix;
    }

    /**
     * @param $field_name
     *
     * @return string|null
     */
    public function sleep($field_name)
    {
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
    public function wakeUp($field_name, $field_value)
    {
        switch ($field_name) {
            case 'token':
                return new Token($field_value);
            default:
                return null;
        }
    }
}
