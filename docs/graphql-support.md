---
description: As of v2.2, Star Ratings now supports GraphQL with an `avgRating` query and `rate` mutation. 
---

# GraphQL Support

## Sort Elements by Highest Rated

In order to **sort by highest rated**, order your query by `avgRating DESC`...

```graphql
query MyQuery {
  entries (section: "songs", orderBy: "avgRating DESC") {
    dateCreated @formatDateTime (format: "Y-m-d")
    title
    avgRating
  }
}
```

The `avgRating` value acts as a dynamically-added field on your query.

<img :src="$withBase('/images/graphql-orderBy-avgRating.png')" class="dropshadow" alt="" style="margin-top:10px; margin-bottom:10px;">

## Get Average Rating of an Element

In order to retrieve the **average rating** of a particular element, use the `avgRating` query...

| Variable    | Type     | Default      | Description
|:------------|:---------|:-------------|:------------
| `elementId` | _int_    | **required** | ID of the element.
| `key`       | _string_ | _null_       | Optional unique key.

```graphql
query MyQuery {
  avgRating(elementId: 101)
}
```

<img :src="$withBase('/images/graphql-avgRating.png')" class="dropshadow" alt="" style="max-width:570px; margin-top:10px; margin-bottom:50px;">

## Cast a Rating on an Element

In order to **cast** a new rating, use the `rate` mutation...

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

<img :src="$withBase('/images/graphql-rate.png')" class="dropshadow" alt="" style="max-width:570px; margin-top:10px;">

:::warning Defaults to Currently Logged-in User (if possible)
When no user ID is specified, the **currently logged in user** will automatically be used instead.

If your system is completely headless, you may still need to manually specify the `userID`.
:::
