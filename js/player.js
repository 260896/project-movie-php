jQuery(document).ready(function ($) {
    let activeHls = null;
    let mainVideo = null;

    function loadPlayer(post_id, server, episode, epName) {
        $('#current-ep-name').text(epName);
        $('#player-wrapper').fadeOut(200, function () {
            $(this).html('<div style="text-align:center;padding-top:20%;color:var(--primary);font-weight:bold;">ĐANG TẢI...</div>').fadeIn(200);
            $.ajax({
                url: themePlayer.ajaxurl,
                type: 'POST',
                data: {
                    action: 'load_player',
                    post_id: post_id,
                    server: server,
                    episode: episode
                },
                success: function (response) {
                    $('#player-wrapper').hide().html(response).fadeIn(500);

                    // Attach event listeners to the new video element
                    setTimeout(function () {
                        mainVideo = document.getElementById('movie-player');
                        if (mainVideo) {
                            // Skip Intro logic
                            if ($('#skip-intro').is(':checked')) {
                                mainVideo.currentTime = 90;
                            }

                            // Auto-next logic
                            mainVideo.onended = function () {
                                if ($('#auto-next').is(':checked')) {
                                    $('#btn-next').click();
                                }
                            };
                        }
                    }, 1000);
                }
            });
        });
    }

    // Handle episode clicks
    $(document).on('click', '.episode-item', function (e) {
        e.preventDefault();
        $('.episode-item').removeClass('active');
        $(this).addClass('active');
        let post_id = $(this).data('post-id');
        let server = $(this).data('server');
        let episode = $(this).data('episode');
        let name = $(this).data('name');
        let slug = $(this).data('slug');
        let movie_title = $(this).data('movie-title');

        loadPlayer(post_id, server, episode, name);

        // Update URL & Title
        if (slug) {
            let baseUrl = window.location.href.split('/sv')[0].split('?')[0];
            let newUrl = baseUrl.replace(/\/$/, '') + '/' + slug + '.html';
            window.history.pushState({ post_id, server, episode, name }, '', newUrl);
            document.title = movie_title + ' - ' + name;
        }
    });

    // Auto load episode on page load
    let wrapper = $('#player-wrapper');
    if (wrapper.length > 0 && wrapper.data('post-id')) {
        let post_id = wrapper.data('post-id');
        let server = wrapper.data('sv');
        let episode = wrapper.data('ep');

        // Find the active episode item to get the name
        let activeEp = $('.episode-item.active').first();
        if (activeEp.length > 0) {
            let epName = activeEp.data('name');
            let movie_title = activeEp.data('movie-title');
            loadPlayer(post_id, server, episode, epName);
            document.title = movie_title + ' - ' + epName;
        }
    }

    // Lights out
    $('#btn-lights, #lights-off-overlay').click(function () {
        $('body').toggleClass('lights-off');
    });

    // Navigation
    $('#btn-next').click(function () {
        let next = $('.episode-item.active').next('.episode-item');
        if (next.length > 0) {
            next.click();
        } else {
            // Try next server group
            let nextGroup = $('.episode-item.active').closest('.server-group').next('.server-group');
            if (nextGroup.length > 0) {
                nextGroup.find('.episode-item').first().click();
            }
        }
    });

    $('#btn-prev').click(function () {
        let prev = $('.episode-item.active').prev('.episode-item');
        if (prev.length > 0) {
            prev.click();
        } else {
            // Try prev server group
            let prevGroup = $('.episode-item.active').closest('.server-group').prev('.server-group');
            if (prevGroup.length > 0) {
                prevGroup.find('.episode-item').last().click();
            }
        }
    });

    // Follow & Rate placeholders
    $('#btn-follow').click(function () {
        alert('Đã thêm vào danh sách theo dõi!');
    });


});
