<?php
use Bitrix\Main\Grid\Panel\Snippet;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UserField\Renderer;
use Bitrix\UI\Toolbar\Facade\Toolbar;
use RSB\Books\ORM\BooksTable;


if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

class BooksListComponent extends CBitrixComponent
{
    const
        ADD_FORM = '/rsb/books/books_edit.php?ID=0',
        GRID_ID = 'rsb_books_list',
        EDIT_PATH = '/rsb/books/books_edit.php';
    private $fieldsMap;

    public function checkModules()
    {
        if (!Loader::includeModule('rsb.books')) {
            ShowError(Loc::getMessage('MODULE_IS_NOT_INSTALLED'));
            return false;
        }
        return true;
    }

    public function processGridActions()
    {
        $postAction = 'action_button_' . self::GRID_ID;

        if ($this->request->isPost()
            && $this->request->getPost($postAction)) {
            if ($this->request->getPost($postAction) == 'delete')
                $this->processDelete();
        }
    }

    public function processDelete()
    {
        $reqest = $this->request;
        if (!$reqest->getPost('ID'))
            return false;

        foreach ($reqest->getPost('ID') as $id)
            BooksTable::delete($id);

    }

    public function executeComponent()
    {
        //Проверка покдлючения модуля
        if (!$this->checkModules())
            return;

        //Действия в гриде
        $this->processGridActions();

        //Получение карты полей
        $this->fieldsMap = BooksTable::getFieldsInfo();

        //Реализация фильтра
        $this->makeFilter();

        //Реализация грида
        $this->makeGrid();

        $this->arResult['SIDE_PANEL_PARAMS'] = [
            'newWindowLabel' => 'Y',
            'copyLinkLabel' => 'Y',
            'width' => 700,
            'cacheable' => false
        ];
        //Реализация тулбаоа
        $this->makeToolbar();


        //Заголовок страницы
        global $APPLICATION;
        $APPLICATION->SetTitle(Loc::getMessage("COMPONENT_TITLE"));


        //Подклчение шаблона компонента
        $this->includeComponentTemplate();
    }


    /**
     * Обработка данных для грида
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function makeGrid()
    {
        $this->arResult['GRID_ID'] = self::GRID_ID;

        //количестов элементов на страниц
        $this->arResult['GRID_PAGE_SIZES'] = self::getPageSizes();

        //Заголовкии
        $this->arResult['HEADERS'] = $this->prepareHeaders();

        //Панель действий
        $this->arResult['ACTION_PANEL'] = $this->prepareActionPanel();


        //region фильтр
        $filter = [];
        foreach ($this->arResult['FILTER'] as $arFilter) {
            $filterable[$arFilter['id']] = $arFilter['filterable'];
        }

        $filterOption = new Bitrix\Main\UI\Filter\Options(self::GRID_ID);
        $filterData = $filterOption->getFilter($this->arResult["FILTER"]);
        foreach ($filterData as $key => $value) {
            if (is_array($value)) {
                if (empty($value))
                    continue;
            } elseif ($value == '')
                continue;

            if (mb_substr($key, -5) == "_from") {
                $new_key = mb_substr($key, 0, -5);
                $op = (!empty($filterData[$new_key . "_numsel"]) && $filterData[$new_key . "_numsel"] == "more") ? ">" : ">=";
            } elseif (mb_substr($key, -3) == "_to") {
                $new_key = mb_substr($key, 0, -3);
                $op = (!empty($filterData[$new_key . "_numsel"]) && $filterData[$new_key . "_numsel"] == "less") ? "<" : "<=";
            } else {
                $op = "";
                $new_key = $key;
            }

            if (array_key_exists($new_key, $filterable)) {
                if ($op == "")
                    $op = $filterable[$new_key];
                $filter[$op . $new_key] = $value;
            }

            if ($key == "FIND" && trim($value)) {
                $op = "*";
                $arFilter[$op . "SEARCHABLE_CONTENT"] = $value;
            }
        }
        //endregion  фильтр

        //Объект грида
        $gridOptions = new Bitrix\Main\Grid\Options(self::GRID_ID);
        //Параметры постраничной навигации
        $navParams = $gridOptions->GetNavParams();
        $nav = new Bitrix\Main\UI\PageNavigation(self::GRID_ID);

        //Настрйоки навигации
        $nav->allowAllRecords(true)
            ->setPageSize($navParams['nPageSize'])
            ->initFromUri();

        //Сортировка
        $gridSort = $gridOptions->GetSorting(["sort" => ["ID" => "ASC"]]);
        $this->arResult['SORT'] = $gridSort['sort'];
        $this->arResult['SORT_VARS'] = $gridSort['vars'];

        //region обработка данных
        $booksQuery = BooksTable::getList([
            'filter' => $filter,
            'select' => [
                '*',
            ],
            'order' => $gridSort['sort'],
            'offset' => $nav->getOffset(),
            'limit' => $nav->getLimit(),
            'count_total' => true
        ]);
        //подготовка строк
        while ($books = $booksQuery->fetch()) {
            $row = [
                'id' => $books['ID'],
                'actions' => [
                    [
                        'text' => 'Посмотреть',
                        'onclick' => 'BX.SidePanel.Instance.open("' . self::EDIT_PATH . '?ID=' . $books['ID'] . '");',
                        'default' => true
                    ],
                    [
                        'text' => 'Редактировать',
                        'onclick' => 'BX.SidePanel.Instance.open("' . self::EDIT_PATH . '?ID=' . $books['ID'] . '&init_mode=edit");',
                    ]
                ],
            ];
            //подготовка полей для вывода
            foreach ($this->fieldsMap as $field) {
                $row['data'][$field['NAME']] = $books[$field['NAME']];

                switch ($field['NAME']) {
                    case 'NAME':
                        $row['columns'][$field['NAME']] = '<a href="' . self::EDIT_PATH . '?ID=' . $books['ID'] . '" data-id="' . $books['ID'] . '">' . $books[$field['NAME']] . '</a>';
                        break;
                    case 'AUTHOR_ID':
                        $userField = [
                            'USER_TYPE_ID' => 'st_authors',
                            'FIELD_NAME' => $field['NAME'],
                            'FIELD_FORM_NAME' => $field['NAME'],
                            'VALUE' => $books[$field['NAME']] ?? null,
                            'MULTIPLE' => 'Y',
                        ];
                        $row['columns'][$field['NAME']] = (new Renderer($userField, ['mode' => 'main.view']))->render();
                        break;
                    default:
                        $row['columns'][$field['NAME']] = $books[$field['NAME']];
                        break;
                }
            }
            $this->arResult['ROWS'][] = $row;
        }
        //endregion

        //Количество элемнетов
        $nav->setRecordCount($booksQuery->getCount());

        $this->arResult["NAV_OBJECT"] = $nav;
        $this->arResult["TOTAL_ROWS_COUNT"] = $booksQuery->getCount();
    }


    /**
     * Групповые действия
     * @return array[][][]
     */
    public function prepareActionPanel()
    {
        $snippet = new Snippet();
        return [
            'GROUPS' => [
                [
                    'ITEMS' => [
                        $snippet->getRemoveButton(),
                    ],
                ]
            ],
        ];
    }


    /**
     * Формирвоания массива с количеством элементов на странице
     * @return string[][]
     */
    public static function getPageSizes(): array
    {
        return [
            ['NAME' => "5", 'VALUE' => '5'],
            ['NAME' => '10', 'VALUE' => '10'],
            ['NAME' => '20', 'VALUE' => '20'],
            ['NAME' => '50', 'VALUE' => '50'],
            ['NAME' => '100', 'VALUE' => '100']
        ];
    }


    /**
     * Загловки таблицы
     * @return array
     */
    public function prepareHeaders(): array
    {
        foreach ($this->fieldsMap as $field) {
            $headers[] = [
                'id' => $field['NAME'],
                'name' => $field['TITLE'],
                'sort' => $field['NAME'],
            ];
        }
        return $headers ?? [];
    }


    /**
     * Подготовка фильтра
     */
    public function makeFilter()
    {
        $this->arResult['FILTER_ID'] = self::GRID_ID;

        foreach ($this->fieldsMap as $field) {
            $filterItem = [
                'id' => $field['NAME'],
                'name' => $field['TITLE']
            ];
            switch ($field['DATA_TYPE']) {
                case "integer":
                    $filterItem["type"] = "number";
                    break;
                case "boolean":
                    $filterItem["type"] = "checkbox";
                    break;
                default:
                    $filterItem["type"] = "string";
                    $filterItem["filterable"] = "?";
                    break;
            };
            $this->arResult['FILTER'][] = $filterItem;
        }
    }

    /**
     * Тулбар в шапке
     */
    public function makeToolbar()
    {
        $linkButton = new \Bitrix\UI\Buttons\CreateButton(
            [
                'link' => self::ADD_FORM
            ]
        );
        Toolbar::addButton($linkButton);
        Toolbar::addFilter([
            "FILTER_ID" => $this->arResult["FILTER_ID"],
            "GRID_ID" => $this->arResult["GRID_ID"],
            "FILTER" => $this->arResult["FILTER"],
            "ENABLE_LABEL" => true,
            "ENABLE_LIVE_SEARCH" => false
        ]);
    }
}
