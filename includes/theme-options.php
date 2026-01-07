<?php
/**
 * includes/theme-options.php
 * Trang cấu hình theme: Logo, Social, SEO, Footer...
 */

function amurhin_theme_options_menu() {
    add_menu_page(
        'Cấu hình Theme',
        'Theme Options',
        'manage_options',
        'amurhin-options',
        'amurhin_theme_options_page',
        'dashicons-admin-generic',
        60
    );
}
add_action('admin_menu', 'amurhin_theme_options_menu');

function amurhin_theme_options_page() {
    if (isset($_POST['amurhin_save_options'])) {
        check_admin_referer('amurhin_options_nonce');
        $options = array(
            'logo' => esc_url_raw($_POST['logo']),
            'favicon' => esc_url_raw($_POST['favicon']),
            'facebook' => esc_url_raw($_POST['facebook']),
            'youtube' => esc_url_raw($_POST['youtube']),
            'seo_title' => sanitize_text_field($_POST['seo_title']),
            'seo_desc' => sanitize_textarea_field($_POST['seo_desc']),
            'footer_text' => wp_kses_post($_POST['footer_text']),
            'autoplay' => isset($_POST['autoplay']) ? 1 : 0,
            'auto_next' => isset($_POST['auto_next']) ? 1 : 0,
        );
        update_option('amurhin_options', $options);
        echo '<div class="notice notice-success is-dismissible" style="margin-left: 260px; margin-top: 20px;"><p>Đã lưu cài đặt thành công!</p></div>';
    }

    $options = get_option('amurhin_options', []);
    ?>
    <style>
        /* Theme Options Container */
        .amurhin-options-wrap {
            display: flex;
            background: #fff;
            margin: 20px 20px 0 0;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            min-height: 600px;
        }

        /* Sidebar Styling */
        .amurhin-sidebar {
            width: 240px;
            background: #1e1e1e;
            color: #ccc;
            flex-shrink: 0;
        }
        .amurhin-header {
            padding: 20px;
            background: #000;
            display: flex;
            align-items: center;
            border-bottom: 1px solid #333;
        }
        .amurhin-header h2 {
            color: #fff;
            margin: 0;
            font-size: 18px;
            font-weight: 600;
            display: flex;
            align-items: center;
        }
        .amurhin-logo-icon {
            width: 32px;
            height: 32px;
            margin-right: 10px;
            background: linear-gradient(45deg, #ff6b6b, #ff8e53);
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: white;
            font-size: 16px;
        }
        .amurhin-nav {
            list-style: none;
            margin: 0;
            padding: 0;
        }
        .amurhin-nav li {
            margin: 0;
            border-bottom: 1px solid #2a2a2a;
        }
        .amurhin-nav li a {
            display: block;
            padding: 15px 20px;
            color: #999;
            text-decoration: none;
            transition: all 0.2s;
            font-weight: 500;
            display: flex;
            align-items: center;
        }
        .amurhin-nav li a:hover, .amurhin-nav li a.active {
            background: #2c2c2c;
            color: #fff;
            border-left: 4px solid #ff6b6b;
        }
        .amurhin-nav li a .dashicons {
            margin-right: 10px;
        }

        /* Content Styling */
        .amurhin-content {
            flex-grow: 1;
            padding: 40px;
            background: #f9f9f9;
        }
        .amurhin-tab-content {
            display: none;
            animation: fadeIn 0.3s;
        }
        .amurhin-tab-content.active {
            display: block;
        }
        .amurhin-section-title {
            font-size: 24px;
            margin: 0 0 30px 0;
            padding-bottom: 15px;
            border-bottom: 2px solid #e5e5e5;
            color: #23282d;
        }
        
        /* Form Elements */
        .form-table th { padding: 20px 10px 20px 0; width: 200px; }
        .form-table td { padding: 15px 10px; }
        .amurhin-input {
            width: 100%;
            max-width: 500px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-shadow: inset 0 1px 2px rgba(0,0,0,0.05);
        }
        .amurhin-textarea {
            width: 100%;
            max-width: 500px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            height: 120px;
        }
        .amurhin-save-bar {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e5e5;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(5px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>

    <div class="amurhin-options-wrap">
        <!-- Sidebar -->
        <div class="amurhin-sidebar">
            <div class="amurhin-header">
                <h2><span class="amurhin-logo-icon">AH</span> AmurHin Options</h2>
            </div>
            <ul class="amurhin-nav">
                <li><a href="#tab-general" class="active"><span class="dashicons dashicons-admin-settings"></span> General Settings</a></li>
                <li><a href="#tab-header"><span class="dashicons dashicons-align-center"></span> Header</a></li>
                <li><a href="#tab-footer"><span class="dashicons dashicons-align-wide"></span> Footer</a></li>
                <li><a href="#tab-seo"><span class="dashicons dashicons-search"></span> SEO Optimization</a></li>
                <li><a href="#tab-player"><span class="dashicons dashicons-video-alt3"></span> Player Settings</a></li>
                <li><a href="#tab-social"><span class="dashicons dashicons-share"></span> Social Media</a></li>
<li><a href="#tab-crawler"><span class="dashicons dashicons-admin-tools"></span> Crawler</a></li>
            </ul>
        </div>

        <!-- Content Area -->
        <div class="amurhin-content">
            <form method="post" action="">
                <?php wp_nonce_field('amurhin_options_nonce'); ?>

                <!-- General Tab -->
                <div id="tab-general" class="amurhin-tab-content active">
                    <h2 class="amurhin-section-title">General Settings</h2>
                    <table class="form-table">
                        <tr valign="top">
                            <th scope="row">Logo URL</th>
                            <td>
                                <input type="text" name="logo" value="<?php echo esc_attr($options['logo'] ?? ''); ?>" class="amurhin-input" />
                                <p class="description">Đường dẫn đầy đủ tới file Logo của bạn.</p>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row">Favicon URL</th>
                            <td><input type="text" name="favicon" value="<?php echo esc_attr($options['favicon'] ?? ''); ?>" class="amurhin-input" /></td>
                        </tr>
                    </table>
                </div>

                <!-- Header Tab -->
                <div id="tab-header" class="amurhin-tab-content">
                    <h2 class="amurhin-section-title">Header Settings</h2>
                    <p>Cài đặt hiển thị cho phần đầu trang (Sẽ cập nhật thêm).</p>
                </div>

                <!-- Footer Tab -->
                <div id="tab-footer" class="amurhin-tab-content">
                    <h2 class="amurhin-section-title">Footer Settings</h2>
                    <table class="form-table">
                        <tr valign="top">
                            <th scope="row">Footer Copyright</th>
                            <td>
                                <textarea name="footer_text" class="amurhin-textarea"><?php echo esc_textarea($options['footer_text'] ?? ''); ?></textarea>
                                <p class="description">Hỗ trợ mã HTML cơ bản.</p>
                            </td>
                        </tr>
                    </table>
                </div>

                <!-- SEO Tab -->
                <div id="tab-seo" class="amurhin-tab-content">
                    <h2 class="amurhin-section-title">SEO Optimization</h2>
                    <table class="form-table">
                        <tr valign="top">
                            <th scope="row">Homepage Title</th>
                            <td><input type="text" name="seo_title" value="<?php echo esc_attr($options['seo_title'] ?? ''); ?>" class="amurhin-input" /></td>
                        </tr>
                        <tr valign="top">
                            <th scope="row">Homepage Description</th>
                            <td><textarea name="seo_desc" class="amurhin-textarea"><?php echo esc_textarea($options['seo_desc'] ?? ''); ?></textarea></td>
                        </tr>
                    </table>
                </div>

                <!-- Player Tab -->
                <div id="tab-player" class="amurhin-tab-content">
                    <h2 class="amurhin-section-title">Player Settings</h2>
                    <table class="form-table">
                        <tr valign="top">
                            <th scope="row">Tự động phát (Autoplay)</th>
                            <td>
                                <label>
                                    <input type="checkbox" name="autoplay" value="1" <?php checked($options['autoplay'] ?? 0, 1); ?>>
                                    Bật tự động phát video khi tải trang
                                </label>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row">Tự động chuyển tập (Auto-next)</th>
                            <td>
                                <label>
                                    <input type="checkbox" name="auto_next" value="1" <?php checked($options['auto_next'] ?? 0, 1); ?>>
                                    Bật tự động chuyển tập tiếp theo khi hết phim
                                </label>
                            </td>
                        </tr>
                    </table>
                </div>

                <!-- Social Tab -->
                <div id="tab-social" class="amurhin-tab-content">
                    <h2 class="amurhin-section-title">Social Media</h2>
                    <table class="form-table">
                        <tr valign="top">
                            <th scope="row">Facebook Fanpage</th>
                            <td><input type="text" name="facebook" value="<?php echo esc_attr($options['facebook'] ?? ''); ?>" class="amurhin-input" /></td>
                        </tr>
                        <tr valign="top">
                            <th scope="row">YouTube Channel</th>
                            <td><input type="text" name="youtube" value="<?php echo esc_attr($options['youtube'] ?? ''); ?>" class="amurhin-input" /></td>
                        </tr>
                    </table>
                </div>

                <div class="amurhin-save-bar">
                    <input type="submit" name="amurhin_save_options" class="button button-primary button-hero" value="Lưu tất cả cài đặt" />
                </div>
            </form>
<div id="tab-crawler" class="amurhin-tab-content">
    <h2 class="amurhin-section-title">Crawler Settings</h2>
    <?php amurhin_crawler_page(); ?>
</div>
        </div>
    </div>

    <script>
    jQuery(document).ready(function($) {
        $('.amurhin-nav a').click(function(e) {
            e.preventDefault();
            
            // Remove active class from all links and contents
            $('.amurhin-nav a').removeClass('active');
            $('.amurhin-tab-content').removeClass('active');
            
            // Add active class to clicked link
            $(this).addClass('active');
            
            // Show corresponding tab content
            var target = $(this).attr('href');
            $(target).addClass('active');
        });
    });
    </script>
    <?php
}

// Helper to get option
function amurhin_get_option($key, $default = '') {
    $options = get_option('amurhin_options', []);
    return $options[$key] ?? $default;
}
