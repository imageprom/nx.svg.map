<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();

if(!CModule::IncludeModule('highloadblock')) return;

use Bitrix\Highloadblock as HL;
use Bitrix\Main\Entity;

$rsLang = \Bitrix\Highloadblock\HighloadBlockTable::getList(array('select' => array('*', 'NAME_LANG' => 'LANG.NAME')));

while ($arLang = $rsLang->fetch()) {
    if($arLang['NAME_LANG']) $arHIBLang[$arLang['ID']] = $arLang['NAME_LANG'];
    else $arHIBLang[$arLang['ID']] = $arLang['NAME'];
}

$rsHIBlock = HL\HighloadBlockTable::getList(array('select'=>array('*'), 'filter'=>array('!=TABLE_NAME' => '')));

while($arHib = $rsHIBlock->Fetch()) {
    $arHIBlock[$arHib['ID']] = $arHIBLang[$arHib['ID']];
    $arHIBlocks[$arHib['ID']] = $arHib;
}

if($arCurrentValues['HBLOCK_ID']) {

    $entity = HL\HighloadBlockTable::compileEntity($arHIBlocks[$arCurrentValues['HBLOCK_ID']]);
    $entityDataClass = $entity->getDataClass();

    $fields = $entity->getFields();
    foreach($fields as $code => $filed) {
        if($code != 'ID') {
            $ar_res = CUserTypeEntity::GetList(array('ID'=>'ASC'), array('FIELD_NAME' => $code));

            if($tmp = $ar_res->GetNext()) {
                $res = CUserTypeEntity::GetByID($tmp['ID']);
                if($res['EDIT_FORM_LABEL'][LANGUAGE_ID]) $arProperty[$res['FIELD_NAME']] = $res['EDIT_FORM_LABEL']['ru'];
                else $arProperty[$res['FIELD_NAME']] = $res['FIELD_NAME'];
            }
        }
    }
}

$arSorts = Array(
	'ASC' => GetMessage('T_IBLOCK_DESC_ASC'),
	'DESC' => GetMessage('T_IBLOCK_DESC_DESC'),
);

$arComponentParameters = array(
	'GROUPS' => array(
	),

	'PARAMETERS' => array(

        'TITLE' => Array(
            'NAME' => 'Заголовок',
            'TYPE' => 'TEXT',
            'PARENT' => 'BASE',
        ),

		'HBLOCK_ID' => array(
            'PARENT' => 'DATA_SOURCE',
			'NAME' => GetMessage('HLLIST_COMPONENT_BLOCK_ID_PARAM'),
            'TYPE' => 'LIST',
            'VALUES' => $arHIBlock,
            'REFRESH' => 'Y',
		),

		'SORT_BY'  =>  Array(
			'PARENT' => 'DATA_SOURCE',
			'NAME' => GetMessage('T_SORT_FIELD'),
            'TYPE' => 'LIST',
            'VALUES' =>  $arProperty,
			'DEFAULT' => '',
		),

		'SORT_ORDER'  =>  Array(
			'PARENT' => 'DATA_SOURCE',
			'NAME' => GetMessage('T_SORT_ORDER'),
			'TYPE' => 'LIST',
			'DEFAULT' => 'DESC',
			'VALUES' => $arSorts,
			'ADDITIONAL_VALUES' => 'Y',
		),

		'FILTER_NAME' => Array(
			'PARENT' => 'DATA_SOURCE',
			'NAME' => GetMessage('T_IBLOCK_FILER'),
			'TYPE' => 'TEXT',
			'DEFAULT' => 'arrMapFilter',
		),

		'CACHE_TIME'  =>  Array('DEFAULT' => 300),
		'CACHE_GROUPS' => array(
			'PARENT' => 'CACHE_SETTINGS',
			'NAME' => GetMessage('CP_BNL_CACHE_GROUPS'),
			'TYPE' => 'CHECKBOX',
			'DEFAULT' => 'Y',
		),		
	)
);