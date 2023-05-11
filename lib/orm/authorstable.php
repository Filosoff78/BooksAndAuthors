<?php
namespace RSB\Books\ORM;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;

Loc::loadMessages(__FILE__);

class AuthorsTable extends DataManager
{
    public static function getTableName()
    {
        return 'rsb_authors';
    }

    public static function getMap()
    {
        return [
            (new IntegerField('ID'))
                ->configurePrimary()
                ->configureAutocomplete()
                ->configureTitle('ID'),

            (new StringField('NAME'))
                ->configureRequired()
                ->configureTitle(Loc::getMessage('RSB_BOOKS_TABLE_FIO')),
        ];
    }
}
