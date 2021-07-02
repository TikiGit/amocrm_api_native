# PHP ФУНКЦИИ ДЛЯ РАБОТЫ С AMOCRM ЧЕРЕЗ API

Всем привет! Здесь находится сборка скриптов, которые я использую для передачи значений из формы обратной связи в amoCRM через API данного сервиса. Мы рассмотрим скрипт для создания сделок с прязкой контактов.

Каждый тип запроса я разбил по функциям, а полное подключение выложил в отдельную функцию, в самом конце. На данный момент, на 2021 год, эта реализация отрабатывает без каких либо ошибок.

Для того чтобы подключить ваш проект к amoCRM нужно сделать следующие действия:
1) Создать аккаунт на amoCRM
2) После этого переходим в Настройки и создаем новую интеграцию. Во время создания интеграции вам нужно указать адрес вашего сайта, предоставить все доступы для данной интеграции после чего сохранить.
3) После создания интеграции, переходим во вкладку «Ключи и доступы» — эти данные нам понадобятся для авторизации нашей интеграции. Мы не будем их использовать при каждом запросе, но переодически они нам будут нужны.

Дополнительную инструкцию вы можете найти на моём сайте - https://prog-time.ru/kak-peredat-dannye-iz-formy-v-amocrm-s-pomoshhyu-api/

Внимание!!! Код авторизации обновляется каждые 20 мин, а значит если вы его скопируете за пару минут до обновления, вы можете не успеть сделать запрос и у вас выведется ошибка. Если у вас появилась ошибка связанная с авторизацией, то просто попробуйте заново копировать данные.

Теперь вам нужно создать PHP файл и в нем мы будем создавать подключение к нашей CRM системе.

Все примеры запросов есть в официальной документации CRM — https://www.amocrm.ru/developers/content/crm_platform/api-reference

## АВТОРИЗАЦИЯ ИНТЕГРАЦИИ

Первый запрос нам нужно сделать на авторизацию созданной интеграции. Для своей задачи я использовал «Упрощённую систему авторизации» — https://www.amocrm.ru/developers/content/oauth/step-by-step#easy_auth

Для начала нам нужно выполнить запрос на авторизацию, код написан ниже. Для запроса я буду использовать библиотеку CURL.

<b> !!! ВНИМАНИЕ! НА ЭКРАН ВЫВЕДИТСЯ ТОКЕН КОТОРЫЙ ВАМ НУЖНО СОХРАНИТЬ! ЕСЛИ У ВАС ПРОИЗОЙДЁТ СБОЙ ТО ВАМ ПРИДЁТСЯ ЗАНОВО МЕНЯТЬ ДАННЫЕ ДЛЯ ЗАПРОСА И ДЕЛАТЬ ЗАПРОС ПОВТОРНО !!! </b>

<pre>
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
</pre>


Полученный token и access_token вам нужно сохранить в БД или в файле. 
В дальнейшем access_token нужно будет использовать для повторного получения токена через 24 часа.

Для повторного получения вам нужно будет воспользоваться функцией <code>returnNewToken($token)</code>
Мы отправляем <code>access_token</code> и получаем новые <code>access_token</code> и <code>refresh_token</code>


<pre>

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

</pre>


## МАССИВ ВХОДНЫХ ПАРАМЕТРОВ

Для добавления информации я использую такой массив. То есть если вам нужно подключить новую форму, вы просто передаёте в данный массив информацию в соответствующие поля и сам массив кидаете в качестве аргумента в функции которые ма рассмотрим ниже.

Если вам 

<pre>
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
</pre>

## ДОБАВЛЕНИЕ НОВОГО КОНТАКТА

Для того чтобы создать новую сделку нужно предварительно создать контакт, чтобы этот контакт можно было привязать к сделке.
Для создания контакта я использую функцию <code>amoAddContact($access_token, $arrContactParams)</code> - первый аргумент токен, второй - массив параметров контакта.

Данная функция возвращает идентификатор созданного пользователя, который далее нужно будет передать в функцию создания заказа.

<pre>

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
	echo '<b>Данные о пользователе:</b>'; echo '<pre>'; print_r($Response); echo '</pre>';

	return $Response["response"]["contacts"]["add"]["0"]["id"];

}

/* ============================================ */
/* ============================================ */


</pre>


## СОЗДАНИЕ СДЕЛКИ

Сделка создаётся с помощью функции <code>amoAddTask($access_token, $arrContactParams, $contactId = false)</code>

$access_token - ваш токен
$arrContactParams - массив с информацией
$contactId - если нужно привязать контакт, то передаём id иначе "пустой"

<pre>

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

</pre>


## ИТОГОВАЯ ФУНКЦИЯ ПОЛНОГО СОЗДАНИЯ СДЕЛКИ С ПРИВЯЗКОЙ КОНТАКТА

$paramsTask - полный массив информации о пользователе и сделке
$dataToken - сюда получаем токен из файла в который его сохранили. Здесь можен быть запрос из БД.

<pre>

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

</pre>









