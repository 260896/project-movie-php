<?php get_header(); ?>

<div class="container" style="display: grid; grid-template-columns: 1fr 300px; gap: 40px; margin-top: 30px;">
    <main id="main-content">
        
        <!-- Showtimes Section -->
        <section class="showtimes-section">
            <div class="showtimes-header">
                <div class="showtimes-title">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="#e67e22" style="margin-right: 5px;"><path d="M19 4h-1V2h-2v2H8V2H6v2H5c-1.11 0-1.99.9-1.99 2L3 20c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V10h14v10zm0-12H5V6h14v2zm-7 5h5v5h-5z"/></svg>
                    <span>Lịch chiếu phim</span>
                </div>
                <a href="#" class="view-all" style="color: var(--text-muted); text-decoration: none; font-size: 13px;">Xem tất cả &raquo;</a>
            </div>

            <div class="showtimes-tabs">
                <?php
                $current_day_slug = strtolower(date('D'));
                $days_of_week = array(
                    'mon' => array('Mon', 'Thứ 2'),
                    'tue' => array('Tue', 'Thứ 3'),
                    'wed' => array('Wed', 'Thứ 4'),
                    'thu' => array('Thu', 'Thứ 5'),
                    'fri' => array('Fri', 'Thứ 6'),
                    'sat' => array('Sat', 'Thứ 7'),
                    'sun' => array('Sun', 'Chủ Nhật'),
                );
                foreach ($days_of_week as $slug => $labels) :
                    $is_active = ($slug == $current_day_slug) ? 'active' : '';
                ?>
                    <div class="showtimes-tab <?php echo $is_active; ?>" data-day="<?php echo $slug; ?>">
                        <span class="day-en"><?php echo $labels[0]; ?></span>
                        <span class="day-vn"><?php echo $labels[1]; ?></span>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="showtimes-content">
                <?php foreach ($days_of_week as $slug => $labels) : 
                    $is_active = ($slug == $current_day_slug) ? 'active' : '';
                    $showtime_query = amurhin_get_movies_by_showtime_day($slug, 6);
                ?>
                    <div class="showtime-grid <?php echo $is_active; ?>" id="grid-<?php echo $slug; ?>">
                        <?php if ($showtime_query->have_posts()) : while ($showtime_query->have_posts()) : $showtime_query->the_post(); 
                            $timestamp = amurhin_get_next_showtime_timestamp(get_the_ID());
                        ?>
                            <article class="movie-item showtime-item" data-time="<?php echo $timestamp; ?>">
                                <div class="movie-poster">
                                    <div class="movie-label"><?php echo get_post_meta(get_the_ID(), '_movie_lang', true) ?: 'HD'; ?></div>
                                    <div class="countdown-badge">
                                        <span class="timer">00:00:00</span>
                                    </div>
                                    <a href="<?php the_permalink(); ?>">
                                        <img src="<?php echo amurhin_get_movie_thumb_url(get_the_ID()); ?>" alt="<?php the_title(); ?>">
                                    </a>
                                </div>
                                <div class="movie-title">
                                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                </div>
                            </article>
                        <?php endwhile; else : ?>
                            <div style="grid-column: 1 / -1; text-align: center; padding: 30px; color: var(--text-muted);">
                                Chưa có lịch chiếu cho ngày này.
                            </div>
                        <?php endif; wp_reset_postdata(); ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <script>
        jQuery(document).ready(function($) {
            $('.showtimes-tab').click(function() {
                $('.showtimes-tab').removeClass('active');
                $(this).addClass('active');
                
                let day = $(this).data('day');
                $('.showtime-grid').removeClass('active');
                $('#grid-' + day).addClass('active');
            });
        });
        </script>
        <?php
        $sections = array(
            'phim-bo' => 'Phim Bộ Mới',
            'phim-le' => 'Phim Lẻ Mới',
            'hoat-hinh' => 'Phim Hoạt Hình',
            'phim-chieu-rap' => 'Phim Chiếu Rạp',
            'tv-show' => 'TV Shows'
        );

        foreach ($sections as $slug => $title) :
            $query = amurhin_get_movies_by_type($slug, 8);
            if ($query->have_posts()) :
        ?>
            <section class="movie-section" style="margin-bottom: 50px;">
                <div class="section-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                    <h2 style="font-size: 24px; border-left: 4px solid var(--primary); padding-left: 15px; margin: 0; color: #fff;"><?php echo $title; ?></h2>
                    <a href="<?php echo get_term_link($slug, 'movie_type'); ?>" style="color: var(--primary); text-decoration: none; font-size: 14px; font-weight: 600;">Xem tất cả &raquo;</a>
                </div>

                <div class="movie-grid" style="grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));">
                    <?php while ($query->have_posts()) : $query->the_post(); ?>
                        
                        <article class="movie-item">
                            <a href="<?php the_permalink(); ?>">
                            <div class="movie-poster">
                                <div class="movie-label">HD</div>
                                    <img src="<?php echo amurhin_get_movie_thumb_url(get_the_ID()); ?>" alt="<?php the_title(); ?>">
                            </div>
                            </a>
                            <div class="movie-title">
                                <a href="<?php the_permalink(); ?>" title="<?php the_title(); ?>"><?php the_title(); ?></a>
                                <div style="font-size: 11px; color: var(--text-muted); margin-top: 5px;">
                                    <?php echo get_post_meta(get_the_ID(), '_movie_views', true) ?: 0; ?> lượt xem
                                </div>
                            </div>
                        </article>
                    <?php endwhile; wp_reset_postdata(); ?>
                </div>
            </section>
        <?php 
            endif;
        endforeach; 
        ?>

        <!-- Hiển thị phim theo quốc gia -->
        <?php 
        $sections_country = array(
            'han-quoc' => 'Phim Hàn Quốc',
            'hong-kong' => 'Phim Hồng Kông',
            'trung-quoc' => 'Phim Trung Quốc',
            'viet-nam' => 'Phim Việt Nam',
            'nhat-ban' => 'Phim Nhật Bản'
        );
        
        foreach ($sections_country as $slug => $title) :
            $query = amurhin_get_movies_by_country($slug, 8);
            if ($query->have_posts()) :
        ?>
            <section class="movie-section" style="margin-bottom: 50px;">
                <div class="section-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                    <h2 style="font-size: 24px; border-left: 4px solid var(--primary); padding-left: 15px; margin: 0; color: #fff;"><?php echo $title; ?></h2>
                    <a href="<?php echo get_term_link($slug, 'country'); ?>" style="color: var(--primary); text-decoration: none; font-size: 14px; font-weight: 600;">Xem tất cả &raquo;</a>
                </div>

                <div class="movie-grid" style="grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));">
                    <?php while ($query->have_posts()) : $query->the_post(); ?>
                        
                        <article class="movie-item">
                            <a href="<?php the_permalink(); ?>">
                            <div class="movie-poster">
                                <div class="movie-label">HD</div>
                                    <img src="<?php echo amurhin_get_movie_thumb_url(get_the_ID()); ?>" alt="<?php the_title(); ?>">
                            </div>
                            </a>
                            <div class="movie-title">
                                <a href="<?php the_permalink(); ?>" title="<?php the_title(); ?>"><?php the_title(); ?></a>
                                <div style="font-size: 11px; color: var(--text-muted); margin-top: 5px;">
                                    <?php echo get_post_meta(get_the_ID(), '_movie_views', true) ?: 0; ?> lượt xem
                                </div>
                            </div>
                        </article>  
                    <?php endwhile; wp_reset_postdata(); ?>
                </div>
            </section>
        <?php 
            endif;
        endforeach; 
        ?>
    </main>

    <?php get_sidebar(); ?>
</div>

<?php get_footer(); ?>
