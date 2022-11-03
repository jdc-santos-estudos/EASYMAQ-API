<?php

namespace App\Library;

use Exception;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Psr\Http\Message\ResponseInterface;

use DocuSign\eSign\Client\ApiClient;
use DocuSign\eSign\Configuration;
use Throwable;

class DocuSign extends AbstractProvider
{
    public $authorizationServer = null;
    public $allowSilentAuth = true;
    public $targetAccountId = false;
    
    protected static $expires_in;
    protected static $access_token;
    protected static $expiresInTimestamp;
    protected static $account;
    protected static ApiClient $apiClient;
    const TOKEN_REPLACEMENT_IN_SECONDS = 120; # 10 minutes

    use BearerAuthorizationTrait;

    public function __construct()
    {
        $config = new Configuration();
        self::$apiClient = new ApiClient($config);
    }

    public function login()
    {
        self::$access_token = $this->configureJwtAuthorizationFlowByKey();
        self::$expiresInTimestamp = time() + self::$expires_in;

        if (is_null(self::$account)) {
            self::$account = self::$apiClient->getUserInfo(self::$access_token->getAccessToken());
        }

        $redirectUrl = false;
        if (isset($_SESSION['eg'])) {
            $redirectUrl = $_SESSION['eg'];
        }

        $this->authCallback($redirectUrl);
    }

    private function configureJwtAuthorizationFlowByKey()
    {
        self::$apiClient->getOAuth()->setOAuthBasePath($GLOBALS['JWT_CONFIG']['authorization_server']);
        $privateKey = file_get_contents(
            $_SERVER['DOCUMENT_ROOT'] . '/' . $GLOBALS['JWT_CONFIG']['private_key_file'],
            true
        );

        $scope = (new DocuSign())->getDefaultScopes()[0];
        $jwt_scope = $scope;

        try {
            $response = self::$apiClient->requestJWTUserToken(
                $aud = $GLOBALS['JWT_CONFIG']['ds_client_id'],
                $aud = $GLOBALS['JWT_CONFIG']['ds_impersonated_user_id'],
                $aud = $privateKey,
                $aud = $jwt_scope
            );

            return $response[0];    //code...
        } catch (Throwable $th) {
            // we found consent_required in the response body meaning first time consent is needed
            if (strpos($th->getMessage(), "consent_required") !== false) {
                $authorizationURL = 'https://account-d.docusign.com/oauth/auth?' . http_build_query(
                    [
                        'scope' => "impersonation+" . $jwt_scope,
                        'redirect_uri' => $GLOBALS['DS_CONFIG']['app_url'] . '/index.php?page=ds_callback',
                        'client_id' => $GLOBALS['JWT_CONFIG']['ds_client_id'],
                        'state' => $_SESSION['oauth2state'],
                        'response_type' => 'code'
                    ]
                );
                header('Location: ' . $authorizationURL);
            }
        }
    }

    function authCallback($redirectUrl): void
    {
        // Check given state against previously stored one to mitigate CSRF attack
        if (!self::$access_token) {
            if (isset($_GET['code'])) {
                // we have obtained consent, let's shortcut and login the user
                $this->login();
            } else {
                exit('Invalid JWT state');
            }
        } else {
            try {
                // We have an access token, which we may use in authenticated
                // requests against the service provider's API.

                $this->flash('You have authenticated with DocuSign.');
                $_SESSION['ds_access_token'] = self::$access_token->getAccessToken();
                $_SESSION['ds_expiration'] = time() + (self::$access_token->getExpiresIn() * 60); # expiration time.

                // Using the access token, we may look up details about the
                // resource owner.
                $_SESSION['ds_user_name'] = self::$account[0]->getName();
                $_SESSION['ds_user_email'] = self::$account[0]->getEmail();

                $account_info = self::$account[0]->getAccounts();
                $base_uri_suffix = '/restapi';
                $_SESSION['ds_account_id'] = $account_info[0]->getAccountId();
                $_SESSION['ds_account_name'] = $account_info[0]->getAccountName();
                $_SESSION['ds_base_path'] = $account_info[0]->getBaseUri() . $base_uri_suffix;
            } catch (IdentityProviderException $e) {
                // Failed to get the access token or user details.
                exit($e->getMessage());
            }
            if (!$redirectUrl) {
                $redirectUrl = $GLOBALS['app_url'];
            }
            header('Location: ' . $redirectUrl);
            exit;
        }
    }


    //---------------------------------------------------------------------------------

    public function getBaseAuthorizationUrl(): string
    {
        $url = $this->getAuthServer();
        if ($this->allowSilentAuth) {
            $url .= '/oauth/auth';
        } else {
            $url .= '/oauth/auth?prompt=login';
        }
        return $url;
    }

    private function getAuthServer(): string
    {
        $url = $this->authorizationServer;
        if ($url == null) {
            throw new Exception('authorizationServer not set.');
        }
        return $url;
    }

    public function getBaseAccessTokenUrl(array $params): string
    {
        $url = $this->getAuthServer();
        $url .= '/oauth/token';
        return $url;
    }

    public function getResourceOwnerDetailsUrl(AccessToken $token): string
    {
        $url = $this->getAuthServer();
        $url .= '/oauth/userinfo';
        return $url;
    }

    public function getDefaultScopes(): array
    {
        return ["signature"];
    }

    protected function checkResponse(ResponseInterface $response, $data): void
    {
        if (isset($data['error'])) {
            if (!empty($response)) {
                throw new IdentityProviderException(
                    $data['error'],
                    0,
                    $response
                );
            }
        }
    }

    protected function createResourceOwner(array $response, AccessToken $token): DocuSignResourceOwner
    {
        $r = new DocuSignResourceOwner($response);
        $r->target_account_id = $this->targetAccountId;
        return $r;
    }
}
