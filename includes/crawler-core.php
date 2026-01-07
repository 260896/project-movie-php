<?php
/**
 * includes/crawler-core.php
 * Tích hợp công cụ cào phim từ API (OPhim, KKPhim, NguonC) trực tiếp vào theme.
 */

// Define API Sources if not defined
if (!defined('API_KKPHIM')) define('API_KKPHIM', 'https://phimapi.com');
if (!defined('API_OPHIM')) define('API_OPHIM', 'https://ophim1.com');
if (!defined('API_NGUONC')) define('API_NGUONC', 'https://phim.nguonc.com');
if (!defined('API_IPHIM')) define('API_IPHIM', 'https://iphim.cc');

function amurhin_get_api_url($source) {
    switch ($source) {
        case 'ophim': return API_OPHIM;
        case 'nguonc': return API_NGUONC;
        case 'iphim': return API_IPHIM;
        case 'kkphim': default: return API_KKPHIM;
    }
}

/**
 * Add Crawler Menu to Admin
 */
function amurhin_crawler_menu() {
    add_submenu_page(
        'edit.php?post_type=movie',
        'Cào Phim API',
        'Cào Phim API',
        'manage_options',
        'amurhin-crawler',
        'amurhin_crawler_page'
    );
}
add_action('admin_menu', 'amurhin_crawler_menu');

function amurhin_crawler_page() {
    ?>
    <style>
        .crawl-thumb { width: 60px; height: 90px; object-fit: cover; border-radius: 4px; }
        .crawl-badge { display: inline-block; padding: 2px 6px; border-radius: 3px; font-size: 11px; margin-right: 4px; color: #fff; }
        .badge-type { background: #2271b1; }
        .badge-year { background: #444; }
        .crawl-actions-cell button { margin-bottom: 5px; width: 100%; display: block; }
        .queue-container { margin-top: 40px; border-top: 2px solid #ddd; padding-top: 20px; }
        #crawl_table_body {
            max-height: 100px;
            overflow-y: auto;
        }
    </style>
    <div class="wrap">
        <h1>🚀 Công cụ cào phim API</h1>
        <p>Tương thích với OPhim, KKPhim, NguonC và iPhim.</p>
        
        <div class="crawl-settings" style="background:#fff; padding:20px; border-radius:8px; box-shadow:0 2px 10px rgba(0,0,0,0.1);">
            <table class="form-table">
                <tr>
                    <th scope="row">Nguồn phim</th>
                    <td>
                        <select id="crawl_source" style="width: 200px;">
                            <option value="kkphim">KKPhim (phimapi.com)</option>
                            <option value="ophim">OPhim (ophim1.com)</option>
                            <option value="nguonc">NguonC (nguonc.com)</option>
                            <option value="iphim">iPhim (iphim.cc)</option>
                        </select>
                        
                        <select id="crawl_type" style="width: 200px; margin-left:10px;">
                            <option value="">-- Lọc theo Loại --</option>
                            <option value="phim-le">Phim Lẻ</option>
                            <option value="phim-bo">Phim Bộ</option>
                            <option value="phim-chieu-rap">Phim Chiếu Rạp</option>
                            <option value="hoat-hinh">Phim Hoạt Hình</option>
                        </select>

                        <select id="crawl_genre" style="width: 200px; margin-left:10px;">
                            <option value="">-- Lọc theo Thể loại --</option>
                            <option value="hanh-dong">Hành Động</option>
                            <option value="tinh-cam">Tình Cảm</option>
                            <option value="hai-huoc">Hài Hước</option>
                            <option value="co-trang">Cổ Trang</option>
                            <option value="tam-ly">Tâm Lý</option>
                            <option value="hinh-su">Hình Sự</option>
                            <option value="chien-tranh">Chiến Tranh</option>
                            <option value="the-thao">Thể Thao</option>
                            <option value="vo-thuat">Võ Thuật</option>
                            <option value="vien-tuong">Viễn Tưởng</option>
                            <option value="phieu-luu">Phiêu Lưu</option>
                            <option value="kinh-di">Kinh Dị</option>
                            <option value="am-nhac">Âm Nhạc</option>
                            <option value="than-thoai">Thần Thoại</option>
                            <option value="tai-lieu">Tài Liệu</option>
                            <option value="gia-dinh">Gia Đình</option>
                            <option value="chinh-kich">Chính Kịch</option>
                            <option value="bi-an">Bí Ẩn</option>
                            <option value="hoc-duong">Học Đường</option>
                            <option value="khoa-hoc">Khoa Học</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Từ Trang</th>
                    <td><input type="number" id="page_from" value="1" style="width: 80px;"> Đến: <input type="number" id="page_to" value="1" style="width: 80px;"></td>
                </tr>
            </table>
            <button type="button" id="get_list_movies" class="button button-primary">Lấy danh sách phim</button>
        </div>

        <div class="crawl-search" style="margin-top: 20px; background:#fff; padding:20px; border-radius:8px; box-shadow:0 2px 10px rgba(0,0,0,0.1);">
            <h3>2. Tìm Kiếm Phim</h3>
            <div style="display:flex; gap:10px;">
                <input type="text" id="search_keyword" placeholder="Nhập tên phim..." style="flex:1; padding:8px;">
                <button type="button" id="search_movie_btn" class="button button-primary">Tìm Kiếm</button>
            </div>
        </div>

        <div id="crawl-results" style="margin-top: 30px;">
            <div id="movie-list-table"></div>
        </div>

        <!-- Queue Section -->
        <div class="queue-container">
            <h3>📥 Danh sách chờ cào (<span id="queue-count">0</span> phim)</h3>
             <button type="button" id="crawl_queue_all" class="button button-primary button-large">
                <span class="dashicons dashicons-download" style="margin-top:4px"></span> Cào tất cả danh sách chờ
            </button>
            <button type="button" id="clear_queue" class="button button-secondary">Xóa danh sách</button>
            <div id="queue-table" style="margin-top:15px; max-height: 400px; overflow-y:auto; background: #f9f9f9; padding: 10px; border: 1px solid #e5e5e5;">
                <p>Chưa có phim nào trong danh sách chờ.</p>
            </div>
        </div>

        <div id="crawl-log" style="margin-top: 30px; background:#000; color:#0f0; padding:15px; border-radius:5px; font-family:monospace; height:300px; overflow-y:auto; display:none;">
            [Ready] Chờ lệnh từ bạn...<br>
        </div>
    </div>

    <script>
    jQuery(document).ready(function($) {
        let crawlQueue = [];

        // --- FETCH LIST ---
        $('#get_list_movies').click(function() {
            const source = $('#crawl_source').val();
            const from = $('#page_from').val();
            const to = $('#page_to').val();
            
            $(this).prop('disabled', true).text('Đang lấy danh sách...');
            $('#movie-list-table').html('<p style="padding:20px;">Đang tải dữ liệu từ API...</p>');

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'amurhin_fetch_list',
                    source: source,
                    page: from,
                    filter_type: $('#crawl_type').val(),
                    filter_genre: $('#crawl_genre').val()
                },
                success: function(res) {
                    $('#get_list_movies').prop('disabled', false).text('Lấy danh sách phim');
                    try {
                        const movies = JSON.parse(res);
                        renderMainTable(movies);
                    } catch(e) {
                         $('#movie-list-table').html('<p style="padding:20px; color:red;">Lỗi phân tích dữ liệu JSON.</p>');
                    }
                }
            });
        });

        // --- SEARCH ---
        $('#search_movie_btn').click(function() {
            const source = $('#crawl_source').val();
            const keyword = $('#search_keyword').val();
            
            if(!keyword) return alert('Vui lòng nhập tên phim!');

            $(this).prop('disabled', true).text('Đang tìm...');
            $('#movie-list-table').html('<p style="padding:20px;">Đang tìm kiếm...</p>');

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'amurhin_search_movie',
                    source: source,
                    keyword: keyword
                },
                success: function(res) {
                    $('#search_movie_btn').prop('disabled', false).text('Tìm Kiếm');
                    try {
                        const response = JSON.parse(res);
                        let movies = [];
                         if(response.data && response.data.items) {
                            movies = response.data.items;
                        } else if(Array.isArray(response)) {
                            movies = response;
                        } else if (response.items) {
                            movies = response.items;
                        }

                        // Map search results to standard format if needed
                        const mappedMovies = movies.map(m => {
                            // Extract domain for thumb if needed, logic is shared php side mostly but search is raw
                            // For search, we might need to rely on API providing full path or manually building it
                            // To keep it simple, we pass raw and handle in render if properties exist

                            return {
                                slug: m.slug,
                                title: m.name,
                                org_title: m.origin_name,
                                year: m.year,
                                thumb: (m.thumb_url && m.thumb_url.startsWith('http')) ? m.thumb_url : ((response.data && response.data.APP_DOMAIN_CDN_IMAGE) ? response.data.APP_DOMAIN_CDN_IMAGE + '/uploads/movies/' + m.thumb_url : m.thumb_url),
                                exists: m.exists,
                                type: m.type,
                                country: m.country,
                                start_date: m.start_date,
                                trailer: m.trailer,
                                status: m.status
                            };
                        });

                        renderMainTable(mappedMovies);
                    } catch(e) {
                        $('#movie-list-table').html('<p style="padding:20px; color:red;">Lỗi xử lý dữ liệu!</p>');
                    }
                }
            });
        });

        function renderMainTable(movies) {
            if(Array.isArray(movies) && movies.length > 0) {
                let html = `<table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th width="80">Thumb</th>
                            <th>
                                Thông tin phim<br>
                                <input type="text" id="crawl_filter_input" placeholder="Lọc nhanh tên phim..." style="width: 100%; margin-top: 5px; font-weight: normal;">
                            </th>
                            <th width="120">Loại phim</th>
                            <th width="150">Thể loại</th>
                            <th width="150">Quốc gia</th>
                            <th width="150">Khởi Chiếu</th>
                            <th width="150">Trạng thái phim</th>
                            <th width="150">Hành động</th>
                        </tr>
                    </thead>
                    <tbody id="crawl_table_body">`;
                
                movies.forEach(m => {
                    // Fallback for missing thumb
                    let thumbUrl = m.thumb;
                     if(!thumbUrl) thumbUrl = 'https://via.placeholder.com/60x90?text=No+Img';
                    html += `<tr class="crawl-row">
                        <td><img src="${thumbUrl}" class="crawl-thumb"></td>
                        <td>
                            <strong class="crawl-title">${m.title}</strong><br>
                            <small>${m.org_title}</small><br>
                            <span class="crawl-badge badge-year">${m.year}</span>
                            ${m.exists ? '<br><span style="color:#d63638; font-weight:bold; font-size:11px; margin-top:5px; display:inline-block;">[Phim đã có]</span>' : ''}
                        </td>
                         <td>
                            ${m.type ? `<span class="crawl-badge badge-type">${m.type}</span>` : '<span style="color:#aaa;">---</span>'}
                        </td>
                        <td>
                            <small>${m.country || '<span style="color:#aaa;">Chưa có</span>'}</small>
                        </td>
                        <td>
                            <small>${m.start_date || '<span style="color:#aaa;">Chưa có</span>'}</small>
                        </td>
                        <td>
                            <small>${m.trailer || '<span style="color:#aaa;">Chưa có</span>'}</small>
                        </td>
                        <td>
                            <small>${m.status || '<span style="color:#aaa;">Chưa có</span>'}</small>
                        </td>
                        <td class="crawl-actions-cell">
                            <button type="button" class="button button-small btn-crawl-now" data-slug="${m.slug}" ${m.exists ? 'disabled' : ''}>🚀 Cào ngay</button>
                            <button type="button" class="button button-small btn-add-queue" data-json='${JSON.stringify(m).replace(/'/g, "&#39;")}' ${m.exists ? 'disabled' : ''}>➕ Thêm List</button>
                        </td>
                    </tr>`;
                });
                html += `</tbody></table>`;
                $('#movie-list-table').html(html);
                
                // Active Local Filter
                $('#crawl_filter_input').on('keyup', function() {
                    var value = $(this).val().toLowerCase();
                    $("#crawl_table_body tr").filter(function() {
                        $(this).toggle($(this).find('.crawl-title').text().toLowerCase().indexOf(value) > -1)
                    });
                });

            } else {
                $('#movie-list-table').html('<p style="padding:20px;">Không tìm thấy phim nào!</p>');
            }
        }

        // --- QUEUE LOGIC ---
        $(document).on('click', '.btn-add-queue', function() {
            const movie = $(this).data('json');
            
            // Check existence
            if (crawlQueue.find(m => m.slug === movie.slug)) {
                return alert('Phim này đã có trong danh sách chờ!');
            }
            
            crawlQueue.push(movie);
            renderQueueTable();
             // Animation effect
            $(this).text('✅ Đã thêm').prop('disabled', true);
        });

        function renderQueueTable() {
            $('#queue-count').text(crawlQueue.length);
            if (crawlQueue.length === 0) {
                $('#queue-table').html('<p>Chưa có phim nào trong danh sách chờ.</p>');
                return;
            }

            let html = `<table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                         <th width="50">#</th>
                         <th>Tên phim</th>
                         <th width="100">Hành động</th>
                    </tr>
                </thead><tbody>`;
            
            crawlQueue.forEach((m, index) => {
                html += `<tr>
                    <td>${index + 1}</td>
                    <td><strong>${m.title}</strong> (${m.year})</td>
                    <td><button type="button" class="button-link-delete btn-remove-queue" data-idx="${index}">Xóa</button></td>
                </tr>`;
            });
            
            html += `</tbody></table>`;
            $('#queue-table').html(html);
        }

        $(document).on('click', '.btn-remove-queue', function() {
            const idx = $(this).data('idx');
            crawlQueue.splice(idx, 1);
            renderQueueTable();
        });

        $('#clear_queue').click(function() {
            if(confirm('Xóa toàn bộ danh sách chờ?')) {
                crawlQueue = [];
                renderQueueTable();
                $('.btn-add-queue').text('➕ Thêm List').prop('disabled', false); // Reset buttons logic simplified
            }
        });

        // --- CRAWL ACTIONS ---
        
        // 1. Crawl Single "Now"
        // 1. Crawl Single "Now"
        $(document).on('click', '.btn-crawl-now', function() {
            const slug = $(this).data('slug');
            const btn = $(this);
            const source = $('#crawl_source').val();

            // Direct execution without confirm
            btn.prop('disabled', true).text('Đang cào...');
            $('#crawl-log').show().append(`[Single] Đang cào: ${slug}... `);
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: { action: 'amurhin_crawl_item', source: source, slug: slug },
                success: function(res) {
                        try {
                            const data = JSON.parse(res);
                            if(data.status) {
                                $('#crawl-log').append(`<span style="color:#0f0;">OK (ID: ${data.post_id})</span><br>`);
                                btn.text('✅ Hoàn tất');
                            } else {
                                $('#crawl-log').append(`<span style="color:#f00;">Error: ${data.msg}</span><br>`);
                                btn.prop('disabled', false).text('🚀 Cào ngay');
                            }
                        } catch(e) { $('#crawl-log').append("JSON Error<br>"); btn.prop('disabled', false).text('🚀 Cào ngay'); }
                }
            });
        });

        // 2. Crawl All Queue
        $('#crawl_queue_all').click(function() {
            if(crawlQueue.length === 0) return alert('Danh sách trống!');
            
            $(this).prop('disabled', true).text('Đang xử lý danh sách...');
            $('#crawl-log').show().empty().append('[Queue] Bắt đầu cào danh sách...<br>');
            processQueue();
        });

        function processQueue() {
            if(crawlQueue.length === 0) {
                $('#crawl_queue_all').prop('disabled', false).text('Cào tất cả danh sách chờ');
                $('#crawl-log').append('<span style="color:white; font-weight:bold;">[DONE] Đã hoàn thành toàn bộ danh sách!</span><br>');
                return;
            }

            const movie = crawlQueue[0]; // Peek first
            const source = $('#crawl_source').val();
            
            $('#crawl-log').append(`[Queue] Đang cào: ${movie.title} (${movie.slug})... `);

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: { action: 'amurhin_crawl_item', source: source, slug: movie.slug },
                success: function(res) {
                    try {
                        const data = JSON.parse(res);
                        if(data.status) {
                            $('#crawl-log').append(`<span style="color:#0f0;">OK</span><br>`);
                            // Remove from queue on success
                            crawlQueue.shift();
                            renderQueueTable();
                        } else {
                            $('#crawl-log').append(`<span style="color:#f00;">Error: ${data.msg}</span><br>`);
                            // Remove even if error to prevent stuck loop? Or keep?
                            // Let's remove to proceed
                            crawlQueue.shift();
                            renderQueueTable();
                        }
                    } catch(e) { $('#crawl-log').append("JSON Error<br>"); crawlQueue.shift(); }
                    
                    // Recursive call
                    processQueue();
                }
            });
        }
    });
    </script>
    <?php
}

/**
 * AJAX Handler: Fetch List
 */
function amurhin_fetch_list_ajax() {
    $source = $_POST['source'];
    $page = $_POST['page'];
    $filter_type = isset($_POST['filter_type']) ? $_POST['filter_type'] : '';
    $filter_genre = isset($_POST['filter_genre']) ? $_POST['filter_genre'] : '';
    
    // Explicit Endpoint Handling based on source
    $api_base = ''; 
    $url = '';

    if ($source == 'nguonc') {
        $api_base = 'https://phim.nguonc.com';
        $url = $api_base . '/api/films/phim-moi-cap-nhat?page=' . $page;
    } elseif ($source == 'iphim') {
        $api_base = 'https://iphim.cc';
        $url = $api_base . '/api/films/phim-moi-cap-nhat?page=' . $page;
    } elseif ($source == 'ophim') {
        $api_base = 'https://ophim1.com';
        $url = $api_base . '/danh-sach/phim-moi-cap-nhat?page=' . $page;
        if($filter_genre) $url = "https://ophim1.com/v1/api/the-loai/{$filter_genre}?page={$page}";
        if($filter_type) $url = "https://ophim1.com/v1/api/danh-sach/{$filter_type}?page={$page}";
    } else { // kkphim
        $api_base = 'https://phimapi.com';
        $url = $api_base . '/danh-sach/phim-moi-cap-nhat?page=' . $page;
        if($filter_genre) $url = "https://phimapi.com/v1/api/the-loai/{$filter_genre}?page={$page}";
        if($filter_type) $url = "https://phimapi.com/v1/api/danh-sach/{$filter_type}?page={$page}";
    }

    $response = wp_remote_get($url, array('timeout' => 15, 'sslverify' => false));
    
    if (is_wp_error($response)) wp_die('[]');
    
    $body = json_decode(wp_remote_retrieve_body($response));
    $list = [];

    // Extract CDN Image Domain if available
    $cdn_image = isset($body->pathImage) ? $body->pathImage : (isset($body->data->APP_DOMAIN_CDN_IMAGE) ? $body->data->APP_DOMAIN_CDN_IMAGE : '');

    // NguonC/iPhim logic may adhere to 'items'
    $items = isset($body->items) ? $body->items : (isset($body->data->items) ? $body->data->items : []);

    if ($items) {
        foreach ($items as $item) {
            // Check if exists in DB
            $exists = false;
            $check = new WP_Query(array(
                'post_type' => 'movie',
                'meta_query' => array(
                    array('key' => '_crawl_slug', 'value' => $item->slug)
                ),
                'posts_per_page' => 1,
                'fields' => 'ids'
            ));
            if($check->have_posts()) $exists = true;

            // Build Thumb URL
            $thumb = '';
            if (isset($item->thumb_url)) {
                if (strpos($item->thumb_url, 'http') === 0) {
                    $thumb = $item->thumb_url;
                } elseif ($cdn_image) {
                     // Usual structure for OPhim/KKPhim pathImage provided
                    if ($source == 'ophim' || $source == 'kkphim') {
                         $thumb = $cdn_image . '/uploads/movies/' . $item->thumb_url;
                    } else {
                         $thumb = $item->thumb_url;
                    }
                } else {
                    $thumb = $item->thumb_url;
                }
            }
            
            $list[] = array(
                'slug' => $item->slug,
                'title' => $item->name,
                'org_title' => $item->origin_name,
                'year' => $item->year,
                'thumb' => $thumb,
                'type' => isset($item->type) ? $item->type : '',
                'genres' => '',
                'exists' => $exists
            );
        }
    }
    echo json_encode($list);
    wp_die();
}
add_action('wp_ajax_amurhin_fetch_list', 'amurhin_fetch_list_ajax');


/**
 * AJAX Handler: Search Movie
 */
function amurhin_search_movie_ajax() {
    $source = $_POST['source'];
    $keyword = urlencode($_POST['keyword']);
    
    $url = '';
    
    if ($source == 'nguonc') {
        $url = "https://phim.nguonc.com/api/films/search?keyword={$keyword}";
    } elseif ($source == 'iphim') {
        $url = "https://iphim.cc/api/films/search?keyword={$keyword}";
    } elseif ($source == 'ophim') {
        $url = "https://ophim1.com/v1/api/tim-kiem?keyword={$keyword}";
    } else { // kkphim
        $url = "https://phimapi.com/v1/api/tim-kiem?keyword={$keyword}";
    }
    
    $response = wp_remote_get($url, array('timeout' => 15, 'sslverify' => false));
    
    if (is_wp_error($response)) wp_die(json_encode([]));
    
    $body = wp_remote_retrieve_body($response);
    echo $body; // Return raw JSON from API to let JS handle structure
    wp_die();
}
add_action('wp_ajax_amurhin_search_movie', 'amurhin_search_movie_ajax');

/**
 * AJAX Handler: Crawl Item
 */
function amurhin_crawl_item_ajax() {
    $source = $_POST['source'];
    $slug = $_POST['slug'];

    $result = ['status' => false, 'msg' => 'Unknown source'];

    switch ($source) {
        case 'ophim':
            $result = amurhin_crawl_ophim($slug);
            break;
        case 'kkphim':
            $result = amurhin_crawl_kkphim($slug);
            break;
        case 'nguonc':
            $result = amurhin_crawl_nguonc($slug);
            break;
        case 'iphim':
            $result = amurhin_crawl_iphim($slug);
            break;
    }

    echo json_encode($result);
    wp_die();
}
add_action('wp_ajax_amurhin_crawl_item', 'amurhin_crawl_item_ajax');

/**
 * Common Function: Save Unified Data to WordPress
 */
function amurhin_save_movie_to_wp($data) {
    // Check if exists
    $existing = new WP_Query(array(
        'post_type' => 'movie',
        'meta_query' => array(
            array('key' => '_crawl_slug', 'value' => $data['slug'])
        ),
        'posts_per_page' => 1
    ));

    $post_data = array(
        'post_title'   => $data['name'],
        'post_content' => $data['content'],
        'post_status'  => 'publish',
        'post_type'    => 'movie',
        'post_excerpt' => $data['origin_name']
    );

    if ($existing->have_posts()) {
        $post_id = $existing->posts[0]->ID;
        $post_data['ID'] = $post_id;
        wp_update_post($post_data);
    } else {
        $post_id = wp_insert_post($post_data);
        update_post_meta($post_id, '_crawl_slug', $data['slug']);
    }

    // Taxonomies
    if(!empty($data['countries'])) wp_set_object_terms($post_id, $data['countries'], 'country');
    if(!empty($data['year'])) wp_set_object_terms($post_id, $data['year'], 'release_year');
    if(!empty($data['genres'])) wp_set_object_terms($post_id, $data['genres'], 'genre');
    if(!empty($data['actor'])) wp_set_object_terms($post_id, $data['actor'], 'actor');
    if(!empty($data['director'])) wp_set_object_terms($post_id, $data['director'], 'director');
    
    // Type handling
    $type = ($data['type'] == 'single' || $data['type'] == 'le') ? 'Phim lẻ' : 'Phim bộ';
    if(isset($data['type_clean'])) $type = $data['type_clean']; // Allow override
    wp_set_object_terms($post_id, $type, 'movie_type');

    // Meta details
    update_post_meta($post_id, '_movie_quality', $data['quality'] ?: 'HD');
    update_post_meta($post_id, '_movie_lang', $data['lang'] ?: 'Vietsub');
    update_post_meta($post_id, '_movie_thumb', $data['thumb']);
    update_post_meta($post_id, '_movie_poster', $data['poster']);
    update_post_meta($post_id, '_movie_banner', $data['poster']); // Map API Poster to Theme Banner
    update_post_meta($post_id, '_movie_trailer', $data['trailer']);
    update_post_meta($post_id, '_movie_duration', $data['time']);
    update_post_meta($post_id, '_movie_total_episodes', $data['episode_total']);
    
    // Existing random stats if new
    if (!$existing->have_posts()) {
        update_post_meta($post_id, '_movie_views', rand(100, 5000));
        update_post_meta($post_id, '_movie_rating', 5);
    }

    // Episodes Handling - Create 'episode' posts and JSON meta
    // Clear old episodes if updating? Maybe risky. Logic: Update/Insert check
    // Legacy JSON for player compatibility
    $servers_save = [];
    
    if (!empty($data['episodes'])) {
        foreach ($data['episodes'] as $sv) {
            $server_name = $sv['server_name'];
            $ep_data_legacy = [];
            
            foreach ($sv['server_data'] as $ep) {
                // Legacy Format
                $ep_data_legacy[] = [
                    'name' => $ep['name'],
                    'link' => $ep['link'],
                    'type' => $ep['type'] ?? 'link' // m3u8 or embed
                ];

                // Create/Update Episode Post
                $ep_name = $ep['name'];
                $ep_link = $ep['link'];
                
                $existing_ep = new WP_Query(array(
                    'post_type'  => 'episode',
                    'meta_query' => array(
                        'relation' => 'AND',
                        array('key' => '_episode_movie_id', 'value' => $post_id),
                        array('key' => '_episode_server', 'value' => $server_name),
                        array('key' => '_episode_link', 'value' => $ep_link), // Strict check
                    ),
                    'posts_per_page' => 1
                ));

                $ep_post_args = array(
                    'post_title'  => $data['name'] . ' - ' . $server_name . ' - ' . $ep_name,
                    'post_type'   => 'episode',
                    'post_status' => 'publish',
                );

                if ($existing_ep->have_posts()) {
                    $ep_post_id = $existing_ep->posts[0]->ID;
                    $ep_post_args['ID'] = $ep_post_id;
                    wp_update_post($ep_post_args);
                } else {
                    $ep_post_id = wp_insert_post($ep_post_args);
                }

                update_post_meta($ep_post_id, '_episode_movie_id', $post_id);
                update_post_meta($ep_post_id, '_episode_server', $server_name);
                update_post_meta($ep_post_id, '_episode_name', $ep_name);
                update_post_meta($ep_post_id, '_episode_link', $ep_link);
                update_post_meta($ep_post_id, '_episode_type', $ep['type'] ?? 'link');
            }
            
            $servers_save[] = [
                'name' => $server_name,
                'data' => $ep_data_legacy
            ];
        }
    }
    
    update_post_meta($post_id, '_movie_episodes', json_encode($servers_save, JSON_UNESCAPED_UNICODE));
    return ['status' => true, 'post_id' => $post_id];
}

/**
 * 1. OPHIM Handler
 */
function amurhin_crawl_ophim($slug) {
    $url = "https://ophim1.com/phim/{$slug}";
    $response = wp_remote_get($url, array('timeout' => 15, 'sslverify' => false));
    if (is_wp_error($response)) return ['status' => false, 'msg' => 'API Error'];
    
    $json = json_decode(wp_remote_retrieve_body($response), true);
    if (!$json || !isset($json['movie'])) return ['status' => false, 'msg' => 'Invalid JSON'];

    $mv = $json['movie'];
    
    // Map Data
    $data = [
        'slug' => $mv['slug'],
        'name' => $mv['name'],
        'origin_name' => $mv['origin_name'],
        'content' => $mv['content'],
        'type' => $mv['type'],
        'status' => $mv['status'],
        'thumb' => $mv['thumb_url'],
        'poster' => $mv['poster_url'],
        'trailer' => isset($mv['trailer_url']) ? $mv['trailer_url'] : '',
        'time' => $mv['time'],
        'episode_total' => $mv['episode_total'],
        'quality' => $mv['quality'],
        'actor' => $mv['actor'],
        'director' => $mv['director'],
        'lang' => $mv['lang'],
        'year' => isset($mv['year']) ? $mv['year'] : '',
        'countries' => array_map(function($c){ return $c['name']; }, $mv['country']),
        'genres' => array_map(function($g){ return $g['name']; }, $mv['category']),
        'episodes' => []
    ];

    if (isset($json['episodes'])) {
        foreach ($json['episodes'] as $sv) {
            $parsed_eps = [];
            foreach ($sv['server_data'] as $ep) {
                $parsed_eps[] = [
                    'name' => $ep['name'],
                    'link' => $ep['link_m3u8'], // OPhim returns m3u8
                    'type' => 'link'
                ];
            }
            $data['episodes'][] = ['server_name' => $sv['server_name'], 'server_data' => $parsed_eps];
        }
    }

    return amurhin_save_movie_to_wp($data);
}

/**
 * 2. KKPHIM Handler
 */
function amurhin_crawl_kkphim($slug) {
    $url = "https://phimapi.com/phim/{$slug}";
    $response = wp_remote_get($url, array('timeout' => 15, 'sslverify' => false));
    if (is_wp_error($response)) return ['status' => false, 'msg' => 'API Error'];
    
    $json = json_decode(wp_remote_retrieve_body($response), true);
    if (!$json || !isset($json['movie'])) return ['status' => false, 'msg' => 'Invalid JSON'];

    $mv = $json['movie'];
    
    // Handles Image URLs (KKPhim uses phimimg.com for CDN usually)
    $thumb = $mv['thumb_url'];
    $poster = $mv['poster_url'];

    if (!empty($thumb) && strpos($thumb, 'http') === false) {
        $thumb = 'https://phimimg.com/' . $thumb;
    }
    if (!empty($poster) && strpos($poster, 'http') === false) {
        $poster = 'https://phimimg.com/' . $poster;
    }

    // Very similar complexity to OPhim
    $data = [
        'slug' => $mv['slug'],
        'name' => $mv['name'],
        'origin_name' => $mv['origin_name'],
        'content' => $mv['content'],
        'type' => $mv['type'],
        'status' => $mv['status'],
        'thumb' => $thumb,
        'poster' => $poster,
        'trailer' => isset($mv['trailer_url']) ? $mv['trailer_url'] : '',
        'time' => $mv['time'],
        'episode_total' => $mv['episode_total'],
        'quality' => $mv['quality'],
        'director' => $mv['director'],
        'actor' => $mv['actor'],
        'lang' => $mv['lang'],
        'year' => isset($mv['year']) ? $mv['year'] : '',
        'countries' => array_map(function($c){ return $c['name']; }, $mv['country']),
        'genres' => array_map(function($g){ return $g['name']; }, $mv['category']),
        'episodes' => []
    ];

    if (isset($json['episodes'])) {
        foreach ($json['episodes'] as $sv) {
            $parsed_eps = [];
            foreach ($sv['server_data'] as $ep) {
                // KKPhim generally same structure as OPhim
                $parsed_eps[] = [
                    'name' => $ep['name'],
                    'link' => $ep['link_m3u8'], 
                    'type' => 'link'
                ];
            }
            $data['episodes'][] = ['server_name' => $sv['server_name'], 'server_data' => $parsed_eps];
        }
    }

    return amurhin_save_movie_to_wp($data);
}

/**
 * 3. NGUONC Handler
 */
function amurhin_crawl_nguonc($slug) {
    $url = "https://phim.nguonc.com/api/film/{$slug}";
    $response = wp_remote_get($url, array('timeout' => 15, 'sslverify' => false));
    if (is_wp_error($response)) return ['status' => false, 'msg' => 'API Error'];
    
    $json = json_decode(wp_remote_retrieve_body($response), true);
    if (!$json || !isset($json['movie'])) return ['status' => false, 'msg' => 'Invalid JSON'];

    $mv = $json['movie'];
    
    // NguonC often has 'category' as 'category' key? Need verification.
    // Based on standard NguonC docs:
    $cats = [];
    if(isset($mv['category'])) {
        // sometimes simple array?
        // Let's assume standard object array similar to others or adjust?
        // NguonC often returns simple structure.
        foreach($mv['category'] as $c) $cats[] = $c['name']; 
    }
    
    $countries = [];
    if(isset($mv['country'])) {
        foreach($mv['country'] as $c) $countries[] = $c['name']; 
    }
    

    $data = [
        'slug' => $mv['slug'],
        'name' => $mv['name'],
        'origin_name' => $mv['original_name'] ?? $mv['origin_name'],
        'content' => $mv['description'] ?? $mv['content'],
        'type' => $mv['type'] ?? 'single', // Need careful mapping
        'status' => $mv['status'] ?? 'completed',
        'thumb' => $mv['thumb_url'],
        'poster' => $mv['poster_url'],
        'trailer' => $mv['trailer_url'] ?? '',
        'time' => $mv['time'] ?? '',
        'episode_total' => $mv['total_episodes'] ?? '??',
        'quality' => $mv['quality'],
        'actor' => $mv['actor'],
        'director' => $mv['director'],
        'lang' => $mv['language'] ?? 'Vietsub',
        'year' => $mv['category']['3']['name'] ?? '2025', // NguonC year logic is tricky, often in category? No, often separate or in cat.
        // NguonC usually provides year in 'category' sometimes if not explicit.
        // Let's rely on standard 'year' if invalid
        'year' => isset($mv['year']) ? $mv['year'] : '', // NguonC API often lack simplistic year?
        'countries' => $countries,
        'genres' => $cats,
        'episodes' => []
    ];
    
    // Logic for Year if missing in direct key for NguonC?
    // Often NguonC API structure: `movie`: { ... }
    
    if (isset($json['movie']['episodes'])) {
        foreach ($json['movie']['episodes'] as $sv) {
            $parsed_eps = [];
            foreach ($sv['items'] as $ep) {
                // NguonC uses 'embed' mostly but sometimes m3u8
                $parsed_eps[] = [
                    'name' => $ep['name'],
                    'link' => $ep['embed'], 
                    'type' => 'embed' // Usually iframe
                ];
            }
            $data['episodes'][] = ['server_name' => $sv['server_name'], 'server_data' => $parsed_eps];
        }
    }

    return amurhin_save_movie_to_wp($data);
}

/**
 * 4. IPHIM Handler
 */
function amurhin_crawl_iphim($slug) {
    $url = "https://iphim.cc/api/phim/{$slug}"; // Corrected per user step 1011
    $response = wp_remote_get($url, array('timeout' => 15, 'sslverify' => false));
    if (is_wp_error($response)) return ['status' => false, 'msg' => 'API Error'];
    
    $json = json_decode(wp_remote_retrieve_body($response), true);
    if (!$json || !isset($json['movie'])) return ['status' => false, 'msg' => 'Invalid JSON'];

    $mv = $json['movie'];
    
    // iPhim similar to OPhim structure usually?
    $data = [
        'slug' => $mv['slug'],
        'name' => $mv['name'],
        'origin_name' => $mv['origin_name'],
        'content' => $mv['content'],
        'type' => $mv['type'],
        'status' => $mv['status'],
        'thumb' => $mv['thumb_url'],
        'poster' => $mv['poster_url'],
        'trailer' => isset($mv['trailer_url']) ? $mv['trailer_url'] : '',
        'time' => $mv['time'],
        'episode_total' => $mv['episode_total'],
        'quality' => $mv['quality'],
        'actor' => $mv['actor'],
        'director' => $mv['director'],
        'lang' => $mv['lang'],
        'year' => isset($mv['year']) ? $mv['year'] : '',
        'countries' => array_map(function($c){ return $c['name']; }, $mv['country']),
        'genres' => array_map(function($g){ return $g['name']; }, $mv['category']),
        'episodes' => []
    ];

    if (isset($json['episodes'])) {
        foreach ($json['episodes'] as $sv) {
            $parsed_eps = [];
            foreach ($sv['server_data'] as $ep) {
                $parsed_eps[] = [
                    'name' => $ep['name'],
                    'link' => $ep['link_m3u8'], // iPhim often m3u8
                    'type' => 'link'
                ];
            }
            $data['episodes'][] = ['server_name' => $sv['server_name'], 'server_data' => $parsed_eps];
        }
    }

    return amurhin_save_movie_to_wp($data);
}
add_action('wp_ajax_amurhin_crawl_item', 'amurhin_crawl_item_ajax');

