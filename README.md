# Puss

[![Build Status](https://travis-ci.org/gajus/puss.png?branch=master)](https://travis-ci.org/gajus/puss)
[![Coverage Status](https://coveralls.io/repos/gajus/puss/badge.png?branch=master)](https://coveralls.io/r/gajus/puss?branch=master)
[![Latest Stable Version](https://poser.pugx.org/gajus/puss/version.png)](https://packagist.org/packages/gajus/puss)
[![License](https://poser.pugx.org/gajus/puss/license.png)](https://packagist.org/packages/gajus/puss)

The alternative SDK provides access to the Graph API. The biggest difference between the [official PHP SDK](https://github.com/facebook/facebook-php-sdk-v4) and Puss is the API.

## Initializing

> You will need to have configured a Facebook App, which you can obtain from the [App Dashboard](https://developers.facebook.com/apps).

Initialize the SDK with your app ID and secret:

```php
/**
 * @param string $app_id App ID.
 * @param string $app_secret App secret.
 */
$app = new Gajus\Puss\App('your app ID', 'your app secret');
```

In the original PHP SDK, [`FacebookSession::setDefaultApplication`](https://developers.facebook.com/docs/php/gettingstarted/4.0.0#init) is used to set the default app credentials statically, making them accessible for future calls without needing to reference an equivalent of the `Gajus\Puss\App` instance.

## Get the Signed Request

The `Gajus\Puss\SignedRequest` is available when either of the following is true:

* The signed request was received via the `$_POST['signed_request']`. In this case, a copy of the raw signed request is stored in the user session.
* The signed request is available in the user session.
* The signed request is available from the Javascript SDK dropped cookie.

```php
/**
 * @return null|Gajus\Puss\SignedRequest
 */
$signed_request = $app->getSignedRequest();

/**
 * Get user ID when user access token can be derived from the signed request.
 *
 * @return null|int
 */
$signed_request->getUserId();

/**
 * Get page ID when signed request is obtained via the page canvas.
 * 
 * @return null|int
 */
$signed_request->getPageId();

/**
 * Return the signed request payload.
 * 
 * @return array
 */
$signed_request->getPayload();
```

## Get the User Access Token

The `Gajus\Puss\AccessToken` is available when either of the following is true:

* The signed request had the `access_token`.
* The signed request had `code` that has been exchanged for the access token.

```php
/**
 * Resolve the user access token from the signed request.
 * The access token is either provided or it can be exchanged for the code.
 *
 * @return null|Gajus\Puss\AccessToken
 */
$access_token = $signed_request->getAccessToken();
```

You can build an `AccessToken` if you have it (e.g. stored in the database).

```php
/**
 * @param Gajus\Puss\App $app
 * @param string $access_token A string that identifies a user, app, or page and can be used by the app to make graph API calls.
 * @param self::TYPE_USER|self::TYPE_APP|self::TYPE_PAGE $type
 */
$access_token = new Gajus\Puss\AccessToken($app, 'user access token', Gajus\Puss\AccessToken::TYPE_USER);
```

## Make User

```php
/**
 * @param Gajus\Puss\App $app
 * @param Gajus\Puss\AccessToken $access_token
 */
$user = new Gajus\Puss\User($this->app, $access_token);
```

## Make Graph API call

An API call can be made using either `Gajus\Puss\App` or `Gajus\Puss\User` context. If use `App` context, then app access token is used; is use `User` context, then user access token is used.

```php
/**
 * @param Gajus\Puss\Session $session
 * @param string $method GET|POST|DELETE
 * @param string $path Path relative to the Graph API.
 * @param array $query GET parameters.
 */
$request = new Gajus\Puss\Request($app, 'GET', 'app');

/**
 * @throws Gajus\Puss\RequestException If the Graph API call results in an error.
 * @return array Graph API response.
 */
$request->make();
```

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