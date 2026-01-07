jQuery(document).ready(function ($) {
    // 1. Increment Real View on page load (if single movie)
    if ($('body').hasClass('single-movie')) {
        let post_id = themePlayer.post_id || $('article.movie-item').data('id') || 0;

        // Try to get ID from localized script or DOM if themePlayer not ready
        if (!post_id && typeof themePlayer !== 'undefined') post_id = themePlayer.post_id;

        if (post_id > 0) {
            setTimeout(function () {
                $.post(themePlayer.ajaxurl, {
                    action: 'amurhin_inc_view',
                    post_id: post_id
                }, function (res) {
                    // Optional: Update view count on page if element exists
                    let data = JSON.parse(res);
                    if (data.success) {
                        $('.real-views-count').text(data.views + ' lượt xem');
                    }
                });
            }, 5000); // Count view after 5 seconds
        }
    }

    // 1.5 Check if already rated on load
    function checkRatedState() {
        let post_id = themePlayer.post_id || $('article.movie-item').data('id') || 0;
        if (!post_id && typeof themePlayer !== 'undefined') post_id = themePlayer.post_id;

        if (post_id > 0 && localStorage.getItem('amurhin_rated_' + post_id)) {
            $('.btn-rating').html('<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg> Đã đánh giá');
            $('.btn-rating').addClass('rated').css('opacity', '0.7');
        }
    }
    checkRatedState();

    // 2. Handle Rating Modal
    $(document).on('click', '.rate-button, .btn-rating', function (e) {
        e.preventDefault();
        $('#amurhin-rating-modal').fadeIn(300);
    });

    $(document).on('click', '.close-rating-modal, .close-rating-modal-text, .rating-overlay', function () {
        $('#amurhin-rating-modal').fadeOut(300);
    });

    // 3. Handle Emoji Vote
    $('.emoji-btn').click(function () {
        let score = $(this).data('score');
        let post_id = $(this).closest('#amurhin-rating-content').data('id');

        // CHECK LOCAL STORAGE
        if (localStorage.getItem('amurhin_rated_' + post_id)) {
            alert('Bạn đã đánh giá phim này rồi!');
            return;
        }

        // Visual feedback
        $('.emoji-btn').removeClass('selected');
        $(this).addClass('selected');

        // Send AJAX
        $.post(themePlayer.ajaxurl, {
            action: 'amurhin_rate_movie',
            post_id: post_id,
            score: score
        }, function (res) {
            let data = JSON.parse(res);
            if (data.success) {
                alert('Cảm ơn bạn đã đánh giá!');

                // SAVE TO STORAGE
                localStorage.setItem('amurhin_rated_' + post_id, score);

                $('#amurhin-rating-modal').fadeOut(300);
                // Update UI rating
                $('.rating-value').text(data.rating);
                $('.rating-count').text('(' + data.count + ' lượt đánh giá)');
            }
        });
    });
});
