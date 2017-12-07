page.jsInline.654 = COA

<INCLUDE_TYPOSCRIPT: source="DIR:EXT:cs_seo/Configuration/TypoScript/Setup/Tracking/" extensions="ts">

### Disable Tracking if Backend User detected ###
[globalVar = TSFE:beUserLogin > 0]
	page.jsInline.654 >
	page.bodyTagCObject.654 >
	page.includeJSFooter.654 >
[end]