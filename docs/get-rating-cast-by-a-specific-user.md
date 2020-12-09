---
description: If you need to find out how a specific user cast their rating, you can do so with the `userRating` variable.
---

# Get rating cast by a specific user

Find out exactly what rating a specific user gave to a specific element...

```twig
craft.starRatings.userRating(userId, elementId [, key = null])
```
| Parameter   | Description 
|:------------|:------------
| `userId`    | ID of user who cast rating
| `elementId` | ID of element being rated
| `key`       | _(optional)_ For elements with [multiple aspects](/multiple-ratings-for-the-same-element/)

The same can be done via PHP...

```php
StarRatings::$plugin->starRatings_query->userRating($userId, $elementId [, $key = null])
```
