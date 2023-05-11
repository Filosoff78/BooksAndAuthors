<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use RSB\Books\ORM\AuthorsTable;

if ($arResult['userField']['MULTIPLE'] === 'Y' && !empty($arResult['value'][0])) {
    $arBooks = AuthorsTable::query()
        ->whereIn('ID', explode(',', $arResult['value'][0]))
        ->setSelect(['ID', 'NAME'])
        ->fetchAll();
} else if ($arResult['userField']['MULTIPLE'] !== 'Y' && !empty($arResult['value'])) {
    $arBooks = AuthorsTable::query()
        ->where('ID', $arResult['value'])
        ->setSelect(['ID', 'NAME'])
        ->fetch();
}

if (!empty($arBooks)) {
    foreach ($arBooks as $book) {
        $arResult['BOOKS'][] = $book['NAME'];
    }
}
