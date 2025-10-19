<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
$APPLICATION->SetTitle("Тестовое задание: Грид");

use Bitrix\Main\Context;

$filterId = 'CSV_FILTER';
$gridId = 'CSV_GRID';

$request = Context::getCurrent()->getRequest();
$filterStatus = $request->get("STATUS");
$filterType = $request->get("TYPE");
$filterDurationFrom = $request->get("DURATION_FROM");
$filterDurationTo = $request->get("DURATION_TO");

$data = [];
$csvFile = $_SERVER["DOCUMENT_ROOT"] . "/test/test_for_grid.csv";
if (($handle = fopen($csvFile, "r")) !== false) {
    $headers = fgetcsv($handle, 1000, ";");

    while (($row = fgetcsv($handle, 1000, ";")) !== false) {
        $item = array_combine($headers, $row);
        $item = array_combine($headers, $row);

        if ($filterStatus && $item['Статус'] !== $filterStatus) continue;
        if ($filterType && $item['Тип звонка'] !== $filterType) continue;
        $callDuration = (int)$item['Длительность звонка'];
        if ($filterDurationFrom !== null && $filterDurationFrom !== '' && $callDuration < (int)$filterDurationFrom) continue;
        if ($filterDurationTo !== null && $filterDurationTo !== '' && $callDuration > (int)$filterDurationTo) continue;

        $data[] = $item;
    }
    fclose($handle);
}

$columns = [];
if (!empty($data)) {
    foreach (array_keys($data[0]) as $key) {
        $columns[] = ['id' => $key, 'name' => $key, 'default' => true];
    }
}

$rows = [];
foreach ($data as $item) {
    $rows[] = ['data' => $item];
}

$filter = [
    ['id' => 'STATUS',
    'name' => 'Статус',
    'type' => 'list',
    'items' => ['Отвечено' => 'Отвечено', 'Не отвечено' => 'Не отвечено', 'Занято' => 'Занято',]],

    ['id' => 'TYPE',
    'name' => 'Тип звонка',
    'type' => 'list',
    'items' => ['Входящий' => 'Входящий', 'Исходящий' => 'Исходящий',]],

    ['id' => 'DURATION',
    'name' => 'Длительность звонка (сек)',
    'type' => 'number'],
];


$APPLICATION->IncludeComponent(
    'bitrix:main.ui.filter',
    '',
    [
        'FILTER_ID' => $filterId,
        'GRID_ID' => $gridId,
        'FILTER' => $filter,
        'ENABLE_LIVE_SEARCH' => true,
        'ENABLE_LABEL' => true,
    ]
);

$APPLICATION->IncludeComponent(
    'bitrix:main.ui.grid',
    '',
    [
        'GRID_ID' => $gridId,
        'COLUMNS' => $columns,
        'ROWS' => $rows,
        'SHOW_ROW_CHECKBOXES' => false,
        'SHOW_GRID_SETTINGS_MENU' => true,
        'SHOW_PAGINATION' => true,
        'AJAX_MODE' => 'Y',
        'PAGE_SIZES' => [
            ['NAME' => '5', 'VALUE' => '5'],
            ['NAME' => '10', 'VALUE' => '10'],
            ['NAME' => '20', 'VALUE' => '20'],
        ],
        'TOTAL_ROWS_COUNT' => count($rows),
    ]
);
?>
    <script>
        BX.ready(function () {
            const filterObj = BX.Main.filterManager.getById('CSV_FILTER');
            if (!filterObj) return;

            BX.addCustomEvent('BX.Main.Filter:apply', function () {
                const filterValues = filterObj.getFilterFieldsValues();

                const map = {};
                if (filterValues.STATUS) map.STATUS = filterValues.STATUS;
                if (filterValues.TYPE) map.TYPE = filterValues.TYPE;
                if (filterValues.DURATION_from) map.DURATION_FROM = filterValues.DURATION_from;
                if (filterValues.DURATION_to) map.DURATION_TO = filterValues.DURATION_to;

                const query = new URLSearchParams(map).toString();
                window.location.href = window.location.pathname + (query ? '?' + query : '');
            });
        });
    </script>
<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");
?>