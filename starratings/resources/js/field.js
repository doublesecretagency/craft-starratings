function hasClass(el, className) {
	if (el instanceof Element) {
		return el.className.split(' ').indexOf(className) > -1;
	} else {
		return false;
	}
}

document.addEventListener('click', function (e) {

	// Get elements
	var fa   = e.target;
	var star = fa.parentNode;

	// Check click target
	if (hasClass(star, 'sr-star')) {

		var starValue = star.getAttribute('data-rating');
		var starGroup = star.parentNode.children;

		// console.log(starGroup);

		for (i in starGroup) {
			var s = starGroup[i];
			if (hasClass(s, 'sr-star')) {
				console.log(s);
			}
		}

	}

});

// 		$(this).removeClass('sr-user-rating sr-unrated');
// 		$(this).find('i').removeClass('fa-star fa-star-o');
// 		var starRating = $(this).data('rating');
// 		if (starRating <= starValue) {
// 			$(this).addClass('sr-user-rating');
// 			$(this).find('i').addClass('fa-star');
// 		} else {
// 			$(this).addClass('sr-unrated');
// 			$(this).find('i').addClass('fa-star-o');
// 		}

// 	// Set input value
// 	$(this).siblings('input').val(starValue);