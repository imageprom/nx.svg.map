<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$requiredModules = array('highloadblock');

foreach ($requiredModules as $requiredModule) {
	if (!CModule::IncludeModule($requiredModule)) {
		ShowError(GetMessage('F_NO_MODULE'));
		return 0;
	}
}

$arResult = array();

use Bitrix\Highloadblock as HL;
use Bitrix\Main\Entity;

$arParams['HBLOCK_ID'] = intval($arParams['HBLOCK_ID']);

if(!$arParams['HBLOCK_ID']) {
    ShowError('HIB Not Selected');
    return false;
}

$arParams['AJAX'] = isset($_REQUEST['nx_ajax_map_action']) && $_REQUEST['nx_ajax_map_action'] == 'Y';

if($this->startResultCache(false, array(($arParams["CACHE_GROUPS"]==="N"? false: $USER->GetGroups())))) {

	$hlblock_id = $arParams['HBLOCK_ID'];

	$hlblock = HL\HighloadBlockTable::getById($hlblock_id)->fetch();

	if (empty($hlblock)) {
		ShowError('HIB Not Exist');
		return false;
	}

	$entity = HL\HighloadBlockTable::compileEntity($hlblock);

	// uf info
	$fields = $GLOBALS['USER_FIELD_MANAGER']->GetUserFields('HLBLOCK_'.$hlblock['ID'], 0, LANGUAGE_ID);

	foreach ($fields as &$field) {
		if($field['USER_TYPE_ID'] == 'enumeration') {

			$obEnum = new \CUserFieldEnum;
			$rsEnum = $obEnum->GetList(array(), array('USER_FIELD_ID' => $field['ID']));

			while($arEnum = $rsEnum->Fetch()) {
			    $field['VALUES'][$arEnum["ID"]] = $arEnum;
			}
		}
	}

	// sort
	$sortId = 'ID';
	$sortType = 'DESC';

	if (!empty($arParams['SORT_BY']) ) $sortId = $arParams['SORT_BY'];
	if (!empty($arParams['SORT_ORDER']) ) $sortType = $arParams['SORT_ORDER'];

	$main_query = new Entity\Query($entity);
	$main_query->setSelect(array('*'));
	$main_query->setOrder(array($sortId => $sortType));
	//$main_query->setSelect($select)
	//	->setGroup($group)
	//	->setOrder($order)
	//	->setOptions($ options);

	// filter 
	if (isset($arParams['FILTER_NAME']) && !empty($arParams['FILTER_NAME']) && preg_match('/^[A-Za-z_][A-Za-z01-9_]*$/', $arParams['FILTER_NAME'])) {
		global ${$arParams['FILTER_NAME']};
		$filter = ${$arParams['FILTER_NAME']};
		if (is_array($filter)){
			$main_query->setFilter($filter);
		}
	}

	$result = $main_query->exec();
	$result = new CDBResult($result);

	while ($row = $result->Fetch()) {
		foreach ($row as $k => $v) {
			if ($k == 'ID') {
				$tableColumns['ID'] = true;
				continue;
			}

			$arUserField = $fields[$k];

			if ($arUserField['SHOW_IN_LIST'] != 'Y') {
				continue;
			}

			$html = call_user_func_array(
				array($arUserField['USER_TYPE']['CLASS_NAME'], 'getadminlistviewhtml'),
				array(
					$arUserField,
					array(
						'NAME' => 'FIELDS['.$row['ID'].']['.$arUserField['FIELD_NAME'].']',
						'VALUE' => htmlspecialcharsbx($v)
					)
				)
			);

			if($html == '') {
				$html = '&nbsp;';
			}

			$tableColumns[$k] = true;

			$row['~'.$k] = $row[$k];
			$row[$k] = $html;
			
		}

		$rows[] = $row;
	}

	$arResult['ITEMS'] = $rows;
	$arResult['FIELDS'] = $fields;
	$arResult['COLUMNS'] = $tableColumns;

	$this->IncludeComponentTemplate(); 
}

if($arParams['AJAX']) {
	$this->setFrameMode(false);
	define("BX_COMPRESSION_DISABLED", true);
	ob_start();
	$this->IncludeComponentTemplate("ajax");
	$json = ob_get_contents();
	$APPLICATION->RestartBuffer();
	while(ob_end_clean());
	header('Content-Type: application/json; charset='.LANG_CHARSET);
	CMain::FinalActions();
	echo $json;
	die();
}