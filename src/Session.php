<?php
namespace Gajus\Fuss;

/**
 * @link https://github.com/gajus/puss for the canonical source repository
 * @license https://github.com/gajus/puss/blob/master/LICENSE BSD 3-Clause
 */
interface Session {
    public function getSecret ();
    public function getAccessToken ();
}