<?php
/*
Template Name: Главная
*/
get_header();
?>
<a href="<?php echo admin_url('admin-post.php?action=process_xlsx'); ?>">Обработать xlsx</a>


<div class="products-wrapper">
    <?php
    // Параметры для WP_Query
    $args = array(
        'post_type' => 'product', // Тип записи - товар
        'post_status' => 'publish', // Только опубликованные товары
        'posts_per_page' => -1, // Выводить все товары
    );

    // Запрос WP_Query
    $query = new WP_Query($args);

    if ($query->have_posts()) {
        echo '<ul class="products-list">';
        while ($query->have_posts()) {
            $query->the_post();

            global $product; // Глобальная переменная товара WooCommerce

            // Выводим HTML разметку для каждого товара
            ?>
            <li class="product-item">
                <a href="<?php the_permalink(); ?>" class="product-link">
                    <?php
                    // Миниатюра товара
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
        echo '</ul>';
    } else {
        echo '<p>Товары не найдены.</p>';
    }

    // Восстановление глобальной переменной $post
    wp_reset_postdata();
    ?>
</div>

<?php
get_footer();
?>
