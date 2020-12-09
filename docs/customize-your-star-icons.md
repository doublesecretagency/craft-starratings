---
description: Practically any HTML is acceptable. And since Font Awesome is natively included in the plugin, you can easily use any other Font Awesome icons.
---

# Customize your star icons

It's incredibly easy to customize your star icons...

## The Basics

### Example:

```twig
{% do craft.starRatings.setIcons({
    'full'  : '<i class="fa fa-thumbs-up"></i>',
    'half'  : '<i class="fa fa-thumbs-o-up"></i>',
    'empty' : '<i class="fa fa-circle-thin"></i>',
}) %}
```

### Results:

<img :src="$withBase('/images/custom-icons-14px.png')" class="dropshadow" alt="" style="margin-top:14px">

Practically any HTML is acceptable. And since **Font Awesome** is natively included in the plugin, you can easily use any other [Font Awesome icons!](https://fontawesome.com/icons)

:::warning Disabling Font Awesome
If you don't need the Font Awesome library to be run by the plugin, you can simply disable it on the plugin's Settings page.
:::

If you'd like to change the colors or other formatting, please read how to [customize your star CSS](/customize-your-star-css/).

## Advanced

You can change the size of your star components. Go to:

_Settings > Star Ratings > **Star Increment Size**_

Select whether you want the final star to appear in quarters, halves, or as a full star.

```twig
{% do craft.starRatings.setIcons({
    '0/4': '<i class="fa fa-thermometer-empty"></i>',
    '1/4': '<i class="fa fa-thermometer-quarter"></i>',
    '2/4': '<i class="fa fa-thermometer-half"></i>',
    '3/4': '<i class="fa fa-thermometer-three-quarters"></i>',
    '4/4': '<i class="fa fa-thermometer-full"></i>',
}) %}
```

Font Awesome doesn't have much in the way of quarter icons, so you may need to implement your own custom icons if you'd like to divide stars into quarters.

### Size Chart:

| Word      | Fraction | Will display
|:----------|:---------|:-------------
| `"empty"` | `"0/4"`  | _Empty star_
|           | `"1/4"`  | _One-quarter star_
| `"half"`  | `"2/4"`  | _Half star_
|           | `"3/4"`  | _Three-quarter star_
| `"full"`  | `"4/4"`  | _Full star_

Fractions must be also treated as strings (ie: wrapped in quotes). 
