<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
session_start();

use Bitrix\Main\Loader,
Bitrix\Iblock;

require_once 'class.php';

if(!Loader::includeModule("iblock"))
{
    ShowError("Модуль Информационных блоков не установлен");
    return;
}

if (empty($_GET) || array_key_exists("clear_cache",$_GET)) {
	?>
	<form id="carForm" action="" method="GET">
		<div>Выберите время начала поездки: <input type="datetime-local" name="startTime"></div>
		<div>Выберите время окончания поездки: <input type="datetime-local" name="endTime"></div>
		<input type="submit">
	</form>
	<?
} else if ($this->compareParams($_GET['car'], $_GET['model'], $_GET['category'], $_GET['driver'], $arParams["IBLOCK_ID_CARS"])) {
	$this->saveTrip($_SESSION['startTime'], $_SESSION['endTime'], $arParams["IBLOCK_ID_CARS"]); ?>
	<div>Поездка сохранена!</div> <?
} else if ($this->checkKeysArr()) {
	$preparedGetFieldsArr = $this->prepareGetFields($_GET['car'], $_GET['model'], $_GET['category'], $_GET['driver']);
	$currentCarParamsArr = $this->getCurrentCarParams($arParams["IBLOCK_ID_CARS"], $preparedGetFieldsArr['car']);?>
	
	<div>Для автомобиля <b><?=$preparedGetFieldsArr['car']?></b> доступны следующие критерии выбора:</div>
	<div>Модель: <?=$currentCarParamsArr['model']?></div>
	<div>Категория комфорта: <?=$currentCarParamsArr['category']?></div>
	<div>Водитель: <?=$currentCarParamsArr['driver']?></div>
	<input type="button" onclick="history.back();" value="Повторить отправку формы"/>
<?
} else {
	$availableCars = $this->checkTrips($arParams["IBLOCK_ID_CARS"]);
	$carArr = $this->getCarName($arParams["IBLOCK_ID_CARS"], $availableCars);
	?>
	<form action="" method="GET">
		<div>Время начала поездки: <?=$this->prepareStartTime();?></div>
		<div>Время окончания начала поездки: <?=$this->prepareEndTime();?></div> <?
		$_SESSION['startTime'] = $this->prepareStartTime();
		$_SESSION['endTime'] = $this->prepareEndTime(); ?>
		<div>Доступные автомобили:
			<select required name="car"> 
				<option disabled selected>Автомобили</option><?
				foreach ($carArr as $key => $value) { ?>
					<option value="<?=$carArr[$key]['NAME'];?>"><?=$carArr[$key]['NAME'];?></option> <?
				} ?>
			</select>		
		</div> <?
		$modelArr = $this->getModelArr($carArr);
		$modelAvail = $this->checkModelArr($arParams["IBLOCK_MODEL_CAR"]); ?>
		<div>Доступные модели авто:
			<select required name="model"> 
				<option disabled selected>Модель:</option><?
				foreach ($modelAvail as $value) { ?>
					<option value="<?=$value;?>"><?=$value;?></option> <?
				} ?>
			</select>		
		</div> <?
		$comfortCategory = $this->getCategoryComfort($modelArr); 
		$comfortCategoryAvail = $this->checkCategoryComfort($comfortCategory); 
		?>
		<div>Ваша категория комфорта:
			<select required name="category"> 
				<option disabled selected>Категория комфорта</option><?
				foreach ($comfortCategoryAvail as $value) { ?>
					<option value="<?=$value;?>"><?=$value;?></option> <?
				} ?>
			</select>		
		</div> <?
		$driversNamesArr = $this->getDriver($arParams["IBLOCK_ID_CARS"]); ?>
		<div>Ваш водитель:
			<select required name="driver"> 
				<option disabled selected>Водитель</option><?
				foreach ($driversNamesArr as $value) { ?>
					<option value="<?=$value;?>"><?=$value;?></option> <?
				} ?>
			</select>		
		</div>
		<input type="submit">
	</form>
	<?
}