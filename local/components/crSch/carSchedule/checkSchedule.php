<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
use \Bitrix\Main\Context;

// echo "<pre>";
// print_r($_GET);
// echo "</pre>";
$values = $request->getQueryList();
print_r($values);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>