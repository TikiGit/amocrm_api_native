<?

$subdomain = 'test'; //Поддомен нужного аккаунта
$link = 'https://' . $subdomain . '.amocrm.ru/oauth2/access_token'; //Формируем URL для запроса

/** Соберем данные для запроса */
$data = [
	'client_id' => 'xxxx', // id нашей интеграции
	'client_secret' => 'xxxx', // секретный ключ нашей интеграции
	'grant_type' => 'authorization_code',
	'code' => 'xxxxxx', // код авторизации нашей интеграции
	'redirect_uri' => 'https://test.ru/',// домен сайта нашей интеграции
];

/**
 * Нам необходимо инициировать запрос к серверу.
 * Воспользуемся библиотекой cURL (поставляется в составе PHP).
 * Вы также можете использовать и кроссплатформенную программу cURL, если вы не программируете на PHP.
 */
$curl = curl_init(); //Сохраняем дескриптор сеанса cURL
/** Устанавливаем необходимые опции для сеанса cURL  */
curl_setopt($curl,CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl,CURLOPT_USERAGENT,'amoCRM-oAuth-client/1.0');
curl_setopt($curl,CURLOPT_URL, $link);
curl_setopt($curl,CURLOPT_HTTPHEADER,['Content-Type:application/json']);
curl_setopt($curl,CURLOPT_HEADER, false);
curl_setopt($curl,CURLOPT_CUSTOMREQUEST, 'POST');
curl_setopt($curl,CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($curl,CURLOPT_SSL_VERIFYPEER, 1);
curl_setopt($curl,CURLOPT_SSL_VERIFYHOST, 2);
$out = curl_exec($curl); //Инициируем запрос к API и сохраняем ответ в переменную
$code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
curl_close($curl);
/** Теперь мы можем обработать ответ, полученный от сервера. Это пример. Вы можете обработать данные своим способом. */
$code = (int)$code;

// коды возможных ошибок
$errors = [
	400 => 'Bad request',
	401 => 'Unauthorized',
	403 => 'Forbidden',
	404 => 'Not found',
	500 => 'Internal server error',
	502 => 'Bad gateway',
	503 => 'Service unavailable',
];

try
{
	/** Если код ответа не успешный - возвращаем сообщение об ошибке  */
	if ($code < 200 || $code > 204) {
		throw new Exception(isset($errors[$code]) ? $errors[$code] : 'Undefined error', $code);
	}
}
catch(\Exception $e)
{
	die('Ошибка: ' . $e->getMessage() . PHP_EOL . 'Код ошибки: ' . $e->getCode());
}

/**
 * Данные получаем в формате JSON, поэтому, для получения читаемых данных,
 * нам придётся перевести ответ в формат, понятный PHP
 */
$response = json_decode($out, true);

$access_token = $response['access_token']; //Access токен
$refresh_token = $response['refresh_token']; //Refresh токен
$token_type = $response['token_type']; //Тип токена
$expires_in = $response['expires_in']; //Через сколько действие токена истекает

// выведем наши токены. Скопируйте их для дальнейшего использования
// access_token будет использоваться для каждого запроса как идентификатор интеграции
var_dump($access_token);
var_dump($refresh_token );


function returnNewToken($token) {

	$link = 'https://site.amocrm.ru/oauth2/access_token'; //Формируем URL для запроса

	/** Соберем данные для запроса */
	$data = [
		'client_id' => 'xxx',
		'client_secret' => 'xxx',
		'grant_type' => 'refresh_token',
		'refresh_token' => $token,
		'redirect_uri' => 'https://site.ru/',
	];

	/**
	 * Нам необходимо инициировать запрос к серверу.
	 * Воспользуемся библиотекой cURL (поставляется в составе PHP).
	 * Вы также можете использовать и кроссплатформенную программу cURL, если вы не программируете на PHP.
	 */
	$curl = curl_init(); //Сохраняем дескриптор сеанса cURL
	/** Устанавливаем необходимые опции для сеанса cURL  */
	curl_setopt($curl,CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl,CURLOPT_USERAGENT,'amoCRM-oAuth-client/1.0');
	curl_setopt($curl,CURLOPT_URL, $link);
	curl_setopt($curl,CURLOPT_HTTPHEADER,['Content-Type:application/json']);
	curl_setopt($curl,CURLOPT_HEADER, false);
	curl_setopt($curl,CURLOPT_CUSTOMREQUEST, 'POST');
	curl_setopt($curl,CURLOPT_POSTFIELDS, json_encode($data));
	curl_setopt($curl,CURLOPT_SSL_VERIFYPEER, 1);
	curl_setopt($curl,CURLOPT_SSL_VERIFYHOST, 2);
	$out = curl_exec($curl); //Инициируем запрос к API и сохраняем ответ в переменную
	$code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
	curl_close($curl);
	/** Теперь мы можем обработать ответ, полученный от сервера. Это пример. Вы можете обработать данные своим способом. */
	$code = (int)$code;
	$errors = [
		400 => 'Bad request',
		401 => 'Unauthorized',
		403 => 'Forbidden',
		404 => 'Not found',
		500 => 'Internal server error',
		502 => 'Bad gateway',
		503 => 'Service unavailable',
	];

	try
	{
		/** Если код ответа не успешный - возвращаем сообщение об ошибке  */
		if ($code < 200 || $code > 204) {
			throw new Exception(isset($errors[$code]) ? $errors[$code] : 'Undefined error', $code);
		}
	}
	catch(\Exception $e)
	{
		die('Ошибка: ' . $e->getMessage() . PHP_EOL . 'Код ошибки: ' . $e->getCode());
	}

	/**
	 * Данные получаем в формате JSON, поэтому, для получения читаемых данных,
	 * нам придётся перевести ответ в формат, понятный PHP
	 */

	$response = json_decode($out, true);

	if($response) {

		/* записываем конечное время жизни токена */
		$response["endTokenTime"] = time() + $response["expires_in"];

		$responseJSON = json_encode($response);

		/* передаём значения наших токенов в файл */
		$filename = $_SERVER["DOCUMENT_ROOT"]."/amo_question.php";
		$f = fopen($filename,'w');
		fwrite($f, $responseJSON);
		fclose($f);

		$response = json_decode($responseJSON, true);

		return $response;
	}
	else {
		return false;
	}

}



$arrContactParams = [
	// поля для сделки 
	"PRODUCT" => [
		"nameForm"	=> "Название формы",

		"nameProduct" 	=> "Название товара",
		"price"		=> "Цена",
		"descProduct"	=> "Описание заказа",

		"namePerson"	=> "Имя пользователя",
		"phonePerson"	=> "Телефон",
		"emailPerson"	=> "Email пользователя",
		"messagePerson"	=> "Сообщение от пользователя",
	],
	// поля для контакта 
	"CONTACT" => [
		"namePerson"	=> "Имя пользователя",
		"phonePerson"	=> "Телефон",
		"emailPerson"	=> "Email пользователя",
		"messagePerson"	=> "Сообщение от пользователя",
	]
];



/* ============================================ */
/* ФУНКЦИЯ ДЛЯ ДОБАВЛЕНИЯ КОНТАКТА */
/* ============================================ */

function amoAddContact($access_token, $arrContactParams) {

	
	$contacts['request']['contacts']['add'] = array(
		[
			'name' => $arrContactParams["CONTACT"]["namePerson"],
			'tags' => 'авто отправка',
			'custom_fields'	=> [
				// ИМЯ ПОЛЬЗОВАТЕЛЯ 
				[
					'id'	=> 518661,
					"values" => [
						[
							"value" => $arrContactParams["CONTACT"]["namePerson"],
						]
					]
				],
				// ТЕЛЕФОН
				[
					'id'	=> 518139,
					"values" => [
						[
							"value" => $arrContactParams["CONTACT"]["phonePerson"],
						]
					]
				],
				// EMAIL 
				[
					'id'	=> 518595,
					"values" => [
						[
							"value" => $arrContactParams["CONTACT"]["emailPerson"],
						]
					]
				],
				// СООБЩЕНИЕ
				[
					'id'	=> 532695,
					"values" => [
						[
							"value" => $arrContactParams["CONTACT"]["messagePerson"],
						]
					]
				]
			]
		]
	);


	/* Формируем заголовки */
	$headers = [
		"Accept: application/json",
		'Authorization: Bearer ' . $access_token
	];
	
	$link='https://site.amocrm.ru/private/api/v2/json/contacts/set';

	$curl = curl_init(); //Сохраняем дескриптор сеанса cURL
	/** Устанавливаем необходимые опции для сеанса cURL  */
	curl_setopt($curl,CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl,CURLOPT_USERAGENT,'amoCRM-oAuth-client/1.0');
	curl_setopt($curl,CURLOPT_URL, $link);
	curl_setopt($curl,CURLOPT_CUSTOMREQUEST,'POST');
	curl_setopt($curl,CURLOPT_POSTFIELDS,json_encode($contacts));
	curl_setopt($curl,CURLOPT_HTTPHEADER, $headers);
	curl_setopt($curl,CURLOPT_HEADER, false);
	curl_setopt($curl,CURLOPT_SSL_VERIFYPEER, 1);
	curl_setopt($curl,CURLOPT_SSL_VERIFYHOST, 2);
	$out=curl_exec($curl); #Инициируем запрос к API и сохраняем ответ в переменную
	$code=curl_getinfo($curl,CURLINFO_HTTP_CODE);
	curl_close($curl);
	$Response=json_decode($out,true);
	$account=$Response['response']['account'];
	echo 'Данные о пользователе:'; echo '
'; print_r($Response); echo '
';
return $Response["response"]["contacts"]["add"]["0"]["id"];

}


/* ============================================ /
/ ============================================ */


/* ============================================ */
/* ФУНКЦИЯ ДЛЯ СОЗДАНИЯ НОВОГО ЗАКАЗА */
/* ============================================ */

function amoAddTask($access_token, $arrContactParams, $contactId = false) {

	$arrTaskParams = [  
		'add' => [
			0 => [
				'name'          => $arrContactParams["PRODUCT"]["nameForm"],
				'price'         => $arrContactParams["PRODUCT"]["price"],
				'pipeline_id'   => '9168',
				'tags'          => [
					'авто отправка',
					$arrContactParams["PRODUCT"]["nameForm"]
				],
				'status_id'     => '10937736',
				'custom_fields'	=> [
					/* ОПИСАНИЕ ЗАКАЗА */
					[
						'id'	=> 531865,
						"values" => [
							[
								"value" => $arrContactParams["PRODUCT"]["descProduct"],
							]
						]
					],
					/* ИМЯ ПОЛЬЗОВАТЕЛЯ */
					[
						'id'	=> 525741,
						"values" => [
							[
								"value" => $arrContactParams["PRODUCT"]["namePerson"],
							]
						]
					],
					/* ТЕЛЕФОН */
					[
						'id'	=> 525687,
						"values" => [
							[
								"value" => $arrContactParams["PRODUCT"]["phonePerson"],
							]
						]
					],
					/* EMAIL */
					[
						'id'	=> 525739,
						"values" => [
							[
								"value" => $arrContactParams["PRODUCT"]["emailPerson"],
							]
						]
					],
					/* СООБЩЕНИЕ */
					[
						'id'	=> 528257,
						"values" => [
							[
								"value" => $arrContactParams["PRODUCT"]["messagePerson"],
							]
						]
					],
				],

				'contacts_id' => [
					0 => $contactId,
				],
			],
		],
	];


	$link = "https://site.amocrm.ru/api/v2/leads";

	$headers = [
        "Accept: application/json",
        'Authorization: Bearer ' . $access_token
	];

	$curl = curl_init();
	curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);
	curl_setopt($curl, CURLOPT_USERAGENT, "amoCRM-API-client-
	undefined/2.0");
	curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($arrTaskParams));
	curl_setopt($curl, CURLOPT_URL, $link);
	curl_setopt($curl, CURLOPT_HEADER,false);
	curl_setopt($curl,CURLOPT_COOKIEFILE,dirname(__FILE__)."/cookie.txt");
	curl_setopt($curl,CURLOPT_COOKIEJAR,dirname(__FILE__)."/cookie.txt");
	$out = curl_exec($curl);
	curl_close($curl);
	$result = json_decode($out,TRUE);

}




function amoCRMScript($paramsTask) {

	/* получаем значения токенов из файла */
	$dataToken = file_get_contents("путь до файла с токеном");
	$dataToken = json_decode($dataToken, true);

	/* проверяем, истёкло ли время действия токена Access */
	if($dataToken["endTokenTime"] < time()) {
		/* запрашиваем новый токен */
		$dataToken = returnNewToken($dataToken["refresh_token"]);
		$newAccess_token = $dataToken["access_token"];
	}
	else {
		$newAccess_token = $dataToken["access_token"];
	}

	if($paramsTask["CONTACT"]) {
		$idContact = amoAddContact($newAccess_token, $paramsTask);
	}

	amoAddTask($newAccess_token, $paramsTask, $idContact);

}

/* ===================================== */
/* ===================================== */

/*

function returnFieldsContact() {

	$access_token = 'xxxxxx';

	$headers = [
		"Accept: application/json",
		'Authorization: Bearer ' . $access_token
	];

	$link='https://test.amocrm.ru/api/v4/leads/custom_fields';

	$curl = curl_init(); //Сохраняем дескриптор сеанса cURL
	curl_setopt($curl,CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl,CURLOPT_USERAGENT,'amoCRM-oAuth-client/1.0');
	curl_setopt($curl,CURLOPT_URL, $link);
	curl_setopt($curl,CURLOPT_HTTPHEADER, $headers);
	curl_setopt($curl,CURLOPT_HEADER, false);
	curl_setopt($curl,CURLOPT_SSL_VERIFYPEER, 1);
	curl_setopt($curl,CURLOPT_SSL_VERIFYHOST, 2);
	$out=curl_exec($curl); 
	$code=curl_getinfo($curl,CURLINFO_HTTP_CODE);
	curl_close($curl);
	$Response=json_decode($out,true);
	$account=$Response['response']['account'];
}

function returnFieldsContact() {

	$access_token = 'xxxxxx';

	$headers = [
		"Accept: application/json",
		'Authorization: Bearer ' . $access_token
	];

	$link='https://test.amocrm.ru/api/v4/contacts/custom_fields';

	$curl = curl_init(); //Сохраняем дескриптор сеанса cURL
	curl_setopt($curl,CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl,CURLOPT_USERAGENT,'amoCRM-oAuth-client/1.0');
	curl_setopt($curl,CURLOPT_URL, $link);
	curl_setopt($curl,CURLOPT_HTTPHEADER, $headers);
	curl_setopt($curl,CURLOPT_HEADER, false);
	curl_setopt($curl,CURLOPT_SSL_VERIFYPEER, 1);
	curl_setopt($curl,CURLOPT_SSL_VERIFYHOST, 2);
	$out=curl_exec($curl); 
	$code=curl_getinfo($curl,CURLINFO_HTTP_CODE);
	curl_close($curl);
	$Response=json_decode($out,true);
	$account=$Response['response']['account'];
}

*/


?>