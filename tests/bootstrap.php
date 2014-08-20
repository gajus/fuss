<?php
require __DIR__ . '/../vendor/autoload.php';

if (isset($_SERVER['TRAVIS'])) {
    define('TEST_APP_ID', $_SERVER['TEST_APP_ID']);
    define('TEST_APP_SECRET', $_SERVER['TEST_APP_SECRET']);
} else {
    if (!file_exists(__DIR__ . '/config.php')) {
        throw new \Exception('Cannot run tests without test app credentials. Rename config.php.dist to config.php.');
    }

    require __DIR__ . '/config.php';
}

$GLOBALS['test']['test_user'] = [];

/**
 * @param string $permissions A list of comma separated permissions.
 * @return Gajus\Fuss\User
 */
function create_test_user ($permissions = '') {
    $app = new Gajus\Fuss\App(\TEST_APP_ID, \TEST_APP_SECRET);

    $request = new Gajus\Fuss\Request($app, 'POST', 'app/accounts/test-users', ['permissions' => $permissions]);

    $test_user = $request->make();

    $GLOBALS['test']['test_user'][] = $test_user;

    return $test_user;
}

/**
 * @param array $data
 * @return Gajus\Fuss\SignedRequest
 */
function make_signed_request (array $data) {
    $app = new Gajus\Fuss\App(\TEST_APP_ID, \TEST_APP_SECRET);

    return new Gajus\Fuss\SignedRequest($app, sign_data($data));
}

/**
 * @param array $data
 * @param string $secret
 * @return string
 */
function sign_data (array $data, $secret = null) {
    if ($secret === null) {
        $secret = \TEST_APP_SECRET;
    }

    $data = json_encode($data, \JSON_UNESCAPED_SLASHES);
    $encoded_data = encode_base64_url($data);
    $encoded_signature = encode_base64_url(hash_hmac('sha256', $encoded_data, $secret, true));

    return $encoded_signature . '.' . $encoded_data;
}

/**
 * @param string $input
 * @return string
 */
function encode_base64_url ($input) {
    return rtrim(strtr(base64_encode($input), '+/', '-_'), '=');
}

register_shutdown_function(function () {
    $app = new Gajus\Fuss\App(\TEST_APP_ID, \TEST_APP_SECRET);

    foreach ($GLOBALS['test']['test_user'] as $test_user) {
        $request = new Gajus\Fuss\Request($app, 'DELETE', $test_user['id']);

        $request->make();
    }
});