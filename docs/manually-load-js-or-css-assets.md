---
description: If your JS or CSS assets are not being loaded automatically, it's easy to load them manually.
---

# Manually load JS or CSS assets

If you have disabled automatic loading of JS or CSS, it's easy to load those assets manually.

```twig
{% do view.registerAssetBundle("doublesecretagency\\starratings\\web\\assets\\JsAssets") %}
{% do view.registerAssetBundle("doublesecretagency\\starratings\\web\\assets\\CssAssets") %}
{% do view.registerAssetBundle("doublesecretagency\\starratings\\web\\assets\\FontAwesomeAssets") %}
```

Feel free to load only the assets you need. The JS is more or less required for everything to function normally. However, you may opt to skip the FontAwesome library, or the entire native CSS.
