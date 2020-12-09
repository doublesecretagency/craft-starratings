---
description: You can output a set of static stars, which cannot be rated or changed.
---

# Output a set of locked stars

Render a set of stars to display a rating at the specified value...

```twig
craft.starRatings.lockedStars(anyNumber)
```

For example, if you needed to get the [collective average](/get-numerical-value-of-stars/) of your [element's attributes](/multiple-ratings-for-the-same-element/), it might look something like this:

```twig
{% set avgComfort  = craft.starRatings.avgRating(hotel.id, 'comfortable') %}
{% set avgClean    = craft.starRatings.avgRating(hotel.id, 'clean') %}
{% set avgFriendly = craft.starRatings.avgRating(hotel.id, 'friendlyStaff') %}

{% set avgEverything = (avgComfort + avgClean + avgFriendly) / 3 %}

<p>Grand Average: {{ craft.starRatings.lockedStars(avgEverything) }}</p>
```

Outputting `lockedStars` will render a normal set of stars, locked in at the value you've specified. It will not be possible for your users to click on stars in this set.
