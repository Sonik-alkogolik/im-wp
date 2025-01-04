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


// require_once ABSPATH . 'vendor/autoload.php';

// use PhpOffice\PhpSpreadsheet\IOFactory;
// use PhpOffice\PhpSpreadsheet\Spreadsheet;

// // Подключение фильтров
 require get_template_directory() . '/xoz_template/filter_xoz_torg.php';
// require get_template_directory() . '/sklad_template/filter_sklad.php';



function load_more_script() {
    wp_enqueue_script('jquery');

    // Подключение вашего JS-файла
    wp_enqueue_script(
        'load-product-js', // Уникальный идентификатор скрипта
        get_template_directory_uri() . '/assets/load_product.js', // Путь к JS-файлу
        array('jquery'), // Зависимости (в данном случае jQuery)
        null, // Версия файла (null для отключения версии)
        true // Подключение в футере
    );

    // Передача данных из PHP в JS
    wp_localize_script('load-product-js', 'ajaxParams', array(
        'ajaxUrl' => admin_url('admin-ajax.php'), // URL для AJAX-запросов
    ));
}
add_action('wp_enqueue_scripts', 'load_more_script');

function load_more_products() {
    $offset = isset($_POST['offset']) ? intval($_POST['offset']) : 0; // Смещение
    $posts_per_page = isset($_POST['posts_per_page']) ? intval($_POST['posts_per_page']) : 50; // Количество товаров

    $args = array(
        'post_type'      => 'product',
        'post_status'    => 'publish',
        'posts_per_page' => $posts_per_page,
        'offset'         => $offset, // Смещение
    );

    $query = new WP_Query($args);

    if ($query->have_posts()) {
        ob_start(); // Начало буферизации
        while ($query->have_posts()) {
            $query->the_post();
            global $product;
            ?>
            <li class="product-item">
                <a href="<?php the_permalink(); ?>" class="product-link">
                    <?php
                    if (has_post_thumbnail()) {
                        the_post_thumbnail('woocommerce_thumbnail');
                    }
                    ?>
                    <h2 class="product-title"><?php the_title(); ?></h2>
                    <span class="product-price"><?php echo $product->get_price_html(); ?></span>
                </a>
            </li>
            <?php
        }
        $content = ob_get_clean(); // Получаем HTML из буфера
        wp_send_json_success($content);
    } else {
        wp_send_json_error('Больше товаров нет.');
    }

    wp_die();
}
add_action('wp_ajax_load_more_products', 'load_more_products');
add_action('wp_ajax_nopriv_load_more_products', 'load_more_products');


// function generate_unique_id() {
//     error_log("Функция generate_unique_id() вызвана");
//     return uniqid('id_', true);
// }

// function handle_error($message) {
//     error_log("Ошибка: $message");
//     echo "Ошибка: " . $message;
//     exit;
// }

// function check_file_exists($file_path) {
//     error_log("Проверка наличия файла: $file_path");
//     if (!file_exists($file_path)) {
//         handle_error("Файл $file_path не найден.");
//     }
// }

// function process_xlsx_to_csv() {
//     error_log("Начало функции process_xlsx_to_csv");

//     $input_file = get_template_directory() . '/input.xlsx';
//     $output_file = get_template_directory() . '/output.csv';

//     error_log("Пути файлов: input_file = $input_file, output_file = $output_file");

//     check_file_exists($input_file);

//     try {
//         error_log("Загрузка файла XLSX: $input_file");
//         $spreadsheet = IOFactory::load($input_file);
//         $worksheet = $spreadsheet->getActiveSheet();
//         $rows = $worksheet->toArray(null, true, true, true);

//         error_log("Количество строк в файле: " . count($rows));

//         $result = [];
//         $sku_check = []; // Для проверки дублирования SKU

//         foreach ($rows as $index => $row) {
//             error_log("Обработка строки #$index: " . json_encode($row));
//             if (empty(array_filter($row))) {
//                 error_log("Строка #$index пропущена (пустая)");
//                 continue;
//             }

//             $product_name = trim($row['A'] ?? '');
//             $sku = trim($row['F'] ?? ''); // Столбец с SKU
//             $unit = $row['G'] ?? '';
//             $price = $row['H'] ?? '';

//             // Форматируем цену
//             $price = str_replace(',', '.', $price);
//             $price = is_numeric($price) ? number_format((float)$price, 2, '.', '') : '';

//             // Генерация SKU, если его нет
//             if (empty($sku)) {
//                 $sku = 'sku_' . substr(md5($product_name), 0, 8);
//                 error_log("Генерируем SKU для товара '$product_name': $sku");
//             }

//             // Проверка на уникальность SKU
//             if (!empty($product_name) && !empty($price)) {
//                 if (!isset($sku_check[$sku])) {
//                     $sku_check[$sku] = true;
//                     $result[] = [$sku, $product_name, $unit, $price];
//                     error_log("Добавлена строка: SKU = $sku, Name = $product_name, Price = $price");
//                 } else {
//                     error_log("Дублирующийся SKU пропущен: $sku");
//                 }
//             }
//         }

//         // Создаем CSV
//         error_log("Создание и сохранение файла CSV: $output_file");
//         $spreadsheet = new Spreadsheet();
//         $sheet = $spreadsheet->getActiveSheet();
//         $sheet->fromArray([['SKU', 'Товар', 'Единица измерения', 'Цена']], null, 'A1');
//         $sheet->fromArray($result, null, 'A2');

//         $writer = IOFactory::createWriter($spreadsheet, 'Csv');
//         $writer->setDelimiter(',');
//         $writer->setEnclosure('"');
//         $writer->setUseBOM(true);
//         $writer->save($output_file);

//         error_log("Файл CSV успешно сохранен: $output_file");
//         echo "Обработка завершена. Результат сохранен в output.csv.";
//         echo '<a href="/">На главную</a>';
//     } catch (Exception $e) {
//         error_log("Ошибка в process_xlsx_to_csv: " . $e->getMessage());
//         handle_error($e->getMessage());
//     }
// }

// function filter_csv_files() {
//     error_log("Начало функции filter_csv_files");

//     $output_file = get_template_directory() . '/output.csv';
//     $filter_file = get_template_directory() . '/filter.csv';
//     $filtered_output_file = get_template_directory() . '/filtered_output.csv';

//     error_log("Файлы: output_file = $output_file, filter_file = $filter_file");

//     check_file_exists($output_file);
//     check_file_exists($filter_file);

//     // Читаем данные из output.csv
//     $output_data = array_map('str_getcsv', file($output_file));
//     $output_headers = array_shift($output_data);

//     // Читаем данные из filter.csv
//     $filter_data = array_map('str_getcsv', file($filter_file));
//     array_shift($filter_data); // Убираем заголовки

//     error_log("Фильтрация строк из output.csv на основе filter.csv");

//     // Функция для очистки строк
//     function clean_value($value) {
//         return strtolower(trim(preg_replace('/\s+/', ' ', $value)));
//     }

//     // Собираем массив фильтров
//     $filter_sku_or_names = array_map(function ($row) {
//         return clean_value($row[0]); // Столбец с фильтром (например, SKU или имя)
//     }, $filter_data);

//     $filtered_rows = [$output_headers];
//     foreach ($output_data as $index => $row) {
//         error_log("Проверка строки #$index: " . json_encode($row));

//         // Очистка SKU и имени товара
//         $sku_or_name = clean_value($row[0]); // SKU
//         $product_name = isset($row[1]) ? clean_value($row[1]) : ''; // Название товара

//         // Проверяем на совпадение SKU или имени товара
//         if (in_array($sku_or_name, $filter_sku_or_names) || in_array($product_name, $filter_sku_or_names)) {
//             $filtered_rows[] = $row;
//             error_log("Строка добавлена: " . json_encode($row));
//         }
//     }

//     if (count($filtered_rows) <= 1) {
//         error_log("Фильтрация не нашла совпадений.");
//         handle_error("Фильтрация не нашла совпадений.");
//     }

//     // Сохраняем файл
//     error_log("Сохранение отфильтрованных данных в: $filtered_output_file");
//     $fp = fopen($filtered_output_file, 'w');
//     foreach ($filtered_rows as $row) {
//         fputcsv($fp, $row);
//     }
//     fclose($fp);

//     error_log("Фильтрация завершена. Результат сохранен в: $filtered_output_file");
//     echo 'Фильтрация завершена. Результат сохранен в ' . $filtered_output_file;
//     echo '<a href="/">На главную</a>';
//     exit;
// }

// // Регистрация обработчика для обработки XLSX
// add_action('admin_post_process_xlsx', 'process_xlsx_to_csv');
// add_action('admin_post_nopriv_process_xlsx', 'process_xlsx_to_csv');

// // Регистрация обработчика для фильтрации CSV (для авторизованных пользователей)
// add_action('admin_post_filter_products_by_csv', 'filter_csv_files');

// // Регистрация обработчика для фильтрации CSV (для неавторизованных пользователей)
// add_action('admin_post_nopriv_filter_products_by_csv', 'filter_csv_files');



// function generate_unique_id() {
//     error_log("Функция generate_unique_id() вызвана");
//     return uniqid('id_', true);
// }

// function handle_error($message) {
//     error_log("Ошибка: $message");
//     echo "Ошибка: " . $message;
//     exit;
// }

// function check_file_exists($file_path) {
//     error_log("Проверка наличия файла: $file_path");
//     if (!file_exists($file_path)) {
//         handle_error("Файл $file_path не найден.");
//     }
// }


// function get_template_directory_path($template_name) {
//     $base_path = get_template_directory();
//     switch ($template_name) {
//         case 'xoz_template':
//             return $base_path . '/xoz_template';
//         case 'sklad_template':
//             return $base_path . '/sklad_template';
//         default:
//             handle_error("Неизвестное имя шаблона: $template_name");
//     }
// }

// function process_xlsx_to_csv($template_name = 'xoz_template') {
//     error_log("Начало функции process_xlsx_to_csv для шаблона: $template_name");

//     $template_path = get_template_directory_path($template_name);
//     $input_file = $template_path . '/input.xlsx';
//     $output_file = $template_path . '/' . $template_name . '_output.csv';

//     error_log("Пути файлов: input_file = $input_file, output_file = $output_file");

//     check_file_exists($input_file);

//     try {
//         error_log("Загрузка файла XLSX: $input_file");
//         $spreadsheet = IOFactory::load($input_file);
//         $worksheet = $spreadsheet->getActiveSheet();
//         $rows = $worksheet->toArray(null, true, true, true);

//         error_log("Количество строк в файле: " . count($rows));

//         $result = [];
//         $sku_check = [];

//         foreach ($rows as $index => $row) {
//             if (empty(array_filter($row))) {
//                 continue;
//             }

//             $product_name = trim($row['A'] ?? '');
//             $sku = trim($row['F'] ?? '');
//             $unit = $row['G'] ?? '';
//             $price = $row['H'] ?? '';

//             $price = str_replace(',', '.', $price);
//             $price = is_numeric($price) ? number_format((float)$price, 2, '.', '') : '';

//             if (empty($sku)) {
//                 $sku = 'sku_' . substr(md5($product_name), 0, 8);
//             }

//             if (!empty($product_name) && !empty($price)) {
//                 if (!isset($sku_check[$sku])) {
//                     $sku_check[$sku] = true;
//                     $result[] = [$sku, $product_name, $unit, $price];
//                 }
//             }
//         }

//         error_log("Создание и сохранение файла CSV: $output_file");
//         $spreadsheet = new Spreadsheet();
//         $sheet = $spreadsheet->getActiveSheet();
//         $sheet->fromArray([['SKU', 'Товар', 'Единица измерения', 'Цена']], null, 'A1');
//         $sheet->fromArray($result, null, 'A2');

//         $writer = IOFactory::createWriter($spreadsheet, 'Csv');
//         $writer->setDelimiter(',');
//         $writer->setEnclosure('"');
//         $writer->setUseBOM(true);
//         $writer->save($output_file);

//         error_log("Файл CSV успешно сохранен: $output_file");
//         echo "Обработка завершена. Результат сохранен в " . basename($output_file);
//     } catch (Exception $e) {
//         handle_error($e->getMessage());
//     }
// }

// function filter_csv_files($template_name = 'xoz_template') {
//     error_log("Начало функции filter_csv_files для шаблона: $template_name");

//     $template_path = get_template_directory_path($template_name);
//     $output_file = $template_path . '/' . $template_name . '_output.csv';
    
//     // Исправляем на правильное имя файла фильтра
//     $filter_file = $template_path . '/' . $template_name . '_filter.csv'; 
//     $filter_file = $template_path . '/xoz_filter.csv';

//     $filtered_output_file = $template_path . '/' . $template_name . '_filtered_output.csv';

//     error_log("Файлы: output_file = $output_file, filter_file = $filter_file");

//     check_file_exists($output_file);
//     check_file_exists($filter_file);

//     $output_data = array_map('str_getcsv', file($output_file));
//     $output_headers = array_shift($output_data);

//     $filter_data = array_map('str_getcsv', file($filter_file));
//     array_shift($filter_data);

//     $filter_sku_or_names = array_map(function ($row) {
//         return strtolower(trim(preg_replace('/\s+/', ' ', $row[0])));
//     }, $filter_data);

//     $filtered_rows = [$output_headers];
//     foreach ($output_data as $row) {
//         $sku_or_name = strtolower(trim($row[0]));
//         $product_name = isset($row[1]) ? strtolower(trim($row[1])) : '';

//         if (in_array($sku_or_name, $filter_sku_or_names) || in_array($product_name, $filter_sku_or_names)) {
//             $filtered_rows[] = $row;
//         }
//     }

//     if (count($filtered_rows) <= 1) {
//         handle_error("Фильтрация не нашла совпадений.");
//     }

//     error_log("Сохранение отфильтрованных данных в: $filtered_output_file");
//     $fp = fopen($filtered_output_file, 'w');
//     foreach ($filtered_rows as $row) {
//         fputcsv($fp, $row);
//     }
//     fclose($fp);

//     echo 'Фильтрация завершена. Результат сохранен в ' . basename($filtered_output_file);
//     exit;
// }


// add_action('admin_post_process_xlsx', function () {
//     if (!isset($_GET['template_name'])) {
//         handle_error("Не передан параметр шаблона.");
//         return; 
//     }
//     $template_name = $_GET['template_name']; 
//     process_xlsx_to_csv($template_name);
// });

// add_action('admin_post_filter_products_by_csv', function () {
//     if (!isset($_GET['template_name'])) {
//         handle_error("Не передан параметр шаблона.");
//         return;
//     }
//     $template_name = $_GET['template_name'];
//     filter_csv_files($template_name);
// });


// // Регистрация обработчика для обработки XLSX
// add_action('admin_post_process_xlsx', 'process_xlsx_to_csv');
// add_action('admin_post_nopriv_process_xlsx', 'process_xlsx_to_csv');

// // Регистрация обработчика для фильтрации CSV (для авторизованных пользователей)
// add_action('admin_post_filter_products_by_csv', 'filter_csv_files');

// // Регистрация обработчика для фильтрации CSV (для неавторизованных пользователей)
// add_action('admin_post_nopriv_filter_products_by_csv', 'filter_csv_files');