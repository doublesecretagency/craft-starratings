// Load AJAX library
var ajax = window.superagent;

// Star Ratings JS object
var starRatings = {
	ratingChangeAllowed: false,
	// Rate an element
	rate: function (elementId, key, value, allowElementRating) {
		// Set stars
		var elementStars = Sizzle('.sr-element-'+this._setItemKey(elementId, key));
		var alreadyRated = Sizzle('.sr-user-rating.sr-element-'+this._setItemKey(elementId, key));
		// Set data
		var data = {
			'id': elementId,
			'key': key,
			'rating': value
		};
		// Initialize action
		var action = null;
		// If element rating is not allowed
		if (!allowElementRating) {
			if (this.devMode) {
				console.log('Rating this element is not permitted.');
			}
		} else {
			// If element has not been rated yet
			if (!alreadyRated.length) {
				// Rate new element
				action = '/actions/starRatings/rate';
			} else if (this.ratingChangeAllowed) {
				// Change rating
				action = '/actions/starRatings/change';
				data.oldRating = alreadyRated.length;
			} else {
				// Rating changes are not allowed
				if (this.devMode) {
					console.log('Rating changes are disabled.');
				}
			}
		}
		// If action specified
		if (action) {
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
						console.log('['+elementId+']'+(key ? ' ['+key+']' : '')+' Rated '+value+' stars');
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
							if (!starRatings.ratingChangeAllowed) {
								starRatings._removeClass(elementStars[i], 'sr-ratable');
							}
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
		}
	},
	// Generate combined item key
	_setItemKey: function (elementId, key) {
		return elementId+(key ? '-'+key : '');
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