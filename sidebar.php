<aside id="secondary" class="widget-area">
    <div class="sidebar-block">
        <h3 class="widget-title">Phim Mới Đề Xuất</h3>
        <?php
        $recommended = amurhin_get_recommended_movies(5);
        if ($recommended->have_posts()) :
            while ($recommended->have_posts()) : $recommended->the_post();
                ?>
                <div class="sidebar-movie-item" style="display: flex; gap: 10px; margin-bottom: 15px;">
                    <a href="<?php the_permalink(); ?>" style="width: 60px;">
                        <img src="<?php echo amurhin_get_movie_thumb_url(get_the_ID()); ?>" style="width: 100%; border-radius: 4px;" alt="<?php the_title(); ?>">
                    </a>
                    <div class="sidebar-movie-info">
                        <h4 style="font-size: 14px; margin: 0;"><a href="<?php the_permalink(); ?>" style="color: #fff; text-decoration: none;"><?php the_title(); ?></a></h4>
                        <div style="font-size: 11px; color: var(--accent);">⭐ <?php echo get_post_meta(get_the_ID(), '_movie_rating', true) ?: '5.0'; ?></div>
                        <div style="font-size: 11px; color: var(--text-muted);"><?php echo number_format(get_post_meta(get_the_ID(), '_movie_views', true) ?: 0); ?> lượt xem</div>
                    </div>
                </div>
                <?php
            endwhile; wp_reset_postdata();
        else :
            echo '<p style="font-size:13px; color:var(--text-muted);">Đang cập nhật...</p>';
        endif;
        ?>
    </div>

    <div class="sidebar-block" style="margin-top: 30px;">
        <h3 class="widget-title">Lịch Chiếu Phim</h3>
        <div class="schedule-sidebar" style="background: var(--bg-card); padding: 15px; border-radius: 8px; border-left: 3px solid var(--primary);">
            <?php
            $schedule_query = new WP_Query(array(
                'post_type' => 'movie',
                'posts_per_page' => 5,
                'meta_query' => array(
                    array(
                        'key' => '_movie_schedule',
                        'value' => '',
                        'compare' => '!='
                    )
                )
            ));
            if ($schedule_query->have_posts()) :
                while ($schedule_query->have_posts()) : $schedule_query->the_post();
                    $s = get_post_meta(get_the_ID(), '_movie_schedule', true);
                    ?>
                    <div class="schedule-item" style="margin-bottom: 10px; font-size: 13px;">
                        <span style="color: var(--primary); font-weight: bold;">[<?php echo esc_html($s); ?>]</span>
                        <a href="<?php the_permalink(); ?>" style="color: #fff; text-decoration: none;"><?php the_title(); ?></a>
                    </div>
                    <?php
                endwhile; wp_reset_postdata();
            else :
                echo '<p style="font-size:13px; color:var(--text-muted);">Chưa có lịch chiếu...</p>';
            endif;
            ?>
        </div>
    </div>

    <?php if ( is_active_sidebar( 'movie-sidebar' ) ) : ?>
        <div style="margin-top: 30px;">
            <?php dynamic_sidebar( 'movie-sidebar' ); ?>
        </div>
    <?php endif; ?>
</aside>

<style>
.widget-title {
    font-size: 18px;
    color: #fff;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 2px solid var(--primary);
    display: inline-block;
}
.sidebar-movie-item:hover h4 a {
    color: var(--primary) !important;
}
</style>
