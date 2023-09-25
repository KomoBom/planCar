<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if(!CModule::IncludeModule("iblock"))
	return;

$arIBlocks=array();
$db_iblock = CIBlock::GetList(array("SORT"=>"ASC"), array("SITE_ID"=>$_REQUEST["site"], "TYPE" => ($arCurrentValues["IBLOCK_TYPE"]!="-"?$arCurrentValues["IBLOCK_TYPE"]:"")));
while($arRes = $db_iblock->Fetch())
	$arIBlocks[$arRes["ID"]] = "[".$arRes["ID"]."] ".$arRes["NAME"];

$arComponentParameters = array(
    "PARAMETERS" => array(
        "IBLOCK_ID_CARS" => array(
            "PARENT" => "BASE",
            "NAME" => "Инфоблок Автомобилей",
            "TYPE" => "LIST",
            "VALUES" => $arIBlocks,
            "REFRESH" => "Y",
        ),
		"IBLOCK_MODEL_CAR" => array(
            "PARENT" => "BASE",
            "NAME" => "Инфоблок Моделей авто",
            "TYPE" => "LIST",
            "VALUES" => $arIBlocks,
            "REFRESH" => "Y",
        ),	
    ),
);
