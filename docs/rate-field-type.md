---
description: This fieldtype can do two things... (1) give a ratings field to your content authors, or (2) create a custom &quot;Reviews&quot; section with a front-end entry form.
---

# "Rate" field type

It's possible to use a rating field in a standard form...

## Back-End Form

You can use the "Rate" field type to provide a simple rating mechanism to the **content author**. While the stars looks very similar to the typical star ratings produced on the front-end, the underlying logic is different.

<img :src="$withBase('/images/rating-field.png')" class="dropshadow" alt="">

```twig
{{ entry.myRatingField }}
```

When using this field type, the value will be controlled like a normal Craft field. It's value will be stored in the database as a simple integer.

If you'd like to do complex calculations with this field value, you'd do those calculations in Twig. Once you have calculated the value needed, you can use it to render a set of [locked stars](/output-a-set-of-locked-stars/).

```twig
{% set ratingValue = entry.myRatingField %}

// do interesting math

{{ craft.starRatings.lockedStars(newValue) }}
```

## Front-End Form

To use your Star Rating field in a [front-end form](https://craftcms.com/knowledge-base/entry-form), simply include something like this:

```twig
{{ craft.starRatings.formField('myRatingField') }}
```

The **formField** method accepts three parameters:

```twig
{{ craft.starRatings.formField(fieldHandle, existingValue = 0, namespace = 'fields') }}
```

| Parameter       | Description
|:----------------|:------------
| `fieldHandle`   | The handle of your ratings field.
| `existingValue` | The current value of your field. (default 0)
| `namespace`     | The namespace of your field. (default "fields")

:::warning Matrix fields only
It's highly unlikely that you'll ever need to use the `namespace` parameter. This only exists to accommodate situations in which the rating field exists within a _matrix block_.

For the vast majority of cases, the default namespace will work perfectly.
:::
