<?php

namespace aksafan\fcm\source\auth;

use Firebase\JWT\JWT;
use Google\Auth\Credentials\ServiceAccountCredentials;
use Google\Auth\Credentials\UserRefreshCredentials;
use Google\Auth\CredentialsLoader;
use Google\Auth\FetchAuthTokenCache;
use GuzzleHttp\ClientInterface;
use InvalidArgumentException;
use LogicException;
use Psr\Cache\CacheItemPoolInterface;
use UnexpectedValueException;

class ServiceAccount
{
    /**@var array $authConfig json-decoded service account data * */
    private $authConfig;

    /**@var CacheItemPoolInterface $cache Caching interface* */
    private $cache;

    /**
     * ServiceAccount constructor.
     *
     * @param string|array $authConfig
     */
    public function __construct($authConfig)
    {
        if (\is_string($authConfig)) {
            if (file_exists($authConfig)) {
                if (!\is_array($authConfig = json_decode(file_get_contents($authConfig), true))) {
                    throw new LogicException('invalid json for FCM auth config');
                }
            } elseif (!\is_array($authConfig = json_decode($authConfig, true))) {
                throw new InvalidArgumentException('FCM auth config file not found');
            }
        }

        if (empty($authConfig['type']) || $authConfig['type'] !== 'service_account') {
            throw new InvalidArgumentException('Invalid service account data!');
        }

        $this->authConfig = $authConfig;
    }

    /**
     * Authorizes an http request.
     *
     * @param array|string $scope Scope of the requested credentials @see https://developers.google.com/identity/protocols/googlescopes
     *
     * @return ClientInterface|\GuzzleHttp\Client
     *
     * @throws \Exception
     */
    public function authorize($scope)
    {
        return CredentialsLoader::makeHttpClient($this->getCredentials($scope));
    }

    /**
     * Returns the Firebase project id
     *
     * @return string
     */
    public function getProjectId()
    {
        if (!isset($this->authConfig['project_id']) || empty($this->authConfig['project_id'])) {
            throw new UnexpectedValueException('project_id not found in auth config file!');
        }

        return $this->authConfig['project_id'];
    }

    /**
     * Gets the client_email service account filed
     *
     * @return string
     */
    public function getClientEmail()
    {
        if (!isset($this->authConfig['client_email']) || empty($this->authConfig['client_email'])) {
            throw new UnexpectedValueException('client_email not found in auth config file!');
        }

        return $this->authConfig['client_email'];
    }

    /**
     * Encodes a JWT token
     *
     * @param string $uid Unique id
     * @param array $claims array of optional claims
     *
     * @return string
     */
    public function encodeJWT($uid, array $claims = []): string
    {
        $clientEmail = $this->getClientEmail();
        $now = time();
        $payload = [
            'iss' => $clientEmail,
            'sub' => $clientEmail,
            'aud' => 'https://identitytoolkit.googleapis.com/google.identity.identitytoolkit.v1.IdentityToolkit',
            'iat' => $now,
            'exp' => $now + 3600,
            'uid' => $uid,
            'claims' => $claims,
        ];

        return JWT::encode($payload, $this->getPrivateKey(), 'RS256');
    }

    /**
     * Decodes a Firebase JWT
     *
     * @param string $jwt JWT string
     * @param string $public_key Public key
     *
     * @return object
     */
    public function decodeJWT($jwt, $public_key)
    {
        return JWT::decode($jwt, $public_key, ['RS256']);
    }

    /**
     * @param CacheItemPoolInterface $cache
     */
    public function setCacheHandler(CacheItemPoolInterface $cache)
    {
        $this->cache = $cache;
    }

    /**
     * Gets the credentials.
     *
     * @param $scope array|string Scope of the requested credentials @see https://developers.google.com/identity/protocols/googlescopes
     *
     * @return ServiceAccountCredentials|UserRefreshCredentials|FetchAuthTokenCache
     */
    protected function getCredentials($scope)
    {
        $credentials = CredentialsLoader::makeCredentials($scope, $this->authConfig);
        //OAuth token caching
        if (null !== $this->cache) {
            $credentials = new FetchAuthTokenCache($credentials, [], $this->cache);
        }

        return $credentials;
    }

    /**
     * Gets the private_key service account filed
     *
     * @return string
     */
    protected function getPrivateKey()
    {
        if (!isset($this->authConfig['private_key']) || empty($this->authConfig['private_key'])) {
            throw new UnexpectedValueException('private_key not found in auth config file!');
        }

        return $this->authConfig['private_key'];
    }
}
