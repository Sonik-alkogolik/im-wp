<?php
/*
Template Name: Главная
*/
get_header();
?>
<div class="wrap">

<?php
if (current_user_can('administrator')) { 
    ?>
    <div class="admin-links">
        <!-- Кнопки для xoz_template -->
        <a target="_blank" href="<?php echo admin_url('admin-post.php?action=process_xlsx&template_name=xoz_template'); ?>">Обработать xlsx для xoz_template</a>
        <a target="_blank" href="<?php echo admin_url('admin-post.php?action=filter_products_by_csv&template_name=xoz_template'); ?>">Отфильтровать позиции для xoz_template</a>

        <!-- Кнопки для sklad_template -->
        <a target="_blank" href="<?php echo admin_url('admin-post.php?action=process_xlsx&template_name=sklad_template'); ?>">Обработать xlsx для sklad_template</a>
        <a target="_blank" href="<?php echo admin_url('admin-post.php?action=filter_products_by_csv&template_name=sklad_template'); ?>">Отфильтровать позиции для sklad_template</a>
    </div>
    <?php
}
?>


           
        </div>

        <div class="products-wrapper">
    <ul class="products-list">
        <?php
        $args = array(
            'post_type'      => 'product',
            'post_status'    => 'publish',
            'posts_per_page' => 50,
        );

        $query = new WP_Query($args);

        if ($query->have_posts()) {
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
        } else {
            echo '<p>Товары не найдены.</p>';
        }

        wp_reset_postdata();
        ?>
    </ul>
</div>

<div class="load-more-wrapper">
        <button id="load-more" data-page="1">Загрузить ещё</button>
    </div>

<?php
get_footer();
?>
