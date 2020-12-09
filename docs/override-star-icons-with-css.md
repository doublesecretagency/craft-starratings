---
description: There are many CSS classes which are automatically applied to stars when they are rendered. You can use these classes to manipulate your stars further via CSS.
---

# Override star icons with CSS

There are many [CSS classes](/customize-your-star-css/) which are automatically applied to stars when they are rendered. You can use these classes to manipulate your stars further via CSS.

For example, if you've got different sets of stars for [various aspects of your element](/multiple-ratings-for-the-same-element/), then you can use CSS to change the icons for specified sets.

### CSS

```css
/* Default icons (stars) */
.stars-icon::before {
    content: "\22C6"
}
.sr-user-rating .stars-icon,
.sr-avg-rating  .stars-icon {
    color: #e5cf4b;
}

/* Override icons (hearts) */
#stars-as-hearts .stars-icon::before {
    content: "\2665"
}
#stars-as-hearts .sr-user-rating .stars-icon,
#stars-as-hearts .sr-avg-rating  .stars-icon {
    color: #d1202a;
}
```

### Twig

```twig
{% do craft.starRatings.setStarIcons({
    full  : '<i class="stars-icon"></i>',
    empty : '<i class="stars-icon"></i>',
}) %}

<div id="stars-as-stars">
    {{ craft.starRatings.stars(entry.id, 'feature-1') }}
</div>
<div id="stars-as-hearts">
    {{ craft.starRatings.stars(entry.id, 'feature-2') }}
</div>
```
