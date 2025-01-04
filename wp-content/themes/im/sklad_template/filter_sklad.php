<?php

function filter_sklad_csv_files() {
    $template_name = 'sklad_template';
    error_log("Фильтрация для шаблона: $template_name");

    $template_path = get_template_directory() . '/' . $template_name;
    $output_file = $template_path . '/sklad_output.csv';
    $filter_file = $template_path . '/sklad_filter.csv';
    $filtered_output_file = $template_path . '/sklad_filtered_output.csv';

    error_log("Файлы: output_file = $output_file, filter_file = $filter_file");

    check_file_exists($output_file);
    check_file_exists($filter_file);

    $output_data = array_map('str_getcsv', file($output_file));
    $output_headers = array_shift($output_data);

    $filter_data = array_map('str_getcsv', file($filter_file));
    array_shift($filter_data);

    $filter_sku_or_names = array_map(function ($row) {
        return strtolower(trim(preg_replace('/\s+/', ' ', $row[0])));
    }, $filter_data);

    $filtered_rows = [$output_headers];
    foreach ($output_data as $row) {
        $sku_or_name = strtolower(trim($row[0]));
        $product_name = isset($row[1]) ? strtolower(trim($row[1])) : '';

        if (in_array($sku_or_name, $filter_sku_or_names) || in_array($product_name, $filter_sku_or_names)) {
            $filtered_rows[] = $row;
        }
    }

    if (count($filtered_rows) <= 1) {
        handle_error("Фильтрация не нашла совпадений.");
    }

    error_log("Сохранение отфильтрованных данных в: $filtered_output_file");
    $fp = fopen($filtered_output_file, 'w');
    foreach ($filtered_rows as $row) {
        fputcsv($fp, $row);
    }
    fclose($fp);

    echo 'Фильтрация для SKLAD завершена. Результат сохранен в ' . basename($filtered_output_file);
    exit;
}
