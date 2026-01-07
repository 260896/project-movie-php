<?php
$banner_url = amurhin_get_movie_banner_url(get_the_ID());
$thumb_url = amurhin_get_movie_thumb_url(get_the_ID());

$views = get_post_meta( get_the_ID(), '_movie_views', true ) ?: 0;
$rating = get_post_meta( get_the_ID(), '_movie_rating', true ) ?: 5;
$quality = get_post_meta( get_the_ID(), '_movie_quality', true ) ?: 'Full HD';
$lang = get_post_meta( get_the_ID(), '_movie_lang', true ) ?: 'Vietsub';
$schedule = get_post_meta( get_the_ID(), '_movie_schedule', true );
?>

<div class="movie-banner-wrap">
    <div class="movie-banner-bg" style="background-image: url('<?php echo $banner_url; ?>');"></div>
    <div class="container movie-banner-content">
        <div class="movie-info">
            <div class="info-poster">
                <?php if ( $thumb_url ) : ?>
                    <img src="<?php echo $thumb_url; ?>" alt="<?php the_title(); ?>">
                <?php else : ?>
                    <img src="https://via.placeholder.com/300x450?text=No+Poster" alt="">
                <?php endif; ?>
            </div>
            <div class="info-details">
                <div class="movie-rating-badge" style="background: var(--accent); color: #000; display: inline-block; padding: 2px 10px; border-radius: 4px; font-weight: bold; margin-bottom: 10px;">
                    ⭐ <?php echo $rating; ?> / 5
                </div>
                <h1><?php the_title(); ?></h1>
                
                <div class="meta-row">
                    <div class="meta-item">
                        <strong>Thể loại</strong>
                        <?php echo get_the_term_list( get_the_ID(), 'genre', '', ', ' ); ?>
                    </div>
                    <div class="meta-item">
                        <strong>Loại phim</strong>
                        <?php echo get_the_term_list( get_the_ID(), 'movie_type', '', ', ' ); ?>
                    </div>
                    <div class="meta-item">
                        <strong>Quốc gia</strong>
                        <?php echo get_the_term_list( get_the_ID(), 'country', '', ', ' ); ?>
                    </div>
                    <div class="meta-item">
                        <strong>Năm</strong>
                        <?php echo get_the_term_list( get_the_ID(), 'release_year', '', ', ' ); ?>
                    </div>
                </div>

                <div class="meta-row" style="margin-bottom: 30px;">
                    <div class="meta-item">
                        <strong>Chất lượng</strong>
                        <span style="color: var(--accent);"><?php echo $quality; ?></span>
                    </div>
                    <div class="meta-item">
                        <strong>Ngôn ngữ</strong>
                        <span style="color: var(--text-main);"><?php echo $lang; ?></span>
                    </div>
                    <div class="meta-item">
                        <strong>Lượt xem</strong>
                        <span style="color: var(--text-main);"><?php echo number_format($views); ?></span>
                    </div>
                </div>

                <?php if($schedule) : ?>
                <div class="movie-schedule-info" style="margin-bottom: 25px; background: rgba(231, 76, 60, 0.15); padding: 10px 15px; border-left: 3px solid var(--primary); border-radius: 4px;">
                    <i style="color: var(--primary);">📅 Lịch chiếu:</i> <?php echo $schedule; ?>
                </div>
                <?php endif; ?>

                <div class="description" style="max-width: 600px; margin-bottom: 30px;">
                    <?php the_excerpt(); ?>
                </div>

                <?php
                $first_watch_url = rtrim(get_permalink(), '/') . '/sv1-tap1.html';
                ?>
                <a href="<?php echo esc_url($first_watch_url); ?>" class="btn-watch">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg>
                    XEM PHIM NGAY
                </a>
            </div>
        </div>
    </div>
</div>

<div class="container">
    <div class="section-header" style="margin-bottom: 20px;">
        <h3 style="font-size: 20px; border-left: 4px solid var(--primary); padding-left: 15px;">Nội dung chi tiết</h3>
    </div>
    <div class="movie-content" style="color: var(--text-muted); font-size: 16px; line-height: 1.8;">
        <?php the_content(); ?>
    </div>
    
    <div class="meta-details" style="margin-top: 40px; padding: 25px; background: var(--bg-card); border-radius: 15px; display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
        <div>
            <p><strong>Diễn viên:</strong> <?php echo get_the_term_list( get_the_ID(), 'actor', '', ', ' ); ?></p>
            <p><strong>Đạo diễn:</strong> <?php echo get_the_term_list( get_the_ID(), 'director', '', ', ' ); ?></p>
        </div>
        <div>
            <p><strong>Từ khóa:</strong> <?php echo get_the_term_list( get_the_ID(), 'movie_tag', '', ', ' ); ?></p>
        </div>
    </div>
</div>
