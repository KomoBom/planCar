<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

use Bitrix\Main\Loader;
Loader::includeModule('iblock');

class CcrSchEmp extends CBitrixComponent
{
	private function getCurrentUserLogin(){
		global $USER;
		return $USER->GetLogin();
	}

	public function prepareDateForRecord($startTime, $endTime)
	{
		$timeForRecord = $startTime . " -- " . $endTime;
		return $timeForRecord;
	}

	public function prepareStartTime()
	{
		$startTime = substr($_GET['startTime'], 0, 10) . " " . substr($_GET['startTime'], 11, 2) . ":00:00";
		return $startTime;
	}

	public function prepareEndTime()
	{
		$endTime = substr($_GET['endTime'], 0, 10) . " " . substr($_GET['endTime'], 11, 2) . ":00:00";
		return $endTime;
	}

	private function prepareRecordStartTime($value)
	{
		$recordStartTime = substr($value, 0, 19);
		return $recordStartTime;
	}

	private function prepareRecordEndTime($value)
	{
		$recordEndTime = substr($value, -19);
		return $recordEndTime;
	}	

	private function getTrips($iBId)
	{
		$elIterator = \CIBlockElement::GetList([], ['IBLOCK_ID' => $iBId]);
		while ($el = $elIterator->GetNextElement()) {
			$arFields[] = $el->GetFields();
		}
		foreach ($arFields as $key => $value) {
			$propIterator = CIBlockElement::GetProperty($iBId, $arFields[$key]['ID'], ["sort"=>"asc"], ['CODE' => 'trip']);
			$keyArr = $arFields[$key]['CODE'];
			$propArr[$keyArr] = $propIterator->Fetch();
		}
		return $propArr;
	}

	public function checkTrips($iBId)
	{
		$propArr = $this->getTrips($iBId);
		foreach ($propArr as $key => $value) {

			$startTime = $this->prepareStartTime();
			$endTime = $this->prepareEndTime();		
			$recordStartTime = $this->prepareRecordStartTime($propArr[$key]['VALUE']);			
			$recordEndTime = $this->prepareRecordEndTime($propArr[$key]['VALUE']);		

			if (empty($propArr[$key]['VALUE'])) {
				$availableCars[] = $key;
				continue;
			} else if ((!empty($propArr[$key]['VALUE'])) 
						&& (($recordStartTime <= $startTime && $startTime <= $recordEndTime) 
						|| ($recordStartTime <= $endTime && $endTime <= $recordEndTime))) {
				
				continue;	
			} else {
				$availableCars[] = $key;
			}
		}
		return $availableCars;
	}

	public function getCarName($iBId, $carCodesArr)
	{
		foreach ($carCodesArr as $key => $value) {
			$elObj = \CIBlockElement::GetList([], ["IBLOCK_ID" => $iBId, "CODE" => $value]);
			while ($el = $elObj->Fetch()) {
				$carArr[] = $el;
			}
		}
		return $carArr;
	}

	public function getModelArr($carArr)
	{
		foreach ($carArr as $key => $value) {
			$carId = $carArr[$key]['ID'];
			$res = CIBlockElement::GetByID($carId);
			while($obj = $res->GetNextElement()) {
				$bindArr[] = $obj->GetProperties();
			}
		}
		foreach ($bindArr as $key => $value) {
			$modelsId = $bindArr[$key]['model']['VALUE'];
			$res = CIBlockElement::GetByID($modelsId);
			while($obj = $res->Fetch()) {
				$modelNames[] = $obj['NAME'];
			}
		}
		return array_unique($modelNames);
	}

	public function checkCategoryComfort($comfortCategory)
	{
		$procPos = $this->getUserPos();
		foreach ($comfortCategory as $key => $value) {
			if ($comfortCategory[$key] != $procPos) {
				unset($comfortCategory[$key]);
			}
		}
		return $comfortCategory;
	}

	private function getUserPos()
	{
		$login = $this->getCurrentUserLogin();
		$rsUser = CUser::GetByLogin($login);
		$userArr = $rsUser->Fetch();
		$position = $userArr['WORK_POSITION'];
		$procPos= substr($position, -1);
		return $procPos;
	}

	public function getCategoryComfort($modelArr)
	{
		foreach ($modelArr as $key => $value) {
			$modelNames = $modelArr[$key];
			$res = CIBlockElement::GetList([], ['NAME' => $modelNames]);
			$obj = $res->GetNextElement();
			$propsArrRes[] = $obj->GetProperties();
		}
		foreach ($propsArrRes as $key => $value) {
			$propsArr[] = $propsArrRes[$key]['comfort_category']['VALUE'];
		}
		return array_unique($propsArr);
	}

	public function checkModelArr($iBId)
	{
		$procPos = $this->getUserPos();

		$res = \CIBlockElement::GetList([], ['IBLOCK_ID' => $iBId]);
		while ($el = $res->GetNextElement()) {
			$elArr[] = $el->GetFields();
		}
		foreach ($elArr as $key => $value) {
			$propIterator = CIBlockElement::GetProperty($iBId, $elArr[$key]['ID'], ["sort"=>"asc"], ['CODE' => 'comfort_category']);
			$keyArr = $elArr[$key]['CODE'];
			$propArr[$keyArr] = $propIterator->Fetch();
		}
		foreach ($propArr as $key => $value) {
			if ($propArr[$key]['VALUE'] != $procPos) {
				unset($propArr[$key]);
			} else {
				$modelsCategory[$key] = $propArr[$key]['VALUE'];
			}
		}
		foreach ($modelsCategory as $key => $value) {
			$iterator = \CIBlockElement::GetList([], ['IBLOCK_ID' => $iBId, 'CODE' => $key]);
			while($el = $iterator->Fetch()) {
				$modelNamesArr[] = $el['NAME'];
			}
		}
		return $modelNamesArr;
	}

	public function getDriver($iBId)
	{
		$availableCars = $this->checkTrips($iBId);

		foreach ($availableCars as $key => $value) {
			$carCode = $availableCars[$key];
			$iBlock = \CIBlock::GetByID($iBId)->Fetch();
			$iterator = \CIBlockElement::GetList([], ["IBLOCK_CODE" => $iBlock['CODE'], "CODE" => $carCode]);
			$el = $iterator->GetNextElement()->GetProperty('driver');
			$driversIdArr[] = $el['VALUE'];
		}

		foreach ($driversIdArr as $value) {
			$res = \CIBlockElement::GetByID($value);
			$driver = $res->Fetch();
			$driversNamesArr[] = $driver['NAME'];
		}
		return $driversNamesArr;
	}

	public function checkKeysArr()
	{
		if (array_key_exists("car",$_GET) &&
			array_key_exists("model",$_GET) &&
			array_key_exists("category",$_GET) &&
			array_key_exists("driver",$_GET)) {
			return true;
		} else {
			return false;
		}
	}

	public function prepareGetFields($car, $model, $category, $driver)
	{
		$car = str_replace("+", " ", $car);
		$model = str_replace("+", " ", $model);
		$category = str_replace("+", " ", $category);
		$driver = str_replace("+", " ", $driver);
		$preparedGetFields = [
			"car"      => $car,
			"model"    => $model,
			"category" => $category,
			"driver"   => $driver];
		return $preparedGetFields;
	}

	public function getCurrentCarParams($iBIdCars, $carName)
	{
		$iterator = \CIBlockElement::GetList([], ["IBLOCK_ID" => $iBIdCars, "NAME" => $carName]);
		$el = $iterator->GetNextElement();
		$model = $el->GetProperty('model');
		$modelId = $model['VALUE'];
		$driver = $el->GetProperty('driver');
		$driverId = $driver['VALUE'];

		$res = \CIBlockElement::GetByID($modelId);
		$modelObj = $res->GetNextElement();
		$modelProp = $modelObj->GetProperty('comfort_category');
		$category = $modelProp['VALUE'];

		$resModelName = \CIBlockElement::GetByID($modelId);
		$modelNameObj = $resModelName->Fetch();
		$modelName = $modelNameObj['NAME'];

		$resDriverName = \CIBlockElement::GetByID($driverId);
		$driverNameObj = $resDriverName->Fetch();
		$driverName = $driverNameObj['NAME'];

		$currentCarParamsArr = [
			"car"      => $carName,
			"model"    => $modelName,
			"category" => $category,
			"driver"   => $driverName
		];
		return $currentCarParamsArr;
	}

	public function compareParams($car, $model, $category, $driver, $iBIdCars)
	{
		$currentCarParamsArr = $this->getCurrentCarParams($iBIdCars, $car);
		if ($car == $currentCarParamsArr["car"] &&
			$model == $currentCarParamsArr["model"] &&
			$category = $currentCarParamsArr["category"] &&
			$driver == $currentCarParamsArr["driver"]) {
			return true;
		} else {
			return false;
		}
	}

	public function saveTrip($startTime, $endTime, $iBId)
	{
		$timeForRecord = $this->prepareDateForRecord($startTime, $endTime);
		$iterator = \CIBlockElement::GetList([], ["IBLOCK_ID" => $iBId, "NAME" => "Автомобиль 4"]);
		if ($el = $iterator->Fetch()) {
			\CIBlockElement::SetPropertyValues($el['ID'], $el['IBLOCK_ID'], [$timeForRecord], "trip");
		}
	}
}