<?php

namespace common\components\webService;

use common\components\webService\request\BaseRequest;
use common\components\webService\response\BaseResponse;
use Exception;
use SoapClient;
use SoapFault;
use Yii;
use yii\base\Component;
use function call_user_func_array;
use function get_class;

class WebService extends Component
{

	public $wsdl;
	public $username;
	public $password;

	/**
	 * @var SoapClient
	 */
	private $client;

	public function init()
	{
		parent::init();
	}

	/**
	 * @param BaseRequest $request
	 * @return BaseResponse
	 * @throws Exception
	 */
	public function send(BaseRequest $request)
	{

		$this->createSoapClient();

		$method = pathinfo(str_replace('\\', '/', get_class($request)), PATHINFO_BASENAME);

		try {
			$response = @call_user_func_array([$this->client, $method], [$request]);
		} catch (Exception $e) {
			Yii::info([
				'requestParams' => $request->attributes,
				'request' => $this->client->__getLastRequest(),
				'response' => $this->client->__getLastResponse(),
			], implode('\\', ['service1c', $method]));
			throw $e;
		}

		Yii::info([
			'requestParams' => $request->attributes,
			'request' => $this->client->__getLastRequest(),
			'response' => $this->client->__getLastResponse(),
		], implode('\\', ['service1c', $method]));

		$class = '\common\components\webService\response\\' . $method;

		return new $class($response);
	}

	public function getLastRequest()
	{
		return $this->client->__getLastRequest();
	}

	public function getLastResponse()
	{
		return $this->client->__getLastResponse();
	}

	/**
	 * @return SoapClient
	 * @throws Exception
	 */
	protected function createSoapClient()
	{

		if ($this->client === null) {
			$wsdl = Yii::getAlias($this->wsdl);
			$this->client = new SoapClient($wsdl, [
				'trace' => 1,
				'compression' => SOAP_COMPRESSION_ACCEPT,
				'login' => $this->username,
				'password' => $this->password,
				'exceptions' => 1,
				'soap_version' => SOAP_1_2,
				'cache_wsdl' => WSDL_CACHE_MEMORY,
			]);
		}

		return $this->client;
	}

}
