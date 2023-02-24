# Aimes_QualityPatchesUi

!["Supported Magento Version"][magento-badge] !["Latest Release"][release-badge]

* Compatible with _Magento Open Source_ and _Adobe Commerce_ `2.4.x`

## Features
- Display Magento Quality Patch Status without need for CLI access

## Requirements

* Magento Open Source or Adobe Commerce version `2.4.x`

## Installation

Please install this module via Composer. This module is hosted on [Packagist][packagist].

* `composer require aimes/module-quality-patches-ui`
* `bin/magento module:enable Aimes_QualityPatchesUi`
* `bin/magento setup:upgrade`

## Usage
Navigate to `Reports -> Patch Status -> Quality Patches` in the admin area

> NOTE: Filtering and sorting are not currently supported

## Preview
![preview](https://user-images.githubusercontent.com/4225347/221297200-e22477f1-75fa-4b38-804e-4ac6fe6aad7d.png)

## Planned Improvements
- Working sorting
- Working filtering
- Create separate grid to display Composer patch status (eg. via [`cweagans/composer-patches`][composer-patches])

## Licence
[GPLv3][gpl] © [Rob Aimes][author]

[magento-badge]:https://img.shields.io/badge/magento-2.3.x%20%7C%202.4.x-orange.svg?logo=magento&style=for-the-badge
[release-badge]:https://img.shields.io/github/v/release/robaimes/module-qual
[packagist]:https://packagist.org/packages/aimes/module-quality-patches-ui
[gpl]:https://www.gnu.org/licenses/gpl-3.0.en.html
[author]:https://aimes.dev/
[composer-patches]:https://github.com/cweagans/composer-patches
