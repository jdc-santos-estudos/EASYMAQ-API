<?php 
namespace App\Libraries;

use DocuSign\eSign\Configuration;
use DocuSign\eSign\Api\EnvelopesApi;
use DocuSign\eSign\Client\ApiClient;
use DocuSign\eSign\Client\ApiException;
use DocuSign\eSign\Api\EnvelopesApi\ListStatusChangesOptions;

class Docusign_library {

    protected $rsaPrivateKey;
    protected $integration_key = "115c918e-0a87-4b3b-a1bb-bea4944e536f";
    protected $impersonatedUserId = "f551a6e6-8a8f-44e7-866a-e245427c1401";
    protected $scopes = "signature impersonation";
	
	function __construct() {
        $this->rsaPrivateKey = file_get_contents("../private.key");

		$config = new Configuration();
        $apiClient = new ApiClient($config);
        try {
            $apiClient->getOAuth()->setOAuthBasePath("account-d.docusign.com");
            $response = $apiClient->requestJWTUserToken($this->integration_key, $this->impersonatedUserId, $this->rsaPrivateKey, $this->scopes, 60);

        } catch (\Throwable $th) {
            echo "<pre>";
            var_dump($th);
            // we found consent_required in the response body meaning first time consent is needed
            if (strpos($th->getMessage(), "consent_required") !== false) {
                $authorizationURL = 'https://account-d.docusign.com/oauth/auth?' . http_build_query([
                    'scope'         => $scopes,
                    'redirect_uri'  => 'https://httpbin.org/get',
                    'client_id'     => $integration_key,
                    'response_type' => 'code'
                ]);

                echo "It appears that you are using this integration key for the first time.  Please visit the following link to grant consent authorization.";
                echo "<pre>";
                echo $authorizationURL;
                exit();
            }
        }

        if (isset($response)) {
            $access_token = $response[0]['access_token'];
            // retrieve our API account Id
            $info = $apiClient->getUserInfo($access_token);
            $account_id = $info[0]["accounts"][0]["account_id"];

            // Instantiate the API client again with the default header set to the access token
            $config->setHost("https://demo.docusign.net/restapi");
            $config->addDefaultHeader('Authorization', 'Bearer ' . $access_token);
            $apiClient = new ApiClient($config);


            $envelope_api = new EnvelopesApi($apiClient);
            $from_date = date("c", (time() - (30 * 24 * 60 * 60)));
            $options = new ListStatusChangesOptions();
            $options->setFromDate($from_date);
            try {
                $results = $envelope_api->listStatusChanges($account_id, $options);
                echo "results";
                echo "<pre>";
                echo stripslashes($results);
            } catch (ApiException $e) {
                var_dump($e);
                exit;
            }
        }
	}
	
    public function purifierConfig() {
        
    }
	
}