---
description: Occasionally, you'll need the raw value of the rating, instead of simply drawing a set of stars on the page.
---

# Get numerical value of stars

Occasionally, you'll need the **raw value** of the rating, instead of simply drawing a set of stars on the page.

```twig
craft.starRatings.avgRating(elementId [, key = null])
```

This allows you to get the average rating of the specified element [(key optional)](/multiple-ratings-for-the-same-element/).

```twig
{% set avgComfort  = craft.starRatings.avgRating(hotel.id, 'comfortable') %}
{% set avgClean    = craft.starRatings.avgRating(hotel.id, 'clean') %}
{% set avgFriendly = craft.starRatings.avgRating(hotel.id, 'friendlyStaff') %}

{% set avgEverything = (avgComfort + avgClean + avgFriendly) / 3 %}

<p>Grand Average: {{ craft.starRatings.lockedStars(avgEverything) }}</p>
```

You'll notice that this is also a great case for [displaying stars with a fixed value](/output-a-set-of-locked-stars/).

## Via a Module or Plugin

You can also get the average rating for an element via a plugin or module.

```php
StarRatings::$plugin->starRatings_query->avgRating($elementId, $optionalKey)
```
