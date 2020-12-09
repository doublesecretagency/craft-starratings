---
description: It's possible to allow ratings across various aspects of the same element.
---

# Multiple ratings for the same element

It's possible to allow ratings across various aspects of the same element.

For example, if you have a section for Hotels, you can allow star ratings for "Comfort", "Cleanliness", "Friendliness", etc.

```twig
{{ craft.starRatings.stars(hotel.id, 'comfortable') }}
{{ craft.starRatings.stars(hotel.id, 'clean') }}
{{ craft.starRatings.stars(hotel.id, 'friendlyStaff') }}
```

## Skippable Parameter

If you are not supplying multiple ratings for the same element, you can skip this parameter to control [whether the element is ratable or not](/prevent-rating-for-miscellaneous-reasons/).

```twig
{{ craft.starRatings.stars(elementId [, key = null] [, allowElementRating = true]) }}
```
