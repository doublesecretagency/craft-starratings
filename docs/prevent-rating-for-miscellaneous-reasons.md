---
description: It is possible to prevent the rating of an element for reasons beyond what is available "out of the box".
---

# Prevent rating for miscellaneous reasons

It is possible to prevent the rating of an element for reasons beyond what is available "out of the box".

An optional second parameter is available on the stars method:

```twig
{{ craft.starRatings.stars(elementId, allowElementRating = true) }}
```

By default, this parameter evaluates to `true`. Using your own custom Twig logic, it's possible for you to set this value to `false`, thereby preventing this element from being rated.

### Example:

```twig
{# No rating for Joe! #}

{% set isNotJoe = (currentUser.username != 'joesmith') %}

{{ craft.starRatings.stars(elementId, isNotJoe) }}
```

:::warning It only takes one...
It only takes a single reason for an element to be denied the ability to be rated.

 - Require login (from settings)
 - Allow rating changes (from settings)
 - Manual override (in Twig template)

The element will not be ratable if **any** of these triggers prevent it from being rated.
:::

If you have configured your element to have [multiple ratings available](/multiple-ratings-for-the-same-element/), then the "allowElementRating" would be specified **last**.

```twig
{{ craft.starRatings.stars(elementId [, key = null] [, allowElementRating = true]) }}
```
