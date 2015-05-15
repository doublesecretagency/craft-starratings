
$(function () {
	// When max stars is changed, show warning
	$('#settings-maxStarsAvailable').on('change', 'select', function () {
		$('#settings-maxStarsAvailable p.warning').show();
	});
});