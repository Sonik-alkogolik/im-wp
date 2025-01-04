<?php 

require_once ABSPATH . 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;


function generate_unique_id() {
    error_log("Функция generate_unique_id() вызвана");
    return uniqid('id_', true);
}

function handle_error($message) {
    error_log("Ошибка: $message");
    echo "Ошибка: " . $message;
    exit;
}

function check_file_exists($file_path) {
    error_log("Проверка наличия файла: $file_path");
    if (!file_exists($file_path)) {
        handle_error("Файл $file_path не найден.");
    }
}

function process_xlsx_to_csv() {
    error_log("Начало функции process_xlsx_to_csv");

    $input_file = get_template_directory() . '/xoz_template/input.xlsx';
    $output_file = get_template_directory() . '/xoz_template/output.csv';

    error_log("Пути файлов: input_file = $input_file, output_file = $output_file");

    check_file_exists($input_file);

    try {
        error_log("Загрузка файла XLSX: $input_file");
        $spreadsheet = IOFactory::load($input_file);
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray(null, true, true, true);

        error_log("Количество строк в файле: " . count($rows));

        $result = [];
        $sku_check = []; // Для проверки дублирования SKU

        foreach ($rows as $index => $row) {
            error_log("Обработка строки #$index: " . json_encode($row));
            if (empty(array_filter($row))) {
                error_log("Строка #$index пропущена (пустая)");
                continue;
            }

            $product_name = trim($row['A'] ?? '');
            $sku = trim($row['F'] ?? ''); // Столбец с SKU
            $unit = $row['G'] ?? '';
            $price = $row['H'] ?? '';

            // Форматируем цену
            $price = str_replace(',', '.', $price);
            $price = is_numeric($price) ? number_format((float)$price, 2, '.', '') : '';

            // Генерация SKU, если его нет
            if (empty($sku)) {
                $sku = 'sku_' . substr(md5($product_name), 0, 8);
                error_log("Генерируем SKU для товара '$product_name': $sku");
            }

            // Проверка на уникальность SKU
            if (!empty($product_name) && !empty($price)) {
                if (!isset($sku_check[$sku])) {
                    $sku_check[$sku] = true;
                    $result[] = [$sku, $product_name, $unit, $price];
                    error_log("Добавлена строка: SKU = $sku, Name = $product_name, Price = $price");
                } else {
                    error_log("Дублирующийся SKU пропущен: $sku");
                }
            }
        }

        // Создаем CSV
        error_log("Создание и сохранение файла CSV: $output_file");
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray([['SKU', 'Товар', 'Единица измерения', 'Цена']], null, 'A1');
        $sheet->fromArray($result, null, 'A2');

        $writer = IOFactory::createWriter($spreadsheet, 'Csv');
        $writer->setDelimiter(',');
        $writer->setEnclosure('"');
        $writer->setUseBOM(true);
        $writer->save($output_file);

        error_log("Файл CSV успешно сохранен: $output_file");
        echo "Обработка завершена. Результат сохранен в output.csv.";
        echo '<a href="/">На главную</a>';
    } catch (Exception $e) {
        error_log("Ошибка в process_xlsx_to_csv: " . $e->getMessage());
        handle_error($e->getMessage());
    }
}

function filter_csv_files() {
    error_log("Начало функции filter_csv_files");

    $output_file = get_template_directory() . '/xoz_template/output.csv';
    $filter_file = get_template_directory() . '/xoz_template/xoz_filter.csv';
    $filtered_output_file = get_template_directory() . '/xoz_template/xoz_template_filtered_output.csv';

    error_log("Файлы: output_file = $output_file, filter_file = $filter_file");

    check_file_exists($output_file);
    check_file_exists($filter_file);

    // Читаем данные из output.csv
    $output_data = array_map('str_getcsv', file($output_file));
    $output_headers = array_shift($output_data);

    // Читаем данные из filter.csv
    $filter_data = array_map('str_getcsv', file($filter_file));
    array_shift($filter_data); // Убираем заголовки

    error_log("Фильтрация строк из output.csv на основе filter.csv");

    // Функция для очистки строк
    function clean_value($value) {
        return strtolower(trim(preg_replace('/\s+/', ' ', $value)));
    }

    // Собираем массив фильтров
    $filter_sku_or_names = array_map(function ($row) {
        return clean_value($row[0]); // Столбец с фильтром (например, SKU или имя)
    }, $filter_data);

    $filtered_rows = [$output_headers];
    foreach ($output_data as $index => $row) {
        error_log("Проверка строки #$index: " . json_encode($row));

        // Очистка SKU и имени товара
        $sku_or_name = clean_value($row[0]); // SKU
        $product_name = isset($row[1]) ? clean_value($row[1]) : ''; // Название товара

        // Проверяем на совпадение SKU или имени товара
        if (in_array($sku_or_name, $filter_sku_or_names) || in_array($product_name, $filter_sku_or_names)) {
            // Увеличиваем цену на 50%
            if (isset($row[3]) && is_numeric($row[3])) { // Предполагается, что цена находится в столбце 4 (индекс 3)
                $original_price = (float) $row[3];
                $row[3] = number_format($original_price * 1.5, 2, '.', ''); // Увеличиваем цену и форматируем
                error_log("Цена увеличена: Оригинал = $original_price, Новая = {$row[3]}");
            }

            $filtered_rows[] = $row;
            error_log("Строка добавлена: " . json_encode($row));
        }
    }

    if (count($filtered_rows) <= 1) {
        error_log("Фильтрация не нашла совпадений.");
        handle_error("Фильтрация не нашла совпадений.");
    }

    // Сохраняем файл
    error_log("Сохранение отфильтрованных данных в: $filtered_output_file");
    $fp = fopen($filtered_output_file, 'w');
    foreach ($filtered_rows as $row) {
        fputcsv($fp, $row);
    }
    fclose($fp);

    error_log("Фильтрация завершена. Результат сохранен в: $filtered_output_file");
    echo 'Фильтрация завершена. Результат сохранен в ' . $filtered_output_file;
    echo '<a href="/">На главную</a>';
    exit;
}


// Регистрация обработчика для обработки XLSX
add_action('admin_post_process_xlsx', 'process_xlsx_to_csv');
add_action('admin_post_nopriv_process_xlsx', 'process_xlsx_to_csv');

// Регистрация обработчика для фильтрации CSV (для авторизованных пользователей)
add_action('admin_post_filter_products_by_csv', 'filter_csv_files');