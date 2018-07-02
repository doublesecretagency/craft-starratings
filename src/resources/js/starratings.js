// Load AJAX library
var ajax = window.superagent;

// Star Ratings JS object
var starRatings = {
    starIcons: [],
    ratingChangeAllowed: false,
    // Rate an element
    rate: function (elementId, key, value, allowElementRating) {
        // Set stars
        var elementStars = Sizzle('.sr-element-'+this._setItemKey(elementId, key));
        // Set data
        var data = {
            'id': elementId,
            'key': key,
            'rating': value
        };
        // If element rating is allowed
        if (allowElementRating) {
            // Append CSRF Token
            data[window.csrfTokenName] = window.csrfTokenValue;
            // Vote via AJAX
            ajax
                .post('/actions/star-ratings/rate')
                .send(data)
                .type('form')
                .set('X-Requested-With','XMLHttpRequest')
                .end(function (response) {
                    var results = JSON.parse(response.text);
                    if (starRatings.devMode) {
                        console.log('['+elementId+']'+(key ? ' ['+key+']' : '')+' Rated '+value+' stars');
                        console.log(results);
                    }
                    var errorReturned = (typeof results === 'string' || results instanceof String);
                    // If no error message was returned
                    if (!errorReturned) {
                        var i; // Counter
                        var currentPosition;
                        // Remove existing rating
                        for (i = 0; i < elementStars.length; i++) {
                            starRatings._removeClass(elementStars[i], 'sr-avg-rating');
                            starRatings._removeClass(elementStars[i], 'sr-user-rating');
                            starRatings._removeClass(elementStars[i], 'sr-unrated');
                            if (!starRatings.ratingChangeAllowed) {
                                starRatings._removeClass(elementStars[i], 'sr-ratable');
                            }
                        }
                        // Adds new rating
                        for (i = 0; i < value; i++) {
                            elementStars[i].innerHTML = starRatings.starIcons['4/4'];
                            starRatings._addClass(elementStars[i], 'sr-user-rating');
                            currentPosition = (i+1);
                        }
                        // Fills remaining stars
                        for (i = currentPosition; i < elementStars.length; i++) {
                            elementStars[i].innerHTML = starRatings.starIcons['0/4'];
                            starRatings._addClass(elementStars[i], 'sr-unrated');
                        }
                    }
                })
            ;
        } else {
            if (this.devMode) {
                console.log('Rating this element is not permitted.');
            }
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
};