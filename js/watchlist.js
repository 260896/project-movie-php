jQuery(document).ready(function ($) {
    const STORAGE_KEY = 'amurhin_watchlist';

    // Helper: Get List
    function getWatchlist() {
        return JSON.parse(localStorage.getItem(STORAGE_KEY) || '[]');
    }

    // Helper: Save List
    function saveWatchlist(list) {
        localStorage.setItem(STORAGE_KEY, JSON.stringify(list));
        renderDropdown();
        updateButtons();
    }

    // Toggle Dropdown
    $('#watchlist-toggle').click(function (e) {
        e.stopPropagation();
        $('.watchlist-dropdown').fadeToggle(200);
    });

    $(document).click(function () {
        $('.watchlist-dropdown').fadeOut(200);
    });

    $('.watchlist-dropdown').click(function (e) {
        e.stopPropagation();
    });

    // Render Dropdown
    function renderDropdown() {
        const list = getWatchlist();
        $('.watchlist-count').text(list.length);
        const $ul = $('#watchlist-items');
        $ul.empty();

        if (list.length === 0) {
            $ul.html('<li class="empty-message">Chưa có phim nào!</li>');
            return;
        }

        list.forEach(item => {
            $ul.append(`
                <li class="watchlist-item">
                    <a href="${item.url}" class="item-link">
                        <img src="${item.thumb}" alt="${item.title}">
                        <div class="item-info">
                            <span class="item-title">${item.title}</span>
                        </div>
                    </a>
                    <button class="remove-item" data-id="${item.id}">&times;</button>
                </li>
            `);
        });
    }

    // Clear All
    // Clear All (Delegate to Dropdown)
    $('.watchlist-dropdown').on('click', '#clear-watchlist', function (e) {
        if (confirm('Bạn có chắc muốn xóa toàn bộ danh sách?')) {
            saveWatchlist([]);
        }
    });

    // Remove Item (Delegate to Dropdown)
    $('.watchlist-dropdown').on('click', '.remove-item', function (e) {
        const id = $(this).data('id');
        let list = getWatchlist();
        list = list.filter(item => item.id != id);
        saveWatchlist(list);
    });

    // Add/Toggle Button Click
    $(document).on('click', '.btn-watchlist', function (e) {
        e.preventDefault();
        const $btn = $(this);
        const id = $btn.data('id');
        const title = $btn.data('title');
        const thumb = $btn.data('thumb');
        const url = $btn.data('url');

        let list = getWatchlist();
        const exists = list.some(item => item.id == id);

        if (exists) {
            // Remove
            list = list.filter(item => item.id != id);
            $btn.removeClass('active');
            $btn.html('<i class="fas fa-heart"></i> Theo dõi');
        } else {
            // Add
            list.push({ id, title, thumb, url });
            $btn.addClass('active');
            $btn.html('<i class="fas fa-heart"></i> Đã theo dõi');

            // Pulse animation
            $btn.addClass('pulse');
            setTimeout(() => $btn.removeClass('pulse'), 500);
        }
        saveWatchlist(list);
    });

    // Update Buttons on Load
    function updateButtons() {
        const list = getWatchlist();
        $('.btn-watchlist').each(function () {
            const id = $(this).data('id');
            if (list.some(item => item.id == id)) {
                $(this).addClass('active');
                $(this).html('<i class="fas fa-heart"></i> Đã theo dõi');
            } else {
                $(this).removeClass('active');
                $(this).html('<i class="fas fa-heart"></i> Theo dõi');
            }
        });
    }

    // Init
    renderDropdown();
    updateButtons();
});
