<?php
/**
 * @link    http://hiqdev.com/yii2-hi3a-authclient
 * @license http://hiqdev.com/yii2-hi3a-authclient/license
 * @copyright Copyright (c) 2015 HiQDev
 */

namespace hi3a\authclient;

/**
 * hi3a allows authentication via hi3a OAuth2.
 *
 * In order to use hi3a you must register your application at <https://hi3a.hipanel.com/>.
 *
 * Example application configuration:
 * ~~~
 * 'components' => [
 *     'authClientCollection' => [
 *         'class' => 'hi3a\authclient\Collection',
 *         'clients' => [
 *             'hi3a' => [
 *                 'class'        => 'hi3a\authclient\Hi3aClient',
 *                 'site'         => 'hi3a.hipanel.com',
 *                 'clientId'     => 'client_id',
 *                 'clientSecret' => 'client_secret',
 *             ],
 *         ],
 *     ]
 *     ...
 * ]
 * ~~~
 */
class Hi3aClient extends \yii\authclient\OAuth2
{
    /**
     * Site for urls generation
     */
    public $site;

    public function buildUrl ($path,array $params = [])
    {
        $url = $this->site.'/'.$path;
        return $params ? $this->composeUrl($url,$params) : $url;
    }

    /**
     * Inits Urls based on $site
     */
    public function init ()
    {
        parent::init();
        if (!$this->site) {
            $this->site = 'hi3a.hipanel.com';
        };
        if (strpos($this->site, '://') === false) {
            $this->site = 'https://'.$this->site;
        };
        $defaults = [
            'authUrl'       => 'oauth/authorize',
            'tokenUrl'      => 'oauth/token',
            'apiBaseUrl'    => 'api',
        ];
        foreach ($defaults as $k => $v) {
            if (!$this->{$k}) {
                $this->{$k} = $this->buildUrl($v);
            };
        };
    }

    /** @inheritdoc */
    protected function initUserAttributes () {
        return $this->getAccessToken()->getParam('user_attributes');
    }

    /** @inheritdoc */
    protected function apiInternal ($accessToken, $url, $method, array $params, array $headers) {
        if (!isset($params['format'])) {
            $params['format'] = 'json';
        }
        $params['oauth_token'] = $accessToken->getToken();

        return $this->sendRequest($method, $url, $params, $headers);
    }

    /** @inheritdoc */
    protected function defaultName  () { return 'hi3a'; }

    /** @inheritdoc */
    protected function defaultTitle () { return 'hi3a'; }
}
