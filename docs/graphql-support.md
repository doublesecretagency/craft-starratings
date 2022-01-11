---
description: As of v2.2, Star Ratings now supports GraphQL with an `avgRating` query and `rate` mutation. 
---

# GraphQL Support

:::warning Support for GraphQL is in BETA
GraphQL support is currently experimental. Please proceed with caution!
:::

## Enabling GraphQL support

Before you can enable GraphQL support for Star Ratings:
 - Your Craft site must be the [Pro edition](https://craftcms.com/knowledge-base/upgrading-to-craft-pro).
 - Your Craft site must have [GraphQL enabled](https://craftcms.com/docs/3.x/config/config-settings.html#enablegql).

If both of those criteria are met, you may then enable GraphQL support within Star Ratings.

Enable GraphQL support on the plugin's Settings page:

<img :src="$withBase('/images/settings-gql.png')" class="dropshadow" alt="" style="max-width:600px; margin-top:5px; margin-bottom:10px;">

With GraphQL support fully enabled, you will be able to use any of the following...

## Sort Elements by Highest Rated

In order to **sort by highest rated**, order your query by `avgRating DESC`...

<img :src="$withBase('/images/graphql-orderBy-avgRating.png')" class="dropshadow" alt="" style="margin-top:3px; margin-bottom:3px;">

The `avgRating` value acts as a dynamically-added field on your query.

```graphql
query MyQuery {
  entries (section: "songs", orderBy: "avgRating DESC") {
    dateCreated @formatDateTime (format: "Y-m-d")
    title
    avgRating
  }
}
```

## Get Average Rating of an Element

In order to retrieve the **average rating** of a particular element, use the `avgRating` query...

<img :src="$withBase('/images/graphql-avgRating.png')" class="dropshadow" alt="" style="max-width:570px; margin-top:3px; margin-bottom:2px;">

| Variable    | Type     | Default      | Description
|:------------|:---------|:-------------|:------------
| `elementId` | _int_    | **required** | ID of the element.
| `key`       | _string_ | _null_       | Optional unique key.

```graphql
query MyQuery {
  avgRating(elementId: 101)
}
```

## Get Total Votes Cast on an Element

In order to retrieve the **total number of votes** on a particular element, use the `totalVotes` query...

<img :src="$withBase('/images/graphql-totalVotes.png')" class="dropshadow" alt="" style="max-width:570px; margin-top:3px; margin-bottom:2px;">

| Variable    | Type     | Default      | Description
|:------------|:---------|:-------------|:------------
| `elementId` | _int_    | **required** | ID of the element.
| `key`       | _string_ | _null_       | Optional unique key.

```graphql
query MyQuery {
  totalVotes(elementId: 101)
}
```

## Cast a Rating on an Element

In order to **cast** a new rating, use the `rate` mutation...

<img :src="$withBase('/images/graphql-rate.png')" class="dropshadow" alt="" style="max-width:570px; margin-top:3px; margin-bottom:2px;">

| Variable    | Type     | Default      | Description
|:------------|:---------|:-------------|:------------
| `rating`    | _int_    | **required** | New rating for specified element.
| `elementId` | _int_    | **required** | ID of the element.
| `key`       | _string_ | _null_       | Optional unique key.
| `userId`    | _int_    | _null_       | ID of user casting rating.

```graphql
mutation MyMutation {
  rate(elementId: 101, rating: 5)
}
```

:::warning Defaults to Currently Logged-in User (if possible)
When no user ID is specified, the **currently logged in user** will automatically be used instead.

If your system is completely headless, you may still need to manually specify the `userID`.
:::
