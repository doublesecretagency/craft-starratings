---
description: The stars are naturally formatted to display their various states. However, you are free to override and customize the CSS however you wish.
---

# Customize your star CSS

The stars are naturally formatted to display their various states. However, you are free to override and customize the CSS however you wish.

<img :src="$withBase('/images/starratings-example.png')" class="dropshadow" alt="">

## CSS classes

The main reason to override the star CSS would be to adjust their colors. Of course, any other CSS adjustments can be made at your discretion. If you'd like to change the icons being used, please read how to [customize your star icons](/customize-your-star-icons/).

| Class             | Default Color      | Applies to...
|:------------------|:-------------------|:--------------
| <span style="white-space:nowrap">`.sr-star`</span>        |                         | All stars, all the time, regardless of state.
| <span style="white-space:nowrap">`.sr-avg-rating`</span>  | `#d1202a`&nbsp;(red)    | Stars which represent the average rating across all users.
| <span style="white-space:nowrap">`.sr-user-rating`</span> | `#e5cf4b`&nbsp;(yellow) | Stars which represent the selected rating of the current user.
| <span style="white-space:nowrap">`.sr-unrated`</span>     | `#cdcdcd`&nbsp;(grey)   | Any stars which are greater than the average or user total.
