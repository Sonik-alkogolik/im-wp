<?php 
// Регистрация действия для обработчика CSV
add_action('admin_post_generate_csv', 'generate_csv_with_images');

function generate_csv_with_images() {
    echo '<a href="/">На главную</a>';
    // Путь к исходному CSV-файлу с товарами
    $csvFile = get_template_directory() . '/pages_xoz_torg/products.csv';

    // Папка, где хранятся изображения товаров
	// Получаем информацию о каталоге загрузок
	$upload_dir_generate = wp_upload_dir();

	// Путь к директории загрузок
	$uploads_path_generate = $upload_dir_generate['basedir'];
    $imagesDir = $uploads_path_generate . '/images/';

    // Путь к новому CSV-файлу с путями к изображениям
    $newCsvFile = get_template_directory() . '/pages_xoz_torg/products_img.csv';

    // Запись путей в журнал ошибок
    error_log("Путь к исходному CSV-файлу: $csvFile");
    error_log("Папка с изображениями: $imagesDir");
    error_log("Путь к новому CSV-файлу: $newCsvFile");

    // Проверка существования исходного CSV-файла
    if (!file_exists($csvFile)) {
        wp_die("Исходный CSV-файл не найден: $csvFile");
    }

    // Открытие исходного CSV-файла для чтения
    if (($handle = fopen($csvFile, 'r')) === false) {
        wp_die("Не удалось открыть исходный CSV-файл: $csvFile");
    }

    // Чтение заголовка
    $header = fgetcsv($handle);

    // Удаление 4-го столбца (пятый по счёту)
    unset($header[4]);

    // Добавление нового заголовка для столбца с путями к изображениям
    $header[] = 'Путь к изображению';

    // Открытие нового CSV-файла для записи
    if (($newHandle = fopen($newCsvFile, 'w')) === false) {
        fclose($handle);
        wp_die("Не удалось открыть новый CSV-файл для записи: $newCsvFile");
    }

    // Запись нового заголовка в новый CSV-файл
    fputcsv($newHandle, $header);

    // Обработка каждой строки исходного CSV-файла
    while (($row = fgetcsv($handle)) !== false) {
        // Удаление 4-го столбца (пятый по счёту)
        unset($row[4]);

        // Извлечение SKU товара (предполагается, что SKU находится в первом столбце)
        $sku = $row[0];

        // Формирование пути к изображению
        $imagePath = $imagesDir . $sku . '.jpg';

        // Проверка существования изображения
        if (file_exists($imagePath)) {
            // Если изображение найдено, добавляем путь к изображению в конец строки
            $row[] = '/images/' . $sku . '.jpg'; // замените на актуальный путь, если нужно
        } else {
            // Если изображение не найдено, добавляем пустую строку
            $row[] = '';
        }

        // Запись обновленной строки в новый CSV-файл
        fputcsv($newHandle, $row);
    }

    // Закрытие файлов
    fclose($handle);
    fclose($newHandle);

    // Вывод успешного сообщения
    echo "Новый CSV-файл с путями к изображениям успешно создан: $newCsvFile";

    exit;
}