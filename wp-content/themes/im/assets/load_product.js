jQuery(document).ready(function ($) {
    let offset = 50; // Начальное смещение (первые 50 товаров уже загружены)
    let postsPerPage = 50; // Сколько товаров подгружать за один раз

    $('#load-more').on('click', function () {
        let button = $(this);
        button.prop('disabled', true).text('Загрузка...');

        $.ajax({
            url: ajaxParams.ajaxUrl,
            type: 'POST',
            data: {
                action: 'load_more_products', // Название действия
                offset: offset, // Передаём текущее смещение
                posts_per_page: postsPerPage, // Количество товаров на запрос
            },
            success: function (response) {
                if (response.success) {
                    $('.products-list').append(response.data); // Добавляем новые товары
                    offset += postsPerPage; // Увеличиваем смещение
                    button.prop('disabled', false).text('Загрузить ещё');
                } else {
                    button.text('Больше товаров нет').prop('disabled', true);
                }
            },
            error: function () {
                button.prop('disabled', false).text('Загрузить ещё');
            },
        });
    });
});
