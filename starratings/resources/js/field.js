$('body').on('click', '.sr-star', function () {

	// Get info
	var starValue = $(this).data('rating');
	var $allStars = $(this).parent('div').children('.sr-star');

	// Mark selected stars
	$allStars.each(function () {
		$(this).removeClass('sr-user-rating sr-unrated');
		$(this).find('i').removeClass('fa-star fa-star-o');
		var starRating = $(this).data('rating');
		if (starRating <= starValue) {
			$(this).addClass('sr-user-rating');
			$(this).find('i').addClass('fa-star');
		} else {
			$(this).addClass('sr-unrated');
			$(this).find('i').addClass('fa-star-o');
		}
	});

	// Set input value
	$(this).siblings('input').val(starValue);

});