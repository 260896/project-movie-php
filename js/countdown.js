jQuery(document).ready(function ($) {
    function updateCountdowns() {
        $('.showtime-item').each(function () {
            const targetTime = parseInt($(this).data('time'));
            if (!targetTime) return;

            const now = Math.floor(Date.now() / 1000);
            const diff = targetTime - now;
            const timerElement = $(this).find('.timer');
            const badgeElement = $(this).find('.countdown-badge');

            if (diff > 0) {
                const hours = Math.floor(diff / 3600);
                const minutes = Math.floor((diff % 3600) / 60);
                const seconds = diff % 60;

                const display =
                    (hours < 10 ? '0' + hours : hours) + ':' +
                    (minutes < 10 ? '0' + minutes : minutes) + ':' +
                    (seconds < 10 ? '0' + seconds : seconds);

                timerElement.text(display);
                badgeElement.removeClass('now-playing').addClass('upcoming');
            } else {
                timerElement.text('ĐANG CHIẾU');
                badgeElement.removeClass('upcoming').addClass('now-playing');
            }
        });
    }

    // Run every second
    setInterval(updateCountdowns, 1000);
    updateCountdowns();
});
