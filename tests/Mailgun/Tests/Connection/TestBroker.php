<?php
namespace Mailgun\Tests\Connection;

use Mailgun\Connection\RestClient;
use Guzzle\Http\Client;

class TestBroker extends RestClient{
	private $apiKey;
	
	protected $apiEndpoint;
	protected $guzzleMockPlugin;

	public function __construct($apiKey = null, 
								$apiEndpoint = "api.mailgun.net", 
								$apiVersion = "v2",
								$ssl,
								$responseCode = 200){
		$this->apiKey = $apiKey;

		$this->guzzleMockPlugin = new \Guzzle\Plugin\Mock\MockPlugin();
		$this->guzzleMockPlugin->addResponse(new \Guzzle\Http\Message\Response($responseCode));

		$this->mgClient = new Client($this->generateEndpoint($apiEndpoint, $apiVersion, $ssl));
		$this->mgClient->setDefaultOption('curl.options', array('CURLOPT_FORBID_REUSE' => true));
		$this->mgClient->setDefaultOption('auth', array (API_USER, $this->apiKey));	
		$this->mgClient->setDefaultOption('exceptions', false);
		$this->mgClient->setUserAgent(SDK_USER_AGENT . '/' . SDK_VERSION);

		$this->mgClient->addSubscriber($this->guzzleMockPlugin);
	}
	
	public function responseHandler($responseObj){
		$httpResponseCode = $responseObj->getStatusCode();
		if($httpResponseCode === 200){
			$result = new \stdClass();
			// if 200, return request body
			$result->http_response_body = $responseObj;
			$result->http_request_body = $this->guzzleMockPlugin->getReceivedRequests();
		}
		elseif($httpResponseCode == 400){
			throw new MissingRequiredParameters(EXCEPTION_MISSING_REQUIRED_PARAMETERS);
		}
		elseif($httpResponseCode == 401){
			throw new InvalidCredentials(EXCEPTION_INVALID_CREDENTIALS);
		}
		elseif($httpResponseCode == 404){
			throw new MissingEndpoint(EXCEPTION_MISSING_ENDPOINT);
		}
		else{
			throw new GenericHTTPError(EXCEPTION_GENERIC_HTTP_ERROR);
		}
		$result->http_response_code = $httpResponseCode;
		return $result;
	}

	private function generateEndpoint($apiEndpoint, $apiVersion, $ssl){
		if(!$ssl){
			return "http://" . $apiEndpoint . "/" . $apiVersion . "/";
		}
		else{
			return "https://" . $apiEndpoint . "/" . $apiVersion . "/";
		}
	}


}


?>