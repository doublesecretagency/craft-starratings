---
description: If you want to know how many times an element has been rated, use the `totalVotes` method.
---

# Get total count of ratings cast

Getting the total number of ratings cast is easy...

```twig
craft.starRatings.totalVotes(elementId [, key = null])
```

If you are using an [optional key](/multiple-ratings-for-the-same-element/), you can specify that as the second parameter.

```twig
{% set totalVotesComfort  = craft.starRatings.totalVotes(hotel.id, 'comfortable') %}
{% set totalVotesClean    = craft.starRatings.totalVotes(hotel.id, 'clean') %}
{% set totalVotesFriendly = craft.starRatings.totalVotes(hotel.id, 'friendlyStaff') %}
```
