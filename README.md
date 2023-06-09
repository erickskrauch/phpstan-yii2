# Yii2 extension for PHPStan

An extension for [PHPStan](https://phpstan.org) providing types support and rules to work with the [Yii2 framework](https://www.yiiframework.com). Hardfork of [proget-hq/phpstan-yii2](https://github.com/proget-hq/phpstan-yii2).

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Total Downloads][ico-downloads]][link-downloads]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-build-status]][link-build-status]

## What does it do?

* Provides stub files for better analysis of array shapes.
* Mark `YII_*` constants as dynamic.
* Significantly improves support for `ActiveRecord` and `ActiveQuery`.
* Provides correct return type for `Yii::$container->get('service_id')` method.
* Provides correct return type for `Yii::$app->request->headers->get('authorization')` method based on the `$first` parameter.
* Provides reflection extension for `BaseObject`'s getters and setters.

## Installation

To use this extension, require it in [Composer](https://getcomposer.org):

```sh
composer require --dev erickskrauch/phpstan-yii2
```

If you also install [phpstan/extension-installer](https://github.com/phpstan/extension-installer) then you're all set!

<details>
  <summary>Manual installation</summary>

  If you don't want to use `phpstan/extension-installer`, include `extension.neon` in your project's PHPStan config:

  ```
  includes:
    - vendor/erickskrauch/phpstan-yii2/extension.neon
  ```
</details>

## Configuration

You have to provide the path to the configuration file for your application. For [Advanced](https://github.com/yiisoft/yii2-app-advanced) project template your path might look like this:

```neon
parameters:
  yii2:
    config_path: common/config/main.php
```

*You may want to create a separate configuration file for PHPStan describing the services available throughout the application. But usually, `common` is sufficient, because it contains all the services universally available in any module of the application.*

[ico-version]: https://img.shields.io/packagist/v/erickskrauch/phpstan-yii2.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-green.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/erickskrauch/phpstan-yii2.svg?style=flat-square
[ico-build-status]: https://img.shields.io/github/actions/workflow/status/erickskrauch/phpstan-yii2/ci.yml?branch=master&style=flat-square

[link-packagist]: https://packagist.org/packages/erickskrauch/phpstan-yii2
[link-downloads]: https://packagist.org/packages/erickskrauch/phpstan-yii2/stats
[link-build-status]: https://github.com/erickskrauch/phpstan-yii2/actions
