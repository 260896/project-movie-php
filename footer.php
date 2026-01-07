<footer style="background: #111; padding: 20px 0; margin-top: 50px; border-top: 1px solid #333;">
    <div class="container">
        <p><?php echo amurhin_get_option('footer_text', '&copy; ' . date('Y') . ' ' . get_bloginfo('name') . '. All Rights Reserved.'); ?></p>
    </div>
</footer>
<div id="lights-off-overlay"></div>
    <div id="amurhin-rating-modal" class="rating-modal" style="display:none;">
        <div class="rating-overlay"></div>
        <div class="rating-content" id="amurhin-rating-content" data-id="<?php echo get_the_ID(); ?>">
            <div class="rating-header">
                <button class="close-rating-modal-text">Đóng</button>
                <h2><?php the_title(); ?></h2>
                <div class="current-rating">
                    <span class="stars">★ <?php echo amurhin_get_rating(get_the_ID()); ?>/5</span>
                    <span class="count">(<?php echo amurhin_get_vote_count(get_the_ID()); ?> lượt đánh giá)</span>
                </div>
            </div>
            <div class="rating-body">
                <p>Bạn đánh giá phim này thế nào?</p>
                <div class="emoji-container">
                    <div class="emoji-item">
                        <button class="emoji-btn" data-score="5">😍</button>
                        <span>Đỉnh nóc</span>
                    </div>
                    <div class="emoji-item">
                        <button class="emoji-btn" data-score="4">😘</button>
                        <span>Hay ho</span>
                    </div>
                    <div class="emoji-item">
                        <button class="emoji-btn" data-score="3">😌</button>
                        <span>Tạm ổn</span>
                    </div>
                    <div class="emoji-item">
                        <button class="emoji-btn" data-score="2">😒</button>
                        <span>Nhạt nhòa</span>
                    </div>
                    <div class="emoji-item">
                        <button class="emoji-btn" data-score="1">🤮</button>
                        <span>Thảm họa</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php wp_footer(); ?>
</body>
</html>
