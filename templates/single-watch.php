<?php
$post_id = get_the_ID();

// Get episodes from Custom Post Type (Preferred)
$episode_query = new WP_Query(array(
    'post_type' => 'episode',
    'posts_per_page' => -1,
    'meta_key' => '_episode_name', // You might want to sort by episode name/number
    'orderby' => 'meta_value',
    'order' => 'ASC',
    'meta_query' => array(
        array('key' => '_episode_movie_id', 'value' => $post_id)
    )
));

$servers = [];
if ($episode_query->have_posts()) {
    while ($episode_query->have_posts()) {
        $episode_query->the_post();
        $s_name = get_post_meta(get_the_ID(), '_episode_server', true) ?: 'Default';
        $e_name = get_post_meta(get_the_ID(), '_episode_name', true) ?: get_the_title();
        $servers[$s_name]['name'] = $s_name;
        $servers[$s_name]['data'][] = [
            'name' => $e_name,
            'link' => get_post_meta(get_the_ID(), '_episode_link', true),
            'type' => get_post_meta(get_the_ID(), '_episode_type', true) ?: 'link'
        ];
    }
    wp_reset_postdata();
    $servers = array_values($servers);
} else {
    // Fallback to legacy JSON meta
    $episodes_json = get_post_meta($post_id, '_movie_episodes', true);
    $servers = json_decode($episodes_json, true) ?: [];
}
$current_sv = max(1, intval(get_query_var('sv') ?: 1));
$current_tap = max(1, intval(get_query_var('tap') ?: 1));

// For the player, indices are 0-based
$player_sv = $current_sv - 1;
$player_ep = $current_tap - 1;
?>

<div class="container">
    <div class="player-wrapper-outer">
        <div class="player-container">
            <div id="player-wrapper" style="width: 100%; height: 100%; position: relative; background: #000;"
                 data-post-id="<?php echo $post_id; ?>"
                 data-sv="<?php echo $player_sv; ?>"
                 data-ep="<?php echo $player_ep; ?>">
                <div id="loading-player" style="color: var(--primary); text-align: center; padding-top: 20%; font-weight: bold; font-size: 20px;">
                    ⚡ ĐANG KẾT NỐI MÁY CHỦ...
                </div>
            </div>
        </div>
    </div>

    <div class="player-controls-bar">
        <div class="controls-left">
            <div class="control-item btn-watchlist" id="btn-follow" 
                 data-id="<?php echo get_the_ID(); ?>"
                 data-title="<?php the_title(); ?>"
                 data-thumb="<?php echo amurhin_get_movie_thumb_url(get_the_ID()); ?>"
                 data-url="<?php the_permalink(); ?>">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
                Theo dõi
            </div>
            <div class="control-item btn-rating" id="btn-rate">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                Đánh giá
            </div>
        </div>
        <div class="controls-right">
            <div class="control-item">
                Chuyển tập
                <label class="switch">
                    <input type="checkbox" id="auto-next" checked>
                    <span class="slider"></span>
                </label>
            </div>
            <div class="control-item">
                Bỏ qua intro
                <label class="switch">
                    <input type="checkbox" id="skip-intro">
                    <span class="slider"></span>
                </label>
            </div>
            <div class="control-item" id="btn-prev">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M6 6h2v12H6zm3.5 6l8.5 6V6z"/></svg>
                Tập trước
            </div>
            <div class="control-item" id="btn-next">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M6 18l8.5-6L6 6zm9-12h2v12h-2z"/></svg>
                Tập tiếp
            </div>
            <div class="control-item" id="btn-lights">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M12 7c-2.76 0-5 2.24-5 5s2.24 5 5 5 5-2.24 5-5-2.24-5-5-5zM2 13h2c.55 0 1-.45 1-1s-.45-1-1-1H2c-.55 0-1 .45-1 1s.45 1 1 1zm18 0h2c.55 0 1-.45 1-1s-.45-1-1-1h-2c-.55 0-1 .45-1 1s.45 1 1 1zM11 2v2c0 .55.45 1 1 1s1-.45 1-1V2c0-.55-.45-1-1-1s-1 .45-1 1zm0 18v2c0 .55.45 1 1 1s1-.45 1-1v-2c0-.55-.45-1-1-1s-1 .45-1 1zM5.99 4.58c-.39-.39-1.03-.39-1.41 0s-.39 1.03 0 1.41l1.06 1.06c.39.39 1.03.39 1.41 0s.39-1.03 0-1.41L5.99 4.58zm12.37 12.37c-.39-.39-1.03-.39-1.41 0s-.39 1.03 0 1.41l1.06 1.06c.39.39 1.03.39 1.41 0s.39-1.03 0-1.41l-1.06-1.06zm1.06-12.37c-.39-.39-1.02-.39-1.41 0l-1.06 1.06c-.39.39-.39 1.02 0 1.41s1.02.39 1.41 0l1.06-1.06c.39-.38.39-1.02 0-1.41zm-12.37 12.37c-.39-.39-1.02-.39-1.41 0l-1.06 1.06c-.39.39-.39 1.02 0 1.41s1.02.39 1.41 0l1.06-1.06c.39-.38.39-1.02 0-1.41z"/></svg>
                Tắt đèn
            </div>
        </div>
    </div>

    <div class="watch-meta" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <h1 style="font-size: 28px; margin: 0;"><?php the_title(); ?> <span style="color: var(--primary);"> - <span id="current-ep-name">Đang tải...</span></span></h1>
        <div class="player-actions" style="display: flex; gap: 10px;">
            <button class="btn-action" style="background: var(--bg-card); border: 1px solid #333; color: #fff; padding: 8px 15px; border-radius: 5px; cursor: pointer;">Báo lỗi</button>
        </div>
    </div>
    
    <div class="episode-box" style="background: var(--bg-card); padding: 25px; border-radius: 15px;">
        <?php if ( !empty($servers) ) : 
            $movie_base_url = get_permalink($post_id);
            $movie_title = get_the_title($post_id);
            foreach ( $servers as $s_idx => $server ) : 
                $s_num = $s_idx + 1;
            ?>
            <div class="server-group" style="margin-bottom: 25px;">
                <h3 class="server-title" style="margin-top: 0; font-size: 16px; color: var(--primary); margin-bottom: 15px; display: flex; align-items: center; gap: 10px;">
                    <span style="display:inline-block; width:10px; height:10px; background:var(--primary); border-radius:2px;"></span>
                    #<?php echo esc_html($server['name']); ?>
                </h3>
                <div class="episode-grid">
                    <?php if ( !empty($server['data']) ) : foreach ( $server['data'] as $e_idx => $ep ) : 
                        $e_num = $e_idx + 1;
                        $ep_url = rtrim($movie_base_url, '/') . "/sv{$s_num}-tap{$e_num}.html";
                        $is_active = ($s_idx === $player_sv && $e_idx === $player_ep);
                    ?>
                        <a href="<?php echo esc_url($ep_url); ?>" class="episode-item <?php echo $is_active ? 'active' : ''; ?>" 
                           data-post-id="<?php echo $post_id; ?>" 
                           data-movie-title="<?php echo esc_attr($movie_title); ?>"
                           data-server="<?php echo $s_idx; ?>" 
                           data-episode="<?php echo $e_idx; ?>"
                           data-name="<?php echo esc_attr($ep['name']); ?>"
                           data-slug="sv<?php echo $s_num; ?>-tap<?php echo $e_num; ?>">
                           <?php echo esc_html($ep['name']); ?>
                        </a>
                    <?php endforeach; endif; ?>
                </div>
            </div>
        <?php endforeach; else : ?>
            <p style="text-align:center; color:var(--text-muted);">Phim đang được cập nhật tập mới...</p>
        <?php endif; ?>
    </div>

    <div class="movie-content" style="margin-top: 40px; color: var(--text-muted);">
        <h3 style="color: #fff;">Thông tin phim:</h3>
        <?php the_content(); ?>
    </div>
</div>

</div>
<?php
// Script is now handled by amurhin-player (player.js) enqueued in functions.php
?>
