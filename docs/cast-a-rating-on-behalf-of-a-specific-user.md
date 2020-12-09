---
description: Via PHP, you can cast a rating on behalf of a specific user.
---

# Cast a rating on behalf of a specific user

Via PHP, you can cast a rating on behalf of a specific user...

```php
StarRatings::$plugin->starRatings_rate->rate($elementId, $key, $rating [, $userId = null])
```

If the `$userId` is omitted, the rating will be cast by the currently logged-in user.
