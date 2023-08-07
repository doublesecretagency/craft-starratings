# Changelog

## 2.3.3 - 2023-08-07

### Fixed
- Fixed bug when creating a new entry containing an Average Rating field. ([#29](https://github.com/doublesecretagency/craft-starratings/issues/29)) (thanks @JamesNock)

## 2.3.2 - 2023-04-19

### Fixed
- Fixed bug preventing anonymous users from voting. ([#28](https://github.com/doublesecretagency/craft-starratings/issues/28))

## 2.3.1 - 2022-08-03

### Fixed
- Fixed console conflict for rating elements.

## 2.3.0 - 2022-05-09

### Added
- Craft 4 compatibility.

## 2.2.2 - 2022-01-10

### Added
- Added a [setting](https://plugins.doublesecretagency.com/star-ratings/graphql-support/#enabling-graphql-support) to enable GraphQL support.

### Changed
- GraphQL support is now disabled by default.
- GraphQL support is now marked as experimental.
- New plugin icon.

## 2.2.1 - 2021-11-10

### Added
- Added the ability to sort by `avgRating` [via GraphQL](https://plugins.doublesecretagency.com/star-ratings/graphql-support/#sort-elements-by-highest-rated).
- Added the ability to get an element's `totalVotes` [via GraphQL](https://plugins.doublesecretagency.com/star-ratings/graphql-support/#get-total-votes-cast-on-an-element).

## 2.2.0 - 2021-02-19

### Added
- Added [GraphQL support](https://plugins.doublesecretagency.com/star-ratings/graphql-support) with an `avgRating` query and `rate` mutation.

## 2.1.3 - 2020-08-19

### Changed
- Craft 3.5 is now required.

### Fixed
- Adjusted raw HTML output on settings page.

## 2.1.2 - 2020-02-08

### Fixed
- Fixed PHP 7.4 compatibility issues.

## 2.1.1 - 2019-02-13

### Fixed
- Fixed migration bug on Craft 3.1.

## 2.1.0 - 2018-07-01

### Added
- Added ability to display quarter stars.
- Added “Star Increment Size” setting.

### Changed
- Removed "Allow half-stars" setting, in favor of “Star Increment Size” setting.

### Fixed
- Patched to run via CLI without errors.

## 2.0.0 - 2018-04-15

### Added
- Craft 3 compatibility.

## 1.3.0 - 2017-09-26

### Added
- New ["Rate"](https://plugins.doublesecretagency.com/star-ratings/rate-field-type) field type, for CP and front-end forms.
- New ["Average User Rating"](https://plugins.doublesecretagency.com/star-ratings/average-user-rating-field-type) field type, for viewing ratings averages in the CP.
- Ability to cast rating on behalf of a specific user (via PHP).
- Ability to see [rating of a specific user](https://plugins.doublesecretagency.com/star-ratings/get-rating-cast-by-a-specific-user).

### Changed
- DEPRECATED: `craft()->starRatings_rate->changeRating` (use `rate` instead).

## 1.2.4 - 2016-09-08

### Added
- Added `totalVotes` variable and service method.

## 1.2.3 - 2016-08-19

### Fixed
- Prevents console conflicts.

## 1.2.2 - 2016-06-14

### Changed
- Events now trigger for updated ratings as well.
- Events include `changedFrom` parameter, which is the previous rating value (if it exists).

## 1.2.1 - 2016-05-29

### Added
- Added [`onBeforeRate` and `onRate` events](https://plugins.doublesecretagency.com/star-ratings/events)
- Added `setIcons` variable and service method

### Changed
- Deprecated `setStarIcons` in favor of `setIcons`
- Updated Font Awesome (v4.6.3)

## 1.2.0 - 2015-12-14

### Added
- Craft 2.5 compatibility.
- Now accepts an optional "key" parameter, so you can [rate multiple things about the same element](https://plugins.doublesecretagency.com/star-ratings/multiple-ratings-for-the-same-element).
- Now possible to get [the numeric value of an element's average rating](https://plugins.doublesecretagency.com/star-ratings/get-numerical-value-of-stars).
- Now possible to [display a fixed set of stars at a specified value](https://plugins.doublesecretagency.com/star-ratings/output-a-set-of-locked-stars).

## 1.1.0 - 2015-05-24

### Added
- Now possible to [prevent rating for miscellaneous reasons](https://plugins.doublesecretagency.com/star-ratings/prevent-rating-for-miscellaneous-reasons).
- Now possible to [disable included JS or CSS](https://plugins.doublesecretagency.com/star-ratings/disable-js-or-css).

## 1.0.0 - 2015-05-19

Initial release.
