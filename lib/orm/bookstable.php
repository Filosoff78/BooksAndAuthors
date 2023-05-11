<?php
namespace RSB\Books\ORM;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\SystemException;

Loc::loadMessages(__FILE__);

class BooksTable extends DataManager
{
    public static function getTableName()
    {
        return 'rsb_books';
    }

    /**
     * @throws SystemException
     * @throws ArgumentException
     */
    public static function getMap()
    {
        return [
            (new IntegerField('ID'))
                ->configurePrimary()
                ->configureAutocomplete()
                ->configureTitle('ID'),

            (new StringField('NAME'))
                ->configureRequired()
                ->configureTitle(Loc::getMessage('RSB_BOOKS_TABLE_BOOKS_NAME')),

            (new StringField('AUTHOR_ID'))
                ->configureRequired()
                ->configureTitle(Loc::getMessage('RSB_BOOKS_TABLE_AUTHOR_ID')),

            (new Reference(
                'AUTHOR',
                self::class,
                Join::on('this.AUTHOR_ID', 'ref.ID')
            )),
        ];
    }

    public static function getFieldsInfo()
    {
        $map = self::getEntity()->getFields();

        foreach ($map as $field) {
            if ($field instanceof Reference)
                continue;

            $fieldsMap[$field->getName()] = [
                'NAME' => $field->getName(),
                'TITLE' => $field->getTitle(),
                'IS_REQUIRED' => $field->isRequired(),
                'DEFAULT_VALUE' => $field->getDefaultValue(),
                'DATA_TYPE' => $field->getDataType(),
                'IS_PRIMARY' => $field->isPrimary()
            ];
        }
        return $fieldsMap ?? [];
    }
}
