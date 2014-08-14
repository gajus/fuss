# Puss

[![Build Status](https://travis-ci.org/gajus/puss.png?branch=master)](https://travis-ci.org/gajus/puss)
[![Coverage Status](https://coveralls.io/repos/gajus/puss/badge.png?branch=master)](https://coveralls.io/r/gajus/puss?branch=master)
[![Latest Stable Version](https://poser.pugx.org/gajus/puss/version.png)](https://packagist.org/packages/gajus/puss)
[![License](https://poser.pugx.org/gajus/puss/license.png)](https://packagist.org/packages/gajus/puss)

The Facebook SDK for PHP provides an interface to the Graph API. The main difference between the [official PHP SDK](https://github.com/facebook/facebook-php-sdk-v4) and Puss is the API.

## Initializing App

> You will need to have configured a Facebook App, which you can obtain from the [App Dashboard](https://developers.facebook.com/apps).

Initialize the SDK with your app ID and secret:

```php
/**
 * @param string $app_id App ID.
 * @param string $app_secret App secret.
 */
$app = new Gajus\Puss\App('your app ID', 'your app secret');
```

In the original Facebook PHP SDK, [`FacebookSession::setDefaultApplication`](https://developers.facebook.com/docs/php/gettingstarted/4.0.0#init) is used to set the default app credentials statically, making them accessible for future calls without needing to reference an equivalent of the `Gajus\Puss\App` instance. Puss does not use stateful programing or global variables.

## Get the Signed Request

The [signed request](https://developers.facebook.com/docs/reference/login/signed-request/) is encapsulated in the `Gajus\Puss\SignedRequest` entity. It is available via an instance of `App` when either of the following is true:

* The signed request was received via the `$_POST['signed_request']`.
* The signed request is present in the user session.
* The signed request is present in the JavaScript SDK cookie.

```php
/**
 * @return null|Gajus\Puss\SignedRequest
 */
$signed_request = $app->getSignedRequest();
```

You might have obtained the signed request via the JavaScript SDK (e.g. [FB.getLoginStatus](https://developers.facebook.com/docs/reference/javascript/FB.getLoginStatus#response_and_session_objects)). In which case, you can share it with the app:

```php
/**
 * Designed to be used for a signed request retrieved via the JavaScript SDK.
 * 
 * @see https://developers.facebook.com/docs/reference/javascript/FB.getLoginStatus#response_and_session_objects
 * @param string $signed_request
 * @return null
 */
$app->setSignedRequest('the signed request');
```

The sign request is stored in the user session and can be retrieved on the following request.

### Signed Request Data

A signed request contains some additional fields of information, even before permissions have been requested:

```php
/**
 * User ID when user access token is in or can be derived from the signed request.
 *
 * @return null|int
 */
$signed_request->getUserId();

/**
 * Page ID when a Page tab loads the app.
 * 
 * @return null|int
 */
$signed_request->getPageId();

/**
 * The content of the app_data query string parameter which may be passed if the app is being loaded within a Page Tab.
 * The JSON input is automatically decoded.
 * 
 * @see https://developers.facebook.com/docs/reference/login/signed-request/
 * @return mixed
 */
$signed_request->getAppData();

/**
 * Return the signed request payload.
 * 
 * @see https://developers.facebook.com/docs/reference/login/signed-request/
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

You can build an `AccessToken` if you have it (e.g. stored in the database):

```php
/**
 * @param Gajus\Puss\App $app
 * @param string $access_token A string that identifies a user, app, or page and can be used by the app to make graph API calls.
 * @param self::TYPE_USER|self::TYPE_APP|self::TYPE_PAGE $type
 */
$access_token = new Gajus\Puss\AccessToken($app, 'user access token', Gajus\Puss\AccessToken::TYPE_USER);
```

### Extend The Access Token

Access tokens generated via web login are [short-lived](https://developers.facebook.com/docs/facebook-login/access-tokens#termtokens) tokens, but you can upgrade them to long-lived tokens.

To check if the access token is long-lived:

```php
/**
 * The issued_at field is not returned for short-lived access tokens.
 * 
 * @see https://developers.facebook.com/docs/facebook-login/access-tokens#debug
 * @return boolean
 */
$access_token->isLong();
```

To extend a short-lived access token:

```php
/**
 * Extend a short-lived access token for a long-lived access token.
 * Upon successfully extending the token, the instance of the object
 * is updated with the long-lived access token.
 *
 * @see https://developers.facebook.com/docs/facebook-login/access-tokens#extending
 * @return null
 */
$access_token->extend();
```

To know when does the access token expire:

```php
/**
 * @return int UNIX timestamp in seconds.
 */
$access_token->getExpirationTimestamp();
```

Take a string copy to store the access token for later use:

```php
/**
 * @return string The access token as a string.
 */
$access_token->getPlain();
```

## Initializing User

In order to make calls on behalf of a [user](https://developers.facebook.com/docs/graph-api/reference/v2.1/user), you need to create `User` entity with a valid access token:

```php
/**
 * @param Gajus\Puss\App $app
 * @param Gajus\Puss\AccessToken $access_token
 */
$user = new Gajus\Puss\User($access_token);
```

Upon instantiating the `User` object, the access token is used to fetch information about the user.

```php
/**
 * Get Facebook user ID.
 * Beware that as of Graph API v2.0, the user ID is app-scoped.
 *
 * @see https://developers.facebook.com/docs/apps/upgrading#upgrading_v2_0_user_ids
 * @return null|int
 */
$user->getId();
```

You can update user access token:

```php
/**
 * @param Gajus\Puss\AccessToken $access_token
 * @return null
 */
$user->setAccessToken($access_token);
```

## Making Graph API calls

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

## Putting Everything Together

```php
$app = new Gajus\Puss\App('your app ID', 'your app secret');
$user = null;

$signed_request = $app->getSignedRequest();

if ($signed_request) {
    $access_token = $signed_request->getAccessToken();

    $user = new Gajus\Puss\User($access_token);
}
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