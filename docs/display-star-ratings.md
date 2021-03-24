---
description: It's quite easy to display a row of stars. It will display the current user's existing rating, or fallback to the average rating of all users.
---

# Display star ratings

Displaying a row of stars is very simple...

```twig
{{ craft.starRatings.stars(elementId [, key = null]) }}
```

This will output a complete row of stars, with the rating relevant to that particular element.

The "key" parameter is optional. Use it only if you need to [rate multiple things about the same element](/multiple-ratings-for-the-same-element/).

### Example:

```twig
<table>
    {% for entry in craft.entries.section('musicCollection').all() %}
        <tr>
            <td>{{ entry.title }}</td>
            <td>{{ craft.starRatings.stars(entry.id) }}</td>
        </tr>
    {% endfor %}
</table>
```

### Results:

 - If the user **has already rated** this element, the stars will display the user's selected rating.
 - If the user **has not rated** this element, the stars will display the average rating of all users.

:::warning Prevent rating for miscellaneous reasons
If you have an abstract reason for wanting rating to be disabled on an element, it's possible to [prevent rating for miscellaneous reasons...](/prevent-rating-for-miscellaneous-reasons/)
:::
