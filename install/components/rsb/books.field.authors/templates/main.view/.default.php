<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$isMulti = $arResult['userField']['MULTIPLE'] === 'Y';

$isMulti ? $compStr = implode(', ', $arResult['BOOKS'])
         : $compStr = $arResult['BOOKS'][0];

print $compStr ?: 'Выбрать';
