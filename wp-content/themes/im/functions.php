<?php
/**
 * im functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package im
 */

if ( ! defined( '_S_VERSION' ) ) {
	// Replace the version number of the theme on each release.
	define( '_S_VERSION', '1.0.0' );
}

/**
 * Sets up theme defaults and registers support for various WordPress features.
 *
 * Note that this function is hooked into the after_setup_theme hook, which
 * runs before the init hook. The init hook is too late for some features, such
 * as indicating support for post thumbnails.
 */
function im_setup() {
	/*
		* Make theme available for translation.
		* Translations can be filed in the /languages/ directory.
		* If you're building a theme based on im, use a find and replace
		* to change 'im' to the name of your theme in all the template files.
		*/
	load_theme_textdomain( 'im', get_template_directory() . '/languages' );

	// Add default posts and comments RSS feed links to head.
	add_theme_support( 'automatic-feed-links' );

	/*
		* Let WordPress manage the document title.
		* By adding theme support, we declare that this theme does not use a
		* hard-coded <title> tag in the document head, and expect WordPress to
		* provide it for us.
		*/
	add_theme_support( 'title-tag' );

	/*
		* Enable support for Post Thumbnails on posts and pages.
		*
		* @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
		*/
	add_theme_support( 'post-thumbnails' );

	// This theme uses wp_nav_menu() in one location.
	register_nav_menus(
		array(
			'menu-1' => esc_html__( 'Primary', 'im' ),
		)
	);

	/*
		* Switch default core markup for search form, comment form, and comments
		* to output valid HTML5.
		*/
	add_theme_support(
		'html5',
		array(
			'search-form',
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
			'style',
			'script',
		)
	);

	// Set up the WordPress core custom background feature.
	add_theme_support(
		'custom-background',
		apply_filters(
			'im_custom_background_args',
			array(
				'default-color' => 'ffffff',
				'default-image' => '',
			)
		)
	);

	// Add theme support for selective refresh for widgets.
	add_theme_support( 'customize-selective-refresh-widgets' );

	/**
	 * Add support for core custom logo.
	 *
	 * @link https://codex.wordpress.org/Theme_Logo
	 */
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 250,
			'width'       => 250,
			'flex-width'  => true,
			'flex-height' => true,
		)
	);
}
add_action( 'after_setup_theme', 'im_setup' );

/**
 * Set the content width in pixels, based on the theme's design and stylesheet.
 *
 * Priority 0 to make it available to lower priority callbacks.
 *
 * @global int $content_width
 */
function im_content_width() {
	$GLOBALS['content_width'] = apply_filters( 'im_content_width', 640 );
}
add_action( 'after_setup_theme', 'im_content_width', 0 );

/**
 * Register widget area.
 *
 * @link https://developer.wordpress.org/themes/functionality/sidebars/#registering-a-sidebar
 */
function im_widgets_init() {
	register_sidebar(
		array(
			'name'          => esc_html__( 'Sidebar', 'im' ),
			'id'            => 'sidebar-1',
			'description'   => esc_html__( 'Add widgets here.', 'im' ),
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		)
	);
}
add_action( 'widgets_init', 'im_widgets_init' );

/**
 * Enqueue scripts and styles.
 */
function im_scripts() {
	wp_enqueue_style( 'im-style', get_stylesheet_uri(), array(), _S_VERSION );
	wp_style_add_data( 'im-style', 'rtl', 'replace' );

	wp_enqueue_script( 'im-navigation', get_template_directory_uri() . '/js/navigation.js', array(), _S_VERSION, true );

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'im_scripts' );

/**
 * Implement the Custom Header feature.
 */
require get_template_directory() . '/inc/custom-header.php';

/**
 * Custom template tags for this theme.
 */
require get_template_directory() . '/inc/template-tags.php';

/**
 * Functions which enhance the theme by hooking into WordPress.
 */
require get_template_directory() . '/inc/template-functions.php';

/**
 * Customizer additions.
 */
require get_template_directory() . '/inc/customizer.php';

/**
 * Load Jetpack compatibility file.
 */
if ( defined( 'JETPACK__VERSION' ) ) {
	require get_template_directory() . '/inc/jetpack.php';
}


require_once ABSPATH . 'vendor/autoload.php'; // Подключаем автозагрузчик

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;


function process_xlsx_to_csv() {
    // Путь к входному XLSX и выходному CSV файлам
    $input_file = get_template_directory() . '/input.xlsx';
    $output_file = get_template_directory() . '/output.csv';

    // Проверяем существование входного файла
    if (!file_exists($input_file)) {
        echo "Ошибка: входной файл не найден.";
        return;
    }

    try {
        // Загружаем входной XLSX-файл
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($input_file);

        // Получаем данные из активного листа
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray(null, true, true, true); // Включаем ключи по буквам столбцов

        $result = [];

        // Перебираем строки и извлекаем данные из нужных столбцов
        foreach ($rows as $row) {
            // Пропускаем пустые строки
            if (empty(array_filter($row))) {
                continue;
            }

            // Извлекаем данные из столбцов
            $col1 = $row['A'] ?? ''; // Столбец 1 (Товар)
            $col6 = $row['F'] ?? ''; // Столбец 6 (Код)
            $col7 = $row['G'] ?? ''; // Столбец 7 (Единица измерения)
            $col8 = $row['H'] ?? ''; // Столбец 8 (Цена)

            // Удаляем пробелы и форматируем цену (если нужно)
            $col8 = str_replace(',', '.', $col8); // Заменяем запятую на точку в числах
            $col8 = is_numeric($col8) ? number_format((float)$col8, 2, '.', '') : $col8;

            // Добавляем в результат только непустые строки
            if (!empty($col1) && !empty($col8)) {
                $result[] = [
                    $col1, $col6, $col7, $col8
                ];
            }
        }

        // Создаем новый файл для записи результата
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Добавляем заголовки
        $sheet->fromArray([['Товар', 'Код', 'Единица измерения', 'Цена']], null, 'A1');

        // Добавляем обработанные данные
        $sheet->fromArray($result, null, 'A2');

        // Сохраняем файл в формате CSV
        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Csv');
        $writer->setDelimiter(',');
        $writer->setEnclosure('"');
        $writer->setUseBOM(true); // Добавляем BOM для UTF-8
        $writer->save($output_file);

        echo "Обработка завершена. Результат сохранен в output.csv.";
    } catch (Exception $e) {
        echo "Ошибка при обработке файла: " . $e->getMessage();
    }
}

// Регистрация обработчика
add_action('admin_post_process_xlsx', 'process_xlsx_to_csv');

// Регистрация action для фильтрации товаров
function filter_products_by_csv_action() {
    // Вызываем функцию для фильтрации CSV
    filter_csv_files();

    // Перенаправляем пользователя обратно на страницу после выполнения действия
    wp_redirect(home_url()); // Замените на нужный URL (например, страницу с результатами)
    exit;
}

// Регистрация обработчика для admin-post (если пользователь авторизован)
add_action('admin_post_filter_products_by_csv', 'filter_products_by_csv_action');

// Если действие вызывается не через админку, а через фронтенд (для неавторизованных пользователей)
add_action('admin_post_nopriv_filter_products_by_csv', 'filter_products_by_csv_action');

// Функция для фильтрации CSV файлов
function filter_csv_files() {
    // Путь к входным файлам и файлу для вывода
    $output_file = get_template_directory() . '/output.csv';    // Основной CSV файл с данными
    $filter_file = get_template_directory() . '/filter.csv';    // Файл с фильтрами (товары)
    $filtered_output_file = get_template_directory() . '/filtered_output.csv'; // Отфильтрованный CSV файл

    // Проверка на существование файлов
    if (!file_exists($output_file) || !file_exists($filter_file)) {
        echo "Ошибка: один или оба входных файла не найдены.";
        return;
    }

    // Чтение filter.csv (содержит наименования товаров для фильтрации)
    $filter_products = [];
    if (($handle = fopen($filter_file, 'r')) !== FALSE) {
        // Пропускаем заголовок
        fgetcsv($handle);

        // Чтение наименований товаров из filter.csv (предполагается, что наименования товаров в 1-м столбце)
        while (($data = fgetcsv($handle)) !== FALSE) {
            $filter_products[] = trim($data[0]);  // Наименования товаров в 1-м столбце
        }
        fclose($handle);
    } else {
        echo "Ошибка при открытии файла фильтра $filter_file.";
        return;
    }

    // Чтение output.csv и фильтрация данных
    $filtered_rows = [];
    if (($handle = fopen($output_file, 'r')) !== FALSE) {
        $headers = fgetcsv($handle); // Чтение заголовков
        $filtered_rows[] = $headers; // Добавление заголовков в новый файл

        // Чтение данных из output.csv
        while (($data = fgetcsv($handle)) !== FALSE) {
            $product_name = trim($data[0]); // Наименование товара в 1-м столбце

            // Если наименование товара присутствует в filter.csv, добавляем строку в отфильтрованный список
            if (in_array($product_name, $filter_products)) {
                $filtered_rows[] = $data;
            }
        }
        fclose($handle);
    } else {
        echo "Ошибка при открытии файла $output_file.";
        return;
    }

    // Запись отфильтрованных данных в filtered_output.csv
    if (($handle = fopen($filtered_output_file, 'w')) !== FALSE) {
        foreach ($filtered_rows as $filtered_row) {
            fputcsv($handle, $filtered_row); // Записываем каждую строку в файл
        }
        fclose($handle);
        echo "Данные успешно отфильтрованы и записаны в $filtered_output_file.";
    } else {
        echo "Ошибка при записи в файл $filtered_output_file.";
    }
}






