<?php
// includes/ajax-player.php - Xử lý load player qua AJAX

function amurhin_load_player_ajax() {
    $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
    $server_idx = isset($_POST['server']) ? intval($_POST['server']) : 0;
    $episode_idx = isset($_POST['episode']) ? intval($_POST['episode']) : 0;

    // Get episodes logic (Matching single-watch.php)
    $episode_query = new WP_Query(array(
        'post_type' => 'episode',
        'posts_per_page' => -1,
        'meta_key' => '_episode_name',
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

    if ( !empty($servers) && isset($servers[$server_idx]['data'][$episode_idx]) ) {
        $episode_data = $servers[$server_idx]['data'][$episode_idx];
        $link = $episode_data['link'];
        $type = $episode_data['type'];

        if ($type === 'embed') {
            echo '<iframe width="100%" height="100%" src="'.$link.'" frameborder="0" allowfullscreen style="position:absolute;top:0;left:0;width:100%;height:100%;"></iframe>';
        } else {
            ?>
            <div id="player-container" style="width:100%; height:100%; background:#000;">
                <video id="movie-player" controls preload="auto" style="width:100%; height:100%;"></video>
            </div>
            <script>
                if (Hls.isSupported()) {
                    var video = document.getElementById('movie-player');
                    var hls = new Hls();
                    hls.loadSource('<?php echo $link; ?>');
                    hls.attachMedia(video);
                    hls.on(Hls.Events.MANIFEST_PARSED, function() {
                        video.play();
                    });
                } else if (video.canPlayType('application/vnd.apple.mpegurl')) {
                    video.src = '<?php echo $link; ?>';
                    video.addEventListener('loadedmetadata', function() {
                        video.play();
                    });
                }
            </script>
            <?php
        }
    } else {
        echo '<div style="color:red; text-align:center; padding-top:20%;">Lỗi: Không tìm thấy link phim.</div>';
    }
    
    wp_die();
}
add_action( 'wp_ajax_load_player', 'amurhin_load_player_ajax' );
add_action( 'wp_ajax_nopriv_load_player', 'amurhin_load_player_ajax' );
