<?php

// Регистрация действия для обработчика изображений из csv файла 
add_action('admin_post_generate_csv_url_image', 'generate_csv_url_images');
function generate_csv_url_images() {
    echo '<a target="_blank" href="' . admin_url('admin-post.php?action=generate_csv') . '">Создать CSV-файл с изображениями</a>';
    // Получаем информацию о каталоге загрузок
    $upload_dir = wp_upload_dir();

    // Путь к директории загрузок
    $uploads_path = $upload_dir['basedir'];

    // Путь к файлу CSV
    $csvFile = get_template_directory() . '/pages_xoz_torg/products.csv';

    // Папка для сохранения изображений
    $imagesDir = $uploads_path . '/images/';

    // Проверка на наличие папки для изображений, если нет — создание
    if (!is_dir($imagesDir)) {
        mkdir($imagesDir, 0777, true);
    }
    
    // Чтение CSV файла
    if (($handle = fopen($csvFile, 'r')) !== false) {
        $header = fgetcsv($handle); // Пропуск заголовка

        // Обработка каждой строки CSV
        while (($row = fgetcsv($handle)) !== false) {
            $sku = trim($row[0]); // SKU товара
            $productName = trim($row[1]); // Имя товара
            $imageUrl = trim($row[4]); // URL изображения

            // Пропускаем строки без SKU, имени товара или URL изображения
            if (empty($sku) || empty($productName) || empty($imageUrl)) continue;

            echo "Скачиваем изображение для SKU: $sku, Товар: $productName<br>";

            // Получаем содержимое изображения
            $imageData = @file_get_contents($imageUrl);

            if ($imageData === false) {
                echo "Не удалось скачать изображение для SKU: $sku, Товар: $productName<br>";
                continue;
            }

            // Сохранение изображения с именем SKU
            $imagePath = $imagesDir . $sku . '.jpg';
            file_put_contents($imagePath, $imageData);

            echo "Изображение сохранено: $imagePath<br>";

            sleep(1); // Задержка между запросами
        }

        fclose($handle); // Закрытие файла CSV
      
    } else {
        echo "Не удалось открыть файл CSV: $csvFile<br>";
    }
}
