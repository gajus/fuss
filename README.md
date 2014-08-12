# Puss

[![Build Status](https://travis-ci.org/gajus/puss.png?branch=master)](https://travis-ci.org/gajus/puss)
[![Coverage Status](https://coveralls.io/repos/gajus/puss/badge.png?branch=master)](https://coveralls.io/r/gajus/puss?branch=master)
[![Latest Stable Version](https://poser.pugx.org/gajus/puss/version.png)](https://packagist.org/packages/gajus/puss)
[![License](https://poser.pugx.org/gajus/puss/license.png)](https://packagist.org/packages/gajus/puss)

The alternative SDK provides access to the Graph API. In contrast to the [official PHP SDK](https://github.com/facebook/facebook-php-sdk-v4), Puss abstracts the 

## Initializing

> You will need to have configured a Facebook App, which you can obtain from the [App Dashboard](https://developers.facebook.com/apps).

Initialize the SDK with your app ID and secret:

```php
$app = new Gajus\Puss\App('YOUR_APP_ID', 'YOUR_APP_SECRET');
```

In the original PHP SDK, [`FacebookSession::setDefaultApplication`](https://developers.facebook.com/docs/php/gettingstarted/4.0.0#init) is used to set the default app credentials statically, making them accessible for future calls without needing to reference an equivalent of the `Gajus\Puss\App` instance.

## Installation

If you are using [Composer](https://getcomposer.org/) as a package manager, add the following dependency to the `composer.json` and run composer with the install parameter.

```
{
    "require" : {
        "gajus/puss" : "1.0.*"
    }
}
```


## Tests

The tests are automatically run using the [Travis-CI](https://travis-ci.org/gajus/puss) and secured app credentials.

To run the tests locally,

1. Pull the repository using the [Composer](https://getcomposer.org/).
2. Create `tests/config.php` from `tests/config.php.dist` and edit to add your credentials.
3. Execute the test script using the [PHPUnit](http://phpunit.de/).

> You should be using a sandboxed application for running the tests.