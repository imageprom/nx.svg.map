<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var array $arParams */
/** @var array $arResult */
$this->setFrameMode(true);
?>
<?=CUtil::JSEscape($arResult["FORM_ACTION"])?>
<section class="interactive-map">
<?if($arParams['TITLE']):?>
    <h3 class="map-title"><?=$arParams['TITLE']?></h3>
<?endif;?>
    <div class="map-caption">
        <?foreach ($arResult['TYPES'] as $arType):?>
            <div class="item-caption tooltip-<?=$arType['XML_ID']?>"><?=$arType['VALUE']?></div>
        <?endforeach?>
    </div>
    <div id="nxMapService" class="nx-map-container">
        <div id="nxMap" class="nx-svg-map"></div>
        <div class="wrap-label">
            <?foreach ($arResult['COUNTRIES'] as $code => $arCountry):?>
                <div data-for="<?=$code?>" class="label nx-svg-map-dot">
                    <div class="nx-svg-map-tooltip">
                        <?foreach ( $arCountry as $arItem):?>
                            <?if($arItem['UF_URL'] != '&nbsp;'):?>
                                <a href="<?=$arItem['UF_URL']?>" class="nx-svg-map-tooltip-item tooltip-<?=$arResult['TYPES'][$arItem['~UF_TYPE']]['XML_ID']?>"><?=$arItem['UF_NAME']?></a>
                            <?else:?>
                                <p class="nx-svg-map-tooltip-item tooltip-<?=$arResult['TYPES'][$arItem['~UF_TYPE']]['XML_ID']?>"><?=$arItem['UF_NAME']?></p>
                            <?endif;?>
                        <?endforeach?>
                    </div>
                </div>
            <?endforeach?>
        </div>
    </div>
</section>