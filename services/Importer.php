<?php

namespace app\services;

use app\interfaces\ImportInterface;
use app\models\Tender;
use app\models\TenderData;
use Yii;
use yii\base\InvalidConfigException;
use yii\httpclient\Client;
use yii\httpclient\Exception;

class Importer implements ImportInterface
{

	public $dataType;
	public $data;
	public $baseapiUrl;
	public $apiUrl;
	public $client;
	public $pageCounter;

	/**
	 * Importer constructor.
	 *
	 * @param string $dataType
	 */
	public function __construct($dataType)
	{
		Yii::info('init importer ' . $dataType, 'console');
		$this->dataType = $dataType;
		Yii::info('dataType is ' . $this->dataType, 'console');

		$params = Yii::$app->params['api_settings'][$this->dataType];
		$this->baseapiUrl = $params['list_url'] ?? '';
		Yii::info('baseapiUrl is ' . $this->baseapiUrl, 'console');

		$this->apiUrl = '/api/0/tenders';
		Yii::info('apiUrl is ' . $this->apiUrl, 'console');

		$this->pageCounter = 1;
		Yii::info('pageCounter is ' . $this->pageCounter, 'console');
	}

	/**
	 * @throws Exception
	 */
	public function import() : bool
	{
		if (!$this->baseapiUrl) {
			Yii::info('import url is empty', 'console');
			return false;
		}
		return $this->startImportData();
	}

	/**
	 * @throws Exception
	 */
	public function startImportData() : bool
	{
		$this->createClient();
		$this->processTenderListData();
		$this->processTenderItemsData();
		return true;
	}

	/**
	 * @throws Exception
	 */
	private function processTenderListData()
	{
		foreach (range(1, 10) as $number) {
			$this->getData();
			$this->checkData();
			$this->processListData();
		}
	}

	private function createClient()
	{
		$this->client = new Client([
			'baseUrl' => $this->baseapiUrl,
			'contentLoggingMaxSize' => 1000000,
			'responseConfig' => [
				'format' => Client::FORMAT_JSON
			],
		]);

		Yii::info('created http client with base url ' . $this->baseapiUrl, 'console');

	}

	/**
	 * @throws Exception
	 */
	private function getData()
	{
		if (!$this->apiUrl) {
			Yii::error('url is empty' , 'console');
			throw new Exception('url is empty');
		}
		try {
			$response = $this->client->createRequest()
				->setMethod('GET')
				->setUrl($this->apiUrl)
				->send();

			if ($response->statusCode != 200) {
				Yii::error('error getting data from API with url '. $this->apiUrl .' with code ' . $response->statusCode . ' and message ' . $response->getContent() , 'console');
				throw new Exception('error getting data from API');
			}

			$content = $response->getContent();

			if (empty($content)) {
				Yii::error('content is empty', 'console');
				throw new Exception('content is empty');
			}

			$this->data = json_decode($content);
			Yii::info('data changed at: ' . time(), 'console');

		} catch (InvalidConfigException | Exception $e) {
			Yii::error('error getting data from API with message ' . $e->getMessage(), 'console');
		}

	}

	/**
	 * @throws Exception
	 */
	private function processListData()
	{
		foreach ($this->data->data as $item) {

			$model = new Tender();
			$model->setAttributes(['tender_id' => $item->id, 'date_modified' => $item->dateModified]);

			if (!$model->save()) {
				Yii::error('model not saved with data id=' . $item->id . ', dateModified=' . $item->dateModified, 'console');
			}
			Yii::info('created Tender model with id ' . $model->id, 'console');
		}

		$this->setApiUrl($this->data->next_page->path ?? '');

		$this->pageCounter++;
		Yii::info('pageCounter changed to ' . $this->pageCounter, 'console');

	}
	/**
	 * @throws Exception
	 */
	private function processTenderItemsData()
	{
		$items = Tender::find()->joinWith('tenderData')->where('tender_data.id is null')->all();
		Yii::info('find items count: ' . count($items), 'console');

		foreach ($items as $item) {
			$this->setApiUrl('/api/0/tenders/'.$item->tender_id);
			$this->getData();
			$this->checkData();
			$this->processItemData();
		}
	}

	private function processItemData()
	{
		$model = new TenderData();
		$model->setAttribute('tender_id', $this->data->data->id);
		$model->setAttribute('tender_data', json_encode($this->data->data));
		if (!$model->save()) {
			Yii::error('model not saved with data id=' . $this->data->data->id , 'console');
		}
		Yii::info('created TenderData model with id ' . $model->id, 'console');
	}

	/**
	 * @throws Exception
	 */
	private function checkData()
	{
		if (empty($this->data->data)) {
			Yii::error('data is empty', 'console');
			throw new Exception('data is empty');
		}

		Yii::info('data checked at: ' . time(), 'console');
	}


	private function setApiUrl(string $string)
	{
		$this->apiUrl = $string;
		Yii::info('apiUrl changed to ' . $this->apiUrl, 'console');
	}



	public function getTestListData()
	{
		$this->data = json_decode('{"data": [{"dateModified": "2022-08-01T19:08:51.617309+03:00", "id": "6833ea9169e94fe9ae561700166efc80"}, {"dateModified": "2022-08-01T19:08:50.463606+03:00", "id": "1bdce9ae67954d95b435aa80794e39eb"}, {"dateModified": "2022-08-01T19:08:29.174900+03:00", "id": "bb676d7a6c1047479935b0b554ae393b"}, {"dateModified": "2022-08-01T19:08:22.228764+03:00", "id": "457b6f1582e642268d9f3081113e19db"}, {"dateModified": "2022-08-01T19:08:13.581864+03:00", "id": "21a710f9339e4656a90a62b4d4fd34e8"}, {"dateModified": "2022-08-01T19:08:05.299728+03:00", "id": "1d1c9264f6e1474fbed819d219b8199f"}, {"dateModified": "2022-08-01T19:08:04.885739+03:00", "id": "be5657bd40b749deab6c093b5e765f0a"}, {"dateModified": "2022-08-01T19:07:53.872139+03:00", "id": "b8ab941119c64591ae086135f9b0a7d8"}, {"dateModified": "2022-08-01T19:07:44.886311+03:00", "id": "a8ca9eac574e41a591b79d95eb7b2796"}, {"dateModified": "2022-08-01T19:07:43.637155+03:00", "id": "fe8c9e29c4f04a47a14243a796d5f4e8"}, {"dateModified": "2022-08-01T19:07:33.070471+03:00", "id": "05c1b2d93fb7416c8438c6ba324332a5"}, {"dateModified": "2022-08-01T19:07:29.141675+03:00", "id": "9f4041d54d9c4d5a92781fcec941be8e"}, {"dateModified": "2022-08-01T19:06:47.492058+03:00", "id": "9d9fc7cd3ff749f98f7faa7bbd5c5992"}, {"dateModified": "2022-08-01T19:06:45.432739+03:00", "id": "e26f3185e7e748cf90d86911fadee809"}, {"dateModified": "2022-08-01T19:06:32.386871+03:00", "id": "f68dc2c1b9774e17ad3165ad1b7ae4e8"}, {"dateModified": "2022-08-01T19:06:30.052717+03:00", "id": "b81892c94a53497ba2db57d930ac45c5"}, {"dateModified": "2022-08-01T19:06:19.030176+03:00", "id": "523b57868dad428d87a7b0fd51e25b71"}, {"dateModified": "2022-08-01T19:06:13.312231+03:00", "id": "7b7a06e345324a6e8f9b54a1e1fba28c"}, {"dateModified": "2022-08-01T19:06:10.451471+03:00", "id": "05736723ce17463f870ad8b418033016"}, {"dateModified": "2022-08-01T19:06:09.234900+03:00", "id": "bb71d9cb6829464f862b2a386af3c49a"}, {"dateModified": "2022-08-01T19:05:53.665614+03:00", "id": "af82ed6d06cb4739b58d156300bd222d"}, {"dateModified": "2022-08-01T19:05:50.383168+03:00", "id": "6ee49158fd4a4e4088538a68524c07be"}, {"dateModified": "2022-08-01T19:05:17.040176+03:00", "id": "c7d5475b42914b87bf7a6e4ab82e1c23"}, {"dateModified": "2022-08-01T19:05:16.969040+03:00", "id": "7e32c8ab3b0d4fb0a72b13eadae36f68"}, {"dateModified": "2022-08-01T19:05:15.911972+03:00", "id": "ee20624aace34fbabca64cb3e50d41a6"}, {"dateModified": "2022-08-01T19:05:14.532355+03:00", "id": "b5da9c9721274e8aaf7d29323b815770"}, {"dateModified": "2022-08-01T19:04:40.423535+03:00", "id": "058fb3e0d35c4ceaa70ffa52ac7b811d"}, {"dateModified": "2022-08-01T19:04:36.840482+03:00", "id": "ec6814d81801464f96ac8dd6cc50d181"}, {"dateModified": "2022-08-01T19:04:13.871866+03:00", "id": "3694e530ab304923895eb30cb076561a"}, {"dateModified": "2022-08-01T19:04:12.156496+03:00", "id": "43c89562c9a044539412c3e4f8aadadd"}, {"dateModified": "2022-08-01T19:04:00.895132+03:00", "id": "79ce4db5c8784bd4917bc942f4135140"}, {"dateModified": "2022-08-01T19:03:59.260128+03:00", "id": "2855fce360ae4b52913134d175e7ec8d"}, {"dateModified": "2022-08-01T19:03:55.693181+03:00", "id": "89e8646efe0d42ef871cc893fd307fca"}, {"dateModified": "2022-08-01T19:03:51.406481+03:00", "id": "6c38ee9f6717484da525904f53120d3a"}, {"dateModified": "2022-08-01T19:03:45.228402+03:00", "id": "1331fe54685f4018b56cda40c396ec8e"}, {"dateModified": "2022-08-01T19:02:54.169817+03:00", "id": "e5e9c07e720242498c0ca676bc7578f9"}, {"dateModified": "2022-08-01T19:02:39.286752+03:00", "id": "b877e9089d164e47a3f043c003af12e3"}, {"dateModified": "2022-08-01T19:02:34.329707+03:00", "id": "4a1cc978eff647758d9159c6240fcb44"}, {"dateModified": "2022-08-01T19:02:28.839597+03:00", "id": "b0eb370920f4455696b284f8609c4555"}, {"dateModified": "2022-08-01T19:02:18.467182+03:00", "id": "b32a865924db41eba436d6b73fd36a88"}, {"dateModified": "2022-08-01T19:02:13.156448+03:00", "id": "ba91403ce32b4857b4cf7803a34c9e92"}, {"dateModified": "2022-08-01T19:01:53.711584+03:00", "id": "78589a86247147d98efd727d4572957a"}, {"dateModified": "2022-08-01T19:01:52.765242+03:00", "id": "db67e70fd5ad412592a4444b1bbb97c9"}, {"dateModified": "2022-08-01T19:01:21.162937+03:00", "id": "6ed38022210d42289ee063aec205972c"}, {"dateModified": "2022-08-01T19:01:20.036514+03:00", "id": "11de5025b9b34f2d9b988a9d6cdefd20"}, {"dateModified": "2022-08-01T19:01:09.380308+03:00", "id": "73be2c7bc50d4b80adb119c1ae0e0ed9"}, {"dateModified": "2022-08-01T19:00:18.919253+03:00", "id": "f55854ca8da04f7988ccae7f0bafaa5d"}, {"dateModified": "2022-08-01T19:00:13.214630+03:00", "id": "74dfe35289c645c28c45e650a3af947f"}, {"dateModified": "2022-08-01T19:00:08.889652+03:00", "id": "7e1b0c2163f249beac6092a921ff68be"}, {"dateModified": "2022-08-01T18:59:24.915575+03:00", "id": "0366709bce9c45449d596d0b1111d064"}, {"dateModified": "2022-08-01T18:59:09.090637+03:00", "id": "cb0ea56d3ff640e690ee056faf262d39"}, {"dateModified": "2022-08-01T18:58:40.974050+03:00", "id": "c7d561473dcf43a19a39336ffe98852f"}, {"dateModified": "2022-08-01T18:58:06.491142+03:00", "id": "7547d46a1894408b9b6a5fc43c96afd5"}, {"dateModified": "2022-08-01T18:58:00.260905+03:00", "id": "22257a3cb4ed4a63a171d98498dac527"}, {"dateModified": "2022-08-01T18:57:21.633334+03:00", "id": "39dd7c32b4b54abd8645287a21db3ada"}, {"dateModified": "2022-08-01T18:57:12.250355+03:00", "id": "27e7c5d262654b9ca5e02bcc12bcd816"}, {"dateModified": "2022-08-01T18:57:02.575502+03:00", "id": "252c0d872ed944eaab66aa5fb2f8b6eb"}, {"dateModified": "2022-08-01T18:56:59.425624+03:00", "id": "94805e864726454fbf61bc8ea62d15e0"}, {"dateModified": "2022-08-01T18:56:45.050963+03:00", "id": "f445cd22e0cb468cacd81ce4bfd7fa74"}, {"dateModified": "2022-08-01T18:56:30.803273+03:00", "id": "62b6b08a7fcd46a8bbae4ec95e152344"}, {"dateModified": "2022-08-01T18:56:25.187014+03:00", "id": "61c244d52675489c948409e6dae11c1f"}, {"dateModified": "2022-08-01T18:55:42.694982+03:00", "id": "8a36f3750fab461c9b3b26ed5e31efcd"}, {"dateModified": "2022-08-01T18:55:07.977222+03:00", "id": "579085e1762548a0a0b326f3c3716526"}, {"dateModified": "2022-08-01T18:54:05.290812+03:00", "id": "d7dae3bf57b3425d83d35e3dc4604984"}, {"dateModified": "2022-08-01T18:53:42.114130+03:00", "id": "454c1bfd9d74420aa74b6ee66878bfb9"}, {"dateModified": "2022-08-01T18:53:00.403001+03:00", "id": "97295c7bf45b4cd68de8781efc03b9f8"}, {"dateModified": "2022-08-01T18:51:53.343674+03:00", "id": "5bdb9afe73e142ea9de254916235c011"}, {"dateModified": "2022-08-01T18:50:43.386645+03:00", "id": "4a366e25ba06474bbf6ae50ceb271453"}, {"dateModified": "2022-08-01T18:50:39.589759+03:00", "id": "f246220490bc40a098d636477a025795"}, {"dateModified": "2022-08-01T18:50:12.198064+03:00", "id": "9cce4dc1d08e49daa2c3700511373cf1"}, {"dateModified": "2022-08-01T18:49:23.730208+03:00", "id": "c2919a1a8c9044d281678c820408ea3d"}, {"dateModified": "2022-08-01T18:49:20.463546+03:00", "id": "17432b7458ce429f9014f0c50d830c39"}, {"dateModified": "2022-08-01T18:49:03.238347+03:00", "id": "8f758024fee24214ac84993cfbc5cabc"}, {"dateModified": "2022-08-01T18:49:02.595521+03:00", "id": "fa8a53305dc74c3f948c5dd0b1e9ce02"}, {"dateModified": "2022-08-01T18:48:02.884797+03:00", "id": "fb77f77f18c540d1be5c1085c01d0856"}, {"dateModified": "2022-08-01T18:47:43.055178+03:00", "id": "f4d415e078e341f499c6a84ddb666930"}, {"dateModified": "2022-08-01T18:47:35.672340+03:00", "id": "065cb3a078c64634bedc4942ee1ff9f8"}, {"dateModified": "2022-08-01T18:47:27.757982+03:00", "id": "fef9586545c04a528dad734f3e526db4"}, {"dateModified": "2022-08-01T18:46:35.298491+03:00", "id": "85ebe8bca1cb4b9da5fa0a6150f19add"}, {"dateModified": "2022-08-01T18:46:13.628283+03:00", "id": "dec7b33aa39146dea6bcd33f1904b9a0"}, {"dateModified": "2022-08-01T18:46:09.179600+03:00", "id": "8849436cbd6b4ac2886267eb5ae20477"}, {"dateModified": "2022-08-01T18:44:22.145117+03:00", "id": "e8be1b965f8d4c8d90be17c8388b651c"}, {"dateModified": "2022-08-01T18:44:10.143998+03:00", "id": "07505b8f910e497e850fb256443aae4c"}, {"dateModified": "2022-08-01T18:44:02.933741+03:00", "id": "8c4bfdbfbdcc46f3af3d7178a0e369c9"}, {"dateModified": "2022-08-01T18:43:43.213795+03:00", "id": "244092f1286b4dc2afab7d319f3ce0e6"}, {"dateModified": "2022-08-01T18:43:41.863895+03:00", "id": "afc4294aab694970b6daf48cb2f039d0"}, {"dateModified": "2022-08-01T18:42:54.874229+03:00", "id": "5dcda6940e6744db8af0ce4e61c31373"}, {"dateModified": "2022-08-01T18:42:54.841960+03:00", "id": "023d3744d00f4281a3dc47f5f1dd9740"}, {"dateModified": "2022-08-01T18:42:30.425399+03:00", "id": "7adb6d8aa09f401c899b1123870b8ba1"}, {"dateModified": "2022-08-01T18:41:42.191175+03:00", "id": "4e1246e377dd487d88b5784ce65962e4"}, {"dateModified": "2022-08-01T18:41:41.741347+03:00", "id": "78e63f2e0bba4acead2de60044059916"}, {"dateModified": "2022-08-01T18:41:36.559430+03:00", "id": "c8ccb25b793b41d3889feb66d89eedba"}, {"dateModified": "2022-08-01T18:41:12.987334+03:00", "id": "cbcc5da0d62f428b8db4a995c1df3be8"}, {"dateModified": "2022-08-01T18:40:27.603885+03:00", "id": "519e0590cbf04b1f9efcc393647f1437"}, {"dateModified": "2022-08-01T18:40:09.277335+03:00", "id": "b502a533a8b441958086345ee8e67fcc"}, {"dateModified": "2022-08-01T18:39:52.811088+03:00", "id": "b820e55684cc4c1188c6300f8bc87f92"}, {"dateModified": "2022-08-01T18:39:32.642896+03:00", "id": "9623cf37fc1b4ab29db2ef4343b59065"}, {"dateModified": "2022-08-01T18:39:31.756197+03:00", "id": "e79bd69e74f1446db5ff43730da2a2cf"}, {"dateModified": "2022-08-01T18:39:26.933425+03:00", "id": "92d5bfcc929e4f998fd3d49746202d23"}, {"dateModified": "2022-08-01T18:38:15.074195+03:00", "id": "6a3a4616a1004c269b71b7f4c887d2f9"}], "next_page": {"offset": 1659368295.08, "path": "/api/2.5/tenders?descending=1&offset=1659368295.08", "uri": "https://public.api.openprocurement.org/api/2.5/tenders?descending=1&offset=1659368295.08"}, "prev_page": {"offset": 1659370131.63, "path": "/api/2.5/tenders?offset=1659370131.63", "uri": "https://public.api.openprocurement.org/api/2.5/tenders?offset=1659370131.63"}}');
	}

	public function getTestItemData()
	{
		$this->data = json_decode('{"data": {"procurementMethod": "open", "tenderPeriod": {"startDate": "2015-06-02T08:00:00+00:00", "endDate": "2015-06-05T08:00:00+00:00"}, "description": "\u041b\u0438\u0441\u0442\u0438 \u0441\u0442\u0430\u043b\u0435\u0432\u0456", "title": "\u041b\u0438\u0441\u0442\u0438 \u0441\u0442\u0430\u043b\u0435\u0432\u0456", "minimalStep": {"currency": "UAH", "amount": 250.0, "valueAddedTaxIncluded": true}, "items": [{"description": "\u041b\u0438\u0441\u0442\u0438 \u0441\u0442\u0430\u043b\u0435\u0432\u0456: \u0420\u043e\u0437\u043c\u0456\u0440 \u043b\u0438\u0441\u0442\u0430, \u043c: 1\u04452, \u041c\u0430\u0440\u043a\u0430 \u0441\u0442\u0430\u043b\u0456: 3 \u0441\u043f, \u0442\u043e\u0432\u0449\u0438\u043d\u0430 3 \u043c\u043c; \u0420\u043e\u0437\u043c\u0456\u0440 \u043b\u0438\u0441\u0442\u0430, \u043c: 1,5\u04451,5, \u041c\u0430\u0440\u043a\u0430 \u0441\u0442\u0430\u043b\u0456: 3 \u0441\u043f, \u0442\u043e\u0432\u0449\u0438\u043d\u0430 4 \u043c\u043c; \u0420\u043e\u0437\u043c\u0456\u0440 \u043b\u0438\u0441\u0442\u0430, \u043c: 1,5\u04453, \u041c\u0430\u0440\u043a\u0430 \u0441\u0442\u0430\u043b\u0456: 3 \u0441\u043f, \u0442\u043e\u0432\u0449\u0438\u043d\u0430 6 \u043c\u043c.", "classification": {"scheme": "CPV", "description": "\u0421\u0442\u0430\u043b\u044c\u00a0", "id": "14622000-7"}, "additionalClassifications": [{"scheme": "\u0414\u041a\u041f\u041f", "id": "24.10.3", "description": "\"\u041f\u0440\u043e\u043a\u0430\u0442 \u043f\u043b\u0430\u0441\u043a\u0438\u0439 \u0437\u0456 \u0441\u0442\u0430\u043b\u0456, \u0431\u0435\u0437 \u043f\u043e\u0434\u0430\u043b\u044c\u0448\u043e\u0433\u043e \u043e\u0431\u0440\u043e\u0431\u043b\u044f\u043d\u043d\u044f, \u043a\u0440\u0456\u043c \u0433\u0430\u0440\u044f\u0447\u043e\u0433\u043e \u043f\u0440\u043e\u043a\u0430\u0442\u0443\u0432\u0430\u043d\u043d\u044f\""}], "deliveryAddress": {"postalCode": "73000", "countryName": "\u0423\u043a\u0440\u0430\u0457\u043d\u0430", "streetAddress": "\u043c. \u0425\u0435\u0440\u0441\u043e\u043d, \u043f\u0440-\u043a\u0442. \u0423\u0448\u0430\u043a\u043e\u0432\u0430, 4", "region": "\u0425\u0435\u0440\u0441\u043e\u043d\u0441\u044c\u043a\u0430", "locality": ""}, "deliveryDate": {"startDate": "2015-06-21T21:00:00+00:00", "endDate": "2015-12-30T22:00:00+00:00"}, "unit": {"code": "KGM", "name": "\u043a\u0456\u043b\u043e\u0433\u0440\u0430\u043c\u0438"}, "quantity": 1650}], "value": {"currency": "UAH", "amount": 25000.0, "valueAddedTaxIncluded": true}, "submissionMethod": "electronicAuction", "procuringEntity": {"contactPoint": {"telephone": "0552-48-14-15", "url": "http://www.seaport.kherson.ua", "faxNumber": "0552-26-40-70", "name": "\u0414\u0435\u0440\u0436\u0430\u0432\u043d\u0435 \u043f\u0456\u0434\u043f\u0440\u0438\u0454\u043c\u0441\u0442\u0432\u043e \u00ab\u0425\u0435\u0440\u0441\u043e\u043d\u0441\u044c\u043a\u0438\u0439 \u043c\u043e\u0440\u0441\u044c\u043a\u0438\u0439 \u0442\u043e\u0440\u0433\u043e\u0432\u0435\u043b\u044c\u043d\u0438\u0439 \u043f\u043e\u0440\u0442\u00bb.", "email": "s.tkachenko@seaport.kherson.ua"}, "identifier": {"scheme": "UA-EDR", "id": "01125695", "legalName": "\u0414\u0435\u0440\u0436\u0430\u0432\u043d\u0435 \u043f\u0456\u0434\u043f\u0440\u0438\u0454\u043c\u0441\u0442\u0432\u043e \u00ab\u0425\u0435\u0440\u0441\u043e\u043d\u0441\u044c\u043a\u0438\u0439 \u043c\u043e\u0440\u0441\u044c\u043a\u0438\u0439 \u0442\u043e\u0440\u0433\u043e\u0432\u0435\u043b\u044c\u043d\u0438\u0439 \u043f\u043e\u0440\u0442\u00bb."}, "name": "\u0414\u0435\u0440\u0436\u0430\u0432\u043d\u0435 \u043f\u0456\u0434\u043f\u0440\u0438\u0454\u043c\u0441\u0442\u0432\u043e \u00ab\u0425\u0435\u0440\u0441\u043e\u043d\u0441\u044c\u043a\u0438\u0439 \u043c\u043e\u0440\u0441\u044c\u043a\u0438\u0439 \u0442\u043e\u0440\u0433\u043e\u0432\u0435\u043b\u044c\u043d\u0438\u0439 \u043f\u043e\u0440\u0442\u00bb.", "address": {"postalCode": "73000", "countryName": "\u0423\u043a\u0440\u0430\u0457\u043d\u0430", "streetAddress": "\u043f\u0440-\u043a\u0442 \u0423\u0448\u0430\u043a\u043e\u0432\u0430, 4", "region": "\u0425\u0435\u0440\u0441\u043e\u043d\u0441\u044c\u043a\u0430", "locality": "\u0425\u0435\u0440\u0441\u043e\u043d"}}, "status": "cancelled", "tenderID": "UA-2015-05-26-000052", "enquiryPeriod": {"startDate": "2015-05-26T16:50:12.478636+03:00", "endDate": "2015-05-28T08:00:00+00:00"}, "owner": "e-tender.biz", "dateModified": "2015-05-26T16:50:36.605094+03:00", "awardCriteria": "lowestCost", "dateCreated": "2015-05-26T16:50:12.627683+03:00", "procurementMethodType": "belowThreshold", "id": "c520b5b093d64e98bd5cc08287e97bba"}}');
	}

}