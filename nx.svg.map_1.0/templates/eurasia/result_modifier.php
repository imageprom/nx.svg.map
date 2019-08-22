<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?

$arResult['TYPES'] = $arResult['FIELDS']['UF_TYPE']['VALUES'];

foreach ($arResult['ITEMS'] as $arItem) {	
	if($arItem['UF_COUNTRY_CODE']) {
		$arResult['COUNTRIES'][$arItem['UF_COUNTRY_CODE']][] = $arItem;
	}
}
?>