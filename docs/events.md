---
description: Star Ratings events follow the same basic pattern as standard Craft events.
---

# Events

Star Ratings events follow the same pattern as [standard Craft events.](https://craftcms.com/docs/3.x/extend/updating-plugins.html#events)

```php
use doublesecretagency\starratings\StarRatings;
use doublesecretagency\starratings\events\RateEvent;
use yii\base\Event;

// Do something BEFORE the rating is cast...
Event::on(
    StarRatings::class,
    StarRatings::EVENT_BEFORE_RATE, 
    function (RateEvent $event) {
        // See the complete list of parameters below
        $elementId = $event->id;
    }
);
```

```php
use doublesecretagency\starratings\StarRatings;
use doublesecretagency\starratings\events\RateEvent;
use yii\base\Event;

// Do something AFTER the rating is cast...
Event::on(
    StarRatings::class,
    StarRatings::EVENT_AFTER_RATE,
    function (RateEvent $event) {
        // See the complete list of parameters below
        $elementId = $event->id;
    }
);
```

Both events give you access to the `RateEvent` model in the `$event` variable. The contents of that variable will look like this...

| Parameter        | Type     | Description
|:-----------------|:---------|-------------
| `id`             | _int_    | Element ID of the element being rated.
| `key`            | _string_ or _null_ | Optional key for allowing [multiple rating types](/multiple-ratings-for-the-same-element/).
| `rating`         | _int_    | The value of the rating.
| `changedFrom`    | _int_    | The previous rating value (if it exists).
| `userId`         | _int_    | ID of the user casting a rating (if login is required to rate).
