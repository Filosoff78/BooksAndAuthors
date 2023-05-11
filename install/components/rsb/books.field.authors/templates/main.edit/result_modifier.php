<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Localization\Loc;
use RSB\Books\ORM\AuthorsTable;

\Bitrix\Main\UI\Extension::load('ui.entity-selector');

$arResult['FIELD_FORM_NAME'] = str_replace(['[', ']'], '_', $arResult['fieldName']);

$arResult['arJsParams'] = [
    'fieldName' => $arResult['fieldName'],
    'userFieldName' => $arResult['userField']['FIELD_NAME'],
    'fieldFormName' => $arResult['FIELD_FORM_NAME'] ?? $arResult['userField']['FIELD_FORM_NAME'],
    'selectedItemIds' => $arResult['value'],
    'multiple' => $arResult['userField']['MULTIPLE'] === 'Y',
];

$arResult['arJsParams']['tabs'] = [
    [
        'id' => 'authors',
        'title' => Loc::getMessage("FIELD_COMR_SELECTOR_TITILE")
    ]
];

$arResult['BOOKS'] = [];

if (empty($arResult['value']) || empty($arResult['value'][0]))
    return;

if (!$arResult['arJsParams']['multiple']) {
    $arResult['BOOKS'] = AuthorsTable::query()
        ->where('ID', $arResult['value'])
        ->setSelect(['ID', 'NAME'])
        ->fetch();
} else {
    $arResult['BOOKS'] = AuthorsTable::query()
        ->whereIn('ID', explode(',', $arResult['value'][0]))
        ->setSelect(['ID', 'NAME'])
        ->fetchAll();
}

foreach ($arResult['BOOKS'] as $book) {
    $arResult['arJsParams']['selectedItems'][] = [
        'id' => $book['ID'],
        'entityId' => 'authors',
        'title' => $book['NAME']
    ];
}
