# Aimes_QualityPatchesUi
!["Supported Magento Version"][magento-badge] !["Latest Release"][release-badge]

* Compatible with _Magento Open Source_ and _Adobe Commerce_ `2.4.x`

## Features
- Display Magento Quality Patch Status as a grid, without need for CLI access, in the admin panel
- Notify users of any updates available to the following packages (when viewing the grid), as a new version may contain new patches:
  - `magento/magento-cloud-patches`
  - `magento/quality-patches`

## Requirements
* Magento Open Source or Adobe Commerce version `2.4.x`

## Installation
Please install this module via Composer. This module is hosted on [Packagist][packagist].

* `composer require aimes/module-quality-patches-ui`
* `bin/magento module:enable Aimes_QualityPatchesUi`
* `bin/magento setup:upgrade`

## Usage
Navigate to `Reports -> Patch Status -> Quality Patches` in the admin area

## Preview
![preview](https://user-images.githubusercontent.com/4225347/222785352-a849b27d-2de0-4e4e-9db4-aac77cbd14de.png)
![preview filtering](https://user-images.githubusercontent.com/4225347/222785473-d04b9e5f-d965-4e3f-b4ff-e86756750fbe.png)


## Licence
[GPLv3][gpl] © [Rob Aimes][author]

[magento-badge]:https://img.shields.io/badge/magento-2.3.x%20%7C%202.4.x-orange.svg?logo=magento&style=for-the-badge
[release-badge]:https://img.shields.io/github/v/release/robaimes/module-quality-patches-ui
[packagist]:https://packagist.org/packages/aimes/module-quality-patches-ui
[gpl]:https://www.gnu.org/licenses/gpl-3.0.en.html
[author]:https://aimes.dev/
[composer-patches]:https://github.com/cweagans/composer-patches
