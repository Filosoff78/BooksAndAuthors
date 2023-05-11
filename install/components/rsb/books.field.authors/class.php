<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Component\BaseUfComponent;
use RSB\Books\UserField\Types\AuthorsTypes;

class AuthorsUfComponent extends BaseUfComponent
{
    protected static function getUserTypeId(): string
    {
        return AuthorsTypes::USER_TYPE_ID;
    }
}
