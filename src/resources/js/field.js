function hasClass(el, className) {
    if (el instanceof Element) {
        return el.className.split(' ').indexOf(className) > -1;
    } else {
        return false;
    }
}

// https://jaketrent.com/post/addremove-classes-raw-javascript/
function addClass(el, className) {
    if (el.classList) {
        el.classList.add(className);
    } else if (!hasClass(el, className)) {
        el.className += " " + className;
    }
}

// https://jaketrent.com/post/addremove-classes-raw-javascript/
function removeClass(el, className) {
    if (el.classList) {
        el.classList.remove(className);
    } else if (hasClass(el, className)) {
        var reg = new RegExp('(\\s|^)' + className + '(\\s|$)');
        el.className=el.className.replace(reg, ' ');
    }
}

// ========================================================================= //

// On click event for stars
document.addEventListener('click', function (e) {

    // Get elements
    var fa   = e.target;
    var star = fa.parentNode;

    // Check click target
    if (hasClass(star, 'sr-star')) {

        // Get container
        var container = star.parentNode;

        // Ensure star set is ratable
        if (hasClass(container, 'sr-stars-container')) {

            // Get new rating
            var starValue = star.getAttribute('data-rating');

            // Get all stars in group
            var starGroup = star.parentNode.children;

            // Loop through all stars in group
            for (i in starGroup) {

                // Get individual element
                var s = starGroup[i];

                // If element is an input
                if (hasClass(s, 'sr-star-input')) {

                    // Set input value
                    s.value = starValue;

                // Else if element is a star
                } else if (hasClass(s, 'sr-star')) {

                    // Get star value
                    var fa = s.childNodes[0];
                    var sValue = s.getAttribute('data-rating');

                    // Get existing classes
                    var starClasses = s.className;
                    var faClasses   = fa.className;

                    // Remove existing classes
                    removeClass(s,  'sr-user-rating');
                    removeClass(s,  'sr-unrated');
                    removeClass(fa, 'fa-star');
                    removeClass(fa, 'fa-star-o');

                    // Add appropriate classes
                    if (sValue <= starValue) {
                        addClass(s,  'sr-user-rating');
                        addClass(fa, 'fa-star');
                    } else {
                        addClass(s,  'sr-unrated');
                        addClass(fa, 'fa-star-o');
                    }

                }

            }

        }

    }

});
