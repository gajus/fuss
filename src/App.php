<?php
namespace Gajus\Fuss;

/**
 * @link https://github.com/gajus/fuss for the canonical source repository
 * @license https://github.com/gajus/fuss/blob/master/LICENSE BSD 3-Clause
 */
class App implements Session {
    const OPTION_VERSION = 'version';
    const OPTION_FORCE_COOKIE = 'force cookie';

    private
        /**
         * @array
         */
        $options = [
            self::OPTION_VERSION => null,
            self::OPTION_FORCE_COOKIE => false
        ],
        /**
         * @var int App ID.
         */
        $app_id,
        /**
         * @var string App secret.
         */
        $app_secret,
        /**
         * @var Gajus\Fuss\SignedRequest
         */
        $signed_request,
        /**
         * @var Gajus\Fuss\AccessToken
         */
        $access_token;
    
    /**
     * @param int $app_id App ID.
     * @param string $app_secret App secret.
     * @param array $options
     */
    public function __construct ($app_id, $app_secret, array $options = []) {
        $this->app_id = (int) $app_id;
        $this->app_secret = (string) $app_secret;

        foreach ($options as $name => $value) {
            $this->setOption($name, $value);
        }

        if (isset($_POST['signed_request'])) {  
            $this->setSignedRequest($_POST['signed_request']);
        } else if (isset($_SESSION['gajus']['fuss'][$this->getId()]['signed_request'])) {
            $this->setSignedRequest($_SESSION['gajus']['fuss'][$this->getId()]['signed_request']);
        } else if (isset($_COOKIE['fbsr_' . $this->getId()])) {
            $this->setSignedRequest($_COOKIE['fbsr_' . $this->getId()]);
        }

        if ($this->getOption(self::OPTION_FORCE_COOKIE)) {
            $this->bypassThirdPartyCookie();
        }
    }

    /**
     * @param self::OPTION_VERSION $name
     * @param mixed $value
     * @return null
     */
    private function setOption ($name, $value) {
        $this->getOption($name);

        if ($name === self::OPTION_VERSION) {
            if (!preg_match('/^v\d\.\d$/', $value)) {
                throw new Exception\AppException('Invalid OPTION_VERSION value format.');
            }
        } else if ($name === self::OPTION_FORCE_COOKIE) {
            if (!is_bool($value)) {
                throw new Exception\AppException('Invalid OPTION_FORCE_COOKIE value format.');
            }
        }

        $this->options[$name] = $value;
    }

    /**
     * @param self::OPTION_VERSION $name
     * @return mixed
     */
    public function getOption ($name) {
        if ($name !== self::OPTION_VERSION && $name !== self::OPTION_FORCE_COOKIE) {
            throw new Exception\AppException('Invalid option.');
        }

        return array_key_exists($name, $this->options) ? $this->options[$name] : null;
    }

    /**
     * Designed to be used for a signed request retrieved via the JavaScript SDK.
     * 
     * @see https://developers.facebook.com/docs/reference/javascript/FB.getLoginStatus#response_and_session_objects
     * @param string $signed_request
     * @return null
     */
    public function setSignedRequest ($signed_request) {
        $this->signed_request = new SignedRequest($this, $signed_request);

        $_SESSION['gajus']['fuss'][$this->app_id]['signed_request'] = $signed_request;
    }

    /**
     * @return null|Gajus\Fuss\SignedRequest
     */
    public function getSignedRequest () {
        return $this->signed_request;
    }

    /**
     * @return int
     */
    public function getId () {
        return $this->app_id;
    }

    /**
     * @return string
     */
    public function getSecret () {
        return $this->app_secret;
    }

    /**
     * Deriving the app access token from the app id and secret.
     * The access token of this type bypass the access token validation.
     * 
     * @see https://developers.facebook.com/docs/facebook-login/access-tokens#apptokens
     * @return Gajus\Fuss\AccessToken
     */
    public function getAccessToken () {
        if (!$this->access_token) {
            $this->access_token = new AccessToken($this, $this->app_id . '|' . $this->app_secret, AccessToken::TYPE_APP);
        }

        return $this->access_token;
    }

    /**
     * When third party cookies are not accepted, client need to be redirected to the
     * domain that needs to drop the cookies and then back to the original URL.
     * 
     * @codeCoverageIgnore
     * @todo Define behavior when client does not accept cookies.
     * @see https://github.com/gajus/fuss/issues/2
     * @return null
     */
    private function bypassThirdPartyCookie () {
        if (isset($_GET['gajus']['fuss']['third_party_cookie'])) {
            \http_response_code(302);

            header('Location: ' . $_GET['gajus']['fuss']['third_party_cookie']);

            exit;
        }

        if (
            session_status() === \PHP_SESSION_ACTIVE &&
            // The cookie is not set.
            (!isset($_COOKIE[session_name()]) || $_COOKIE[session_name()] !== session_id())
            )
        {
            $content_type = null;

            foreach (headers_list() as $header) {
                $header = mb_strtolower($header);

                if (strpos($header, 'content-type:') === 0) {
                    $content_type = $header;

                    break;
                }
            }

            // Use JavaScript only when content-type HTML or unknown.
            if (!$content_type || strpos($content_type, 'text/html') !== false) {
                parse_str($_SERVER['QUERY_STRING'], $query);

                $query['gajus']['fuss']['third_party_cookie'] = $this->getTopUrl();

                $query = http_build_str($query);

                $request_url = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? 'https' : 'http';
                $request_url .= '://' . $_SERVER['HTTP_HOST'];
                $request_url .= strpos($_SERVER['REQUEST_URI'], '?') === false ? $_SERVER['REQUEST_URI'] : strstr($_SERVER['REQUEST_URI'], '?', true);
                $request_url .= '?' . $query;

                ?>
                <script>
                window.top.location.href = <?=json_encode($request_url, \JSON_UNESCAPED_SLASHES)?>;
                </script>
                <?php
            }
        }
    }

    /**
     * When app is loaded either in Page Tab or Canvas, the top URL is generally not known.
     * This is an attempt to reconstruct the top URL with whatever accompanying state data.
     * 
     * @return string
     */
    public function getTopUrl () {
        $signed_request = $this->getSignedRequest();

        if (!$signed_request) {
            throw new Exception\AppException('App is not loaded in Page Tab or Canvas.');
        }

        if ($signed_request->isPageTab()) {
            $top_url = 'https://www.facebook.com/' . $signed_request->getPageTab()->getId() . '/app_' . $this->getId();

            if ($app_data = $signed_request->getAppData()) {
                $top_url .= '?' . http_build_str(['app_data' => $app_data]);
            }
        } else {
            $top_url = 'https://apps.facebook.com/' . $this->getId() . '/';

            if (!empty($_SERVER['QUERY_STRING'])) {
                $top_url .= '?' . $_SERVER['QUERY_STRING'];
            }
        }

        return $top_url;
    }   
}