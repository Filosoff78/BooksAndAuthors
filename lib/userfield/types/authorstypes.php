<?php
namespace RSB\Books\UserField\Types;
use \Bitrix\Main\UserField\Types\BaseType;
use \Bitrix\Main\Localization\Loc;

class AuthorsTypes extends BaseType
{
    public const
        USER_TYPE_ID = 'st_authors',
        RENDER_COMPONENT = 'rsb:books.field.authors';

    public static function getDescription(): array
    {
        return [
            'USER_TYPE_ID' => self::USER_TYPE_ID,
            'CLASS_NAME' => __CLASS__,
            'DESCRIPTION' => Loc::getMessage('RSB_FIELD_AUTHORS_DESC'),
            'BASE_TYPE' => \CUserTypeManager::BASE_TYPE_INT
        ];
    }

    public static function getDbColumnType(): string
    {
        global $DB;
        switch(strtolower($DB->type))
        {
            case "mysql":
                return "int(18)";
            case "oracle":
                return "number(18)";
            case "mssql":
                return "int";
        }
        return "int";
    }

    public static function checkFields($userField, $value)
    {
        return [];
    }

}
