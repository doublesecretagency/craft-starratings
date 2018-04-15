$(function () {
    // When max stars is changed, show warning
    $('.maxStarsAvailable').on('change', 'select', function () {
        $('.maxStarsAvailable p.warning.change-value').show();
    });
});