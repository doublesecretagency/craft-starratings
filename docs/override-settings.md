---
description: It's possible to override the plugin's settings through an environment-aware config file.
---

# Override settings

You can set the plugin's config settings via a file in your project's `/config/` folder. Similar to the default `general.php` and `db.php`, create a config file called `star-ratings.php`. Once you've created that, you can override any of the default settings values...

```php
return [
    'maxStarsAvailable' => 5,
    'requireLogin' => true,
    'allowHalfStars' => true,
    'allowRatingChange' => true,
    'allowFontAwesome' => true,
    'enableGql' => false,
    'keepRatingLog' => false,
];
```

It's possible to override only the settings that you want to change. You can also override settings based on the current environment...

```php
return [
    // All environments
    '*' => [
        'keepRatingLog' => false
    ],
    // Production environment only
    'production' => [
        'keepRatingLog' => true
    ]
];
```
