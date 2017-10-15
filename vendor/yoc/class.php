<?php

return [
	'DB'                       => YOC_PATH . '/db/DB.php',
	'UserStatus'               => BASE_PATH . '/libs/Const.php',
	'MessageCategory'          => BASE_PATH . '/libs/Const.php',
	'Http'                     => YOC_PATH . '/Libs/Http.php',
	'Sign'                     => BASE_PATH . '/libs/Sign.php',
	'Code'                     => BASE_PATH . '/config/code.php',
	'Token'                    => BASE_PATH . '/libs/Sign.php',
	'Encode'                   => BASE_PATH . '/libs/Encode.php',
	'Middle'                   => YOC_PATH . '/Middle/Middle.php',
	'Authorize'                => BASE_PATH . '/libs/Authorize.php',
	'Encrypted'                => YOC_PATH . '/Libs/Encrypted.php',
	'ViewException'            => YOC_PATH . '/Exception/ViewException.php',
	'TaskException'            => YOC_PATH . '/Exception/TaskException.php',
	'ActiveController'         => BASE_PATH . '/app/ActiveController.php',
	'DBErrorException'         => YOC_PATH . '/Exception/DBErrorException.php',
	'RequestException'         => YOC_PATH . '/Exception/RequestException.php',
	'NotFoundException'        => YOC_PATH . '/Exception/NotFoundException.php',
	'DataEmptyException'       => YOC_PATH . '/Exception/DataEmptyException.php',
	'UnknownClassException'    => YOC_PATH . '/Exception/UnknownClassException.php',
	'UnknownMethodException'   => YOC_PATH . '/Exception/UnknownMethodException.php',
	'UnknownFunctionException' => YOC_PATH . '/Exception/UnknownFunctionException.php',
	'AuthenticationException'  => YOC_PATH . '/Exception/AuthenticationException.php',
];