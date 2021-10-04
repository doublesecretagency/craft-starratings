---
description: You can sort your results to display the highest rated elements first! Fetch your ECM just as you normally would, then pass the ECM into the sort method.
---

# Sort by highest rated

Once your elements are being rated, you'll likely want to know which items are the most popular.

## Basic Sorting

To sort by highest rated, simply order by `avgRating DESC`...

```twig
{# Get all songs, sorted by highest average rating #}
{% set favoriteSongs = craft.entries
    .section('songs')
    .orderBy('avgRating DESC')
    .all() %}
```

You can apply `.orderBy('avgRating DESC')` to any ordinary element query.

:::warning Field Handle Conflict
If you have a real field with the handle of `avgRating`, you may see a conflict. To resolve any issues, simply change the existing field handle.
:::

## Using a Key

If sorting with an optional [key](/multiple-ratings-for-the-same-element/), the process is just slightly more complicated...

```twig
{# Create a query as you normally would #}
{% set hotels = craft.entries.section('hotels') %}

{# Pass the query into the `sort` method #}
{% do craft.starRatings.sort(hotels, 'comfortable') %}

{% for entry in hotels.all() %}
    {# Loop over highest rated, sorted by "comfortable" #}
{% endfor %}
```
