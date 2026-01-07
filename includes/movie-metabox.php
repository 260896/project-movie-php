<?php
/**
 * includes/movie-metabox.php
 * Khung giao diện quản lý phim nâng cao: Thông tin phụ và Quản lý tập phim.
 */

function amurhin_add_movie_metaboxes() {
    add_meta_box(
        'amurhin_movie_details',
        '📊 Thông tin nâng cao',
        'amurhin_movie_details_callback',
        'movie',
        'normal',
        'high'
    );

    add_meta_box(
        'amurhin_movie_episodes',
        '🎞️ Quản lý tập phim',
        'amurhin_movie_episodes_callback',
        'movie',
        'normal',
        'high'
    );

    // Enqueue media uploader for movie edit screen
    add_action('admin_enqueue_scripts', function($hook) {
        if ('post.php' != $hook && 'post-new.php' != $hook) return;
        global $post_type;
        if ('movie' != $post_type) return;
        wp_enqueue_media();
    });
}
add_action( 'add_meta_boxes', 'amurhin_add_movie_metaboxes' );

/**
 * Metadata Metabox (Views, Rating, Recommended, Schedule)
 */
function amurhin_movie_details_callback( $post ) {
    wp_nonce_field( 'amurhin_save_movie_details', 'amurhin_details_nonce' );
    
    $views = get_post_meta( $post->ID, '_movie_views', true ) ?: 0;
    $rating = get_post_meta( $post->ID, '_movie_rating', true ) ?: 5;
    $is_recommended = get_post_meta( $post->ID, '_movie_recommended', true );
    $schedule = get_post_meta( $post->ID, '_movie_schedule', true );
    $quality = get_post_meta( $post->ID, '_movie_quality', true ) ?: 'Full HD';
    $lang = get_post_meta( $post->ID, '_movie_lang', true ) ?: 'Vietsub';
    $thumb = get_post_meta( $post->ID, '_movie_thumb', true );
    $banner = get_post_meta( $post->ID, '_movie_banner', true );

    ?>
    <style>
        .movie-meta-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; padding: 10px; }
        .meta-field { margin-bottom: 15px; }
        .meta-field label { display: block; font-weight: bold; margin-bottom: 5px; }
        .meta-field input[type="text"], .meta-field input[type="number"], .meta-field select { width: 100%; padding: 8px; }
        .image-preview-wrap { margin-top: 10px; position: relative; display: inline-block; }
        .image-preview { max-width: 150px; max-height: 150px; border-radius: 4px; border: 1px solid #ddd; display: block; }
        .remove-img-btn { position: absolute; top: -10px; right: -10px; background: #d63638; color: #fff; border: none; border-radius: 50%; width: 22px; height: 22px; cursor: pointer; font-size: 14px; line-height: 20px; text-align: center; }
    </style>
    <div class="movie-meta-grid">
        <div class="meta-field">
            <label>Hình Thumbnail (Poster đứng):</label>
            <div class="meta-image-input">
                <input type="text" name="movie_thumb" id="movie_thumb" value="<?php echo esc_attr($thumb); ?>" placeholder="URL hình ảnh hoặc chọn từ thư viện">
                <button type="button" class="button select-img-btn" data-target="movie_thumb">Chọn hình</button>
            </div>
            <div class="image-preview-wrap" id="movie_thumb_preview_wrap" <?php echo $thumb ? '' : 'style="display:none;"'; ?>>
                <img src="<?php echo esc_url($thumb); ?>" class="image-preview" id="movie_thumb_preview">
                <button type="button" class="remove-img-btn" data-target="movie_thumb">×</button>
            </div>
        </div>
        <div class="meta-field">
            <label>Hình Banner (Poster ngang):</label>
            <div class="meta-image-input">
                <input type="text" name="movie_banner" id="movie_banner" value="<?php echo esc_attr($banner); ?>" placeholder="URL hình ảnh hoặc chọn từ thư viện">
                <button type="button" class="button select-img-btn" data-target="movie_banner">Chọn hình</button>
            </div>
            <div class="image-preview-wrap" id="movie_banner_preview_wrap" <?php echo $banner ? '' : 'style="display:none;"'; ?>>
                <img src="<?php echo esc_url($banner); ?>" class="image-preview" id="movie_banner_preview">
                <button type="button" class="remove-img-btn" data-target="movie_banner">×</button>
            </div>
        </div>
        <div class="meta-field">
            <label>Số lượt xem:</label>
            <input type="number" name="movie_views" value="<?php echo esc_attr($views); ?>">
        </div>
        <div class="meta-field">
            <label>Đánh giá (1-5 sao):</label>
            <input type="number" name="movie_rating" min="1" max="5" step="0.1" value="<?php echo esc_attr($rating); ?>">
        </div>
        <div class="meta-field">
            <label>Số lượt đánh giá (Manual):</label>
            <input type="number" name="movie_rating_count" value="<?php echo esc_attr(get_post_meta($post->ID, '_movie_rating_count', true) ?: 1); ?>">
        </div>
        <div class="meta-field">
            <label>Chất lượng:</label>
            <input type="text" name="movie_quality" value="<?php echo esc_attr($quality); ?>" placeholder="Ví dụ: 4K, Full HD...">
        </div>
        <div class="meta-field">
            <label>Lịch chiếu phim (Text):</label>
            <input type="text" name="movie_schedule" value="<?php echo esc_attr($schedule); ?>" placeholder="Ví dụ: Mỗi tối thứ 7 hàng tuần">
        </div>
        <div class="meta-field">
            <label>
                <input type="checkbox" name="movie_recommended" value="1" <?php checked( $is_recommended, 1 ); ?>>
                <strong>Đánh dấu là Phim Đề xuất</strong>
            </label>
        </div>
    </div>

    <script>
    jQuery(document).ready(function($) {
        $('.select-img-btn').click(function(e) {
            e.preventDefault();
            let button = $(this);
            let targetId = button.data('target');
            let frame = wp.media({
                title: 'Chọn hình ảnh',
                button: { text: 'Sử dụng hình này' },
                multiple: false
            });

            frame.on('select', function() {
                let attachment = frame.state().get('selection').first().toJSON();
                $(`#${targetId}`).val(attachment.url);
                $(`#${targetId}_preview`).attr('src', attachment.url);
                $(`#${targetId}_preview_wrap`).show();
            });

            frame.open();
        });

        $('.remove-img-btn').click(function(e) {
            e.preventDefault();
            let targetId = $(this).data('target');
            $(`#${targetId}`).val('');
            $(`#${targetId}_preview_wrap`).hide();
        });
    });
    </script>
<?php
}

/**
 * Episode Manager Metabox
 */
function amurhin_movie_episodes_callback( $post ) {
    wp_nonce_field( 'amurhin_save_movie_episodes', 'amurhin_episodes_nonce' );
    
    $episodes_json = get_post_meta( $post->ID, '_movie_episodes', true );
    $servers = json_decode($episodes_json, true) ?: [];

    ?>
    <div id="episode-manager" style="border: 2px dashed #d63638; padding: 15px; border-radius: 8px;">
        <div style="background: #fcf0f1; border-left: 4px solid #d63638; padding: 10px; margin-bottom: 15px; color: #d63638;">
            <strong>⚠️ LƯU Ý:</strong> Phần này là <b>Hệ thống cũ (Legacy)</b>. 
            Vui lòng sử dụng menu <a href="edit.php?post_type=episode" style="color:#2271b1;text-decoration:underline;">Tập phim</a> ở bên trái để quản lý tập phim chuyên nghiệp hơn.
        </div>
        <div id="server-list">
            <?php if ( !empty($servers) ) : foreach ( $servers as $s_index => $server ) : ?>
                <div class="server-item" data-index="<?php echo $s_index; ?>">
                    <div class="server-header">
                        <input type="text" name="servers[<?php echo $s_index; ?>][name]" value="<?php echo esc_attr($server['name']); ?>" placeholder="Tên Server (Ví dụ: Server #1)">
                        <button type="button" class="button remove-server">Xóa Server</button>
                    </div>
                    <table class="episode-table">
                        <thead>
                            <tr>
                                <th>Tên tập</th>
                                <th>Link phim (M3U8/Embed)</th>
                                <th>Loại</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ( !empty($server['data']) ) : foreach ( $server['data'] as $e_index => $ep ) : ?>
                                <tr>
                                    <td><input type="text" name="servers[<?php echo $s_index; ?>][data][<?php echo $e_index; ?>][name]" value="<?php echo esc_attr($ep['name']); ?>" placeholder="Tập 1"></td>
                                    <td><input type="text" name="servers[<?php echo $s_index; ?>][data][<?php echo $e_index; ?>][link]" value="<?php echo esc_attr($ep['link']); ?>" placeholder="https://..."></td>
                                    <td>
                                        <select name="servers[<?php echo $s_index; ?>][data][<?php echo $e_index; ?>][type]">
                                            <option value="link" <?php selected($ep['type'], 'link'); ?>>Link (.m3u8)</option>
                                            <option value="embed" <?php selected($ep['type'], 'embed'); ?>>Embed (Iframe)</option>
                                        </select>
                                    </td>
                                    <td><button type="button" class="button remove-episode">×</button></td>
                                </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                    <button type="button" class="button add-episode">Thêm tập mới</button>
                </div>
            <?php endforeach; endif; ?>
        </div>
        <button type="button" class="button button-primary" id="add-server">Thêm Server mới</button>
    </div>

    <style>
        #episode-manager { padding: 10px; }
        .server-item { background: #f8f9fa; border: 1px solid #ccd0d4; padding: 15px; margin-bottom: 20px; border-radius: 4px; }
        .server-header { display: flex; gap: 10px; margin-bottom: 15px; }
        .server-header input { flex: 1; font-weight: bold; }
        .episode-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        .episode-table th { text-align: left; padding: 8px; background: #eef1f3; }
        .episode-table td { padding: 5px; }
        .episode-table input, .episode-table select { width: 100%; }
        .remove-server { color: #d63638 !important; border-color: #d63638 !important; }
        .remove-episode { color: #d63638; }
    </style>

    <script>
    jQuery(document).ready(function($) {
        let serverCount = <?php echo count($servers); ?>;

        $('#add-server').click(function() {
            let html = `
                <div class="server-item" data-index="${serverCount}">
                    <div class="server-header">
                        <input type="text" name="servers[${serverCount}][name]" value="" placeholder="Tên Server (Ví dụ: Server #1)">
                        <button type="button" class="button remove-server">Xóa Server</button>
                    </div>
                    <table class="episode-table">
                        <thead>
                            <tr><th>Tên tập</th><th>Link phim</th><th>Loại</th><th></th></tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                    <button type="button" class="button add-episode">Thêm tập mới</button>
                </div>`;
            $('#server-list').append(html);
            serverCount++;
        });

        $(document).on('click', '.add-episode', function() {
            let serverItem = $(this).closest('.server-item');
            let sIdx = serverItem.data('index');
            let eIdx = serverItem.find('tbody tr').length;
            let html = `
                <tr>
                    <td><input type="text" name="servers[${sIdx}][data][${eIdx}][name]" placeholder="Tập ${eIdx + 1}"></td>
                    <td><input type="text" name="servers[${sIdx}][data][${eIdx}][link]" placeholder="https://..."></td>
                    <td>
                        <select name="servers[${sIdx}][data][${eIdx}][type]">
                            <option value="link">Link (.m3u8)</option>
                            <option value="embed">Embed (Iframe)</option>
                        </select>
                    </td>
                    <td><button type="button" class="button remove-episode">×</button></td>
                </tr>`;
            serverItem.find('tbody').append(html);
        });

        $(document).on('click', '.remove-server', function() {
            if(confirm('Bạn có chắc chắn muốn xóa toàn bộ Server này?')) {
                $(this).closest('.server-item').remove();
            }
        });

        $(document).on('click', '.remove-episode', function() {
            $(this).closest('tr').remove();
        });
    });
    </script>
    <?php
}

/**
 * Save Metadata and Episodes
 */
function amurhin_save_movie_meta( $post_id ) {
    // Check Details Nonce
    if ( isset( $_POST['amurhin_details_nonce'] ) && wp_verify_nonce( $_POST['amurhin_details_nonce'], 'amurhin_save_movie_details' ) ) {
        if ( isset( $_POST['movie_views'] ) ) update_post_meta( $post_id, '_movie_views', sanitize_text_field($_POST['movie_views']) );
        if ( isset( $_POST['movie_rating'] ) ) update_post_meta( $post_id, '_movie_rating', sanitize_text_field($_POST['movie_rating']) );
        if ( isset( $_POST['movie_rating_count'] ) ) update_post_meta( $post_id, '_movie_rating_count', sanitize_text_field($_POST['movie_rating_count']) );
        if ( isset( $_POST['movie_quality'] ) ) update_post_meta( $post_id, '_movie_quality', sanitize_text_field($_POST['movie_quality']) );
        if ( isset( $_POST['movie_lang'] ) ) update_post_meta( $post_id, '_movie_lang', sanitize_text_field($_POST['movie_lang']) );
        if ( isset( $_POST['movie_schedule'] ) ) update_post_meta( $post_id, '_movie_schedule', sanitize_text_field($_POST['movie_schedule']) );
        if ( isset( $_POST['movie_thumb'] ) ) update_post_meta( $post_id, '_movie_thumb', esc_url_raw($_POST['movie_thumb']) );
        if ( isset( $_POST['movie_banner'] ) ) update_post_meta( $post_id, '_movie_banner', esc_url_raw($_POST['movie_banner']) );
        $recommended = isset( $_POST['movie_recommended'] ) ? 1 : 0;
        update_post_meta( $post_id, '_movie_recommended', $recommended );
    }

    // Check Episodes Nonce
    if ( isset( $_POST['amurhin_episodes_nonce'] ) && wp_verify_nonce( $_POST['amurhin_episodes_nonce'], 'amurhin_save_movie_episodes' ) ) {
        if ( isset( $_POST['servers'] ) ) {
            // Re-index to ensure JSON is clean
            $servers = array_values($_POST['servers']);
            foreach ($servers as &$server) {
                if (isset($server['data'])) {
                    $server['data'] = array_values($server['data']);
                } else {
                    $server['data'] = [];
                }
            }
            update_post_meta( $post_id, '_movie_episodes', json_encode($servers, JSON_UNESCAPED_UNICODE) );
        } else {
            update_post_meta( $post_id, '_movie_episodes', '' );
        }
    }
}
add_action( 'save_post', 'amurhin_save_movie_meta' );
