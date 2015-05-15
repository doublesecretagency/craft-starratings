// Load AJAX library
var ajax = window.superagent;

// Star Ratings JS object
var starRatings = {
	ratingChangeAllowed: false,
	// Rate an element
	rate: function (elementId, value) {
		//if (this.devMode) {
		//	console.log('['+elementId+'] Rated '+value+' stars');
		//}
		// Set stars
		var elementStars = Sizzle('.sr-element-'+elementId);
		var alreadyRated = Sizzle('.sr-user-rating.sr-element-'+elementId);
		// Set data
		var data = {
			'id': elementId,
			'rating': value
		};
		// If element has not been rated yet
		if (!alreadyRated.length) {
			// Rate new element
			var action = '/actions/starRatings/rate';
		} else if (this.ratingChangeAllowed) {
			// Change rating
			var action = '/actions/starRatings/change';
			data.oldRating = alreadyRated.length;
		} else {
			// Rating changes are not allowed
			if (this.devMode) {
				console.log('Rating changes are disabled.');
			}
			return;
		}
		// Append CSRF Token
		data[window.csrfTokenName] = window.csrfTokenValue;
		// Vote via AJAX
		ajax
			.post(action)
			.send(data)
			.type('form')
			.set('X-Requested-With','XMLHttpRequest')
			.end(function (response) {
				var results = JSON.parse(response.text);
				if (starRatings.devMode) {
					console.log(results);
				}
				var errorReturned = (typeof results == 'string' || results instanceof String);
				// If no error message was returned
				if (!errorReturned) {
					var currentPosition;
					// Remove existing rating
					for (var i = 0; i < elementStars.length; i++) {
						starRatings._removeClass(elementStars[i], 'sr-avg-rating');
						starRatings._removeClass(elementStars[i], 'sr-user-rating');
						starRatings._removeClass(elementStars[i], 'sr-unrated');
					}
					// Adds new rating
					for (var i = 0; i < value; i++) {
						elementStars[i].innerHTML = starRatings.starIconFull;
						starRatings._addClass(elementStars[i], 'sr-user-rating');
						currentPosition = (i+1);
					}
					// Fills remaining stars
					for (var i = currentPosition; i < elementStars.length; i++) {
						elementStars[i].innerHTML = starRatings.starIconEmpty;
						starRatings._addClass(elementStars[i], 'sr-unrated');
					}
				}
			})
		;
	},
	// Remove class from star
	_removeClass: function (star, cssClass) {
		star.className = star.className.replace(cssClass, '');
	},
	// Add class to star
	_addClass: function (star, cssClass) {
		star.className = star.className.trim() + ' ' + cssClass;
	}
}