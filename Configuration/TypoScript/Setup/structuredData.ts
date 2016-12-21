### SiteSearch ###
[userFunc = Clickstorm\CsSeo\UserFunc\StructuredData::siteSearch()]
    page.footerData.655 = USER
    page.footerData.655.userFunc = Clickstorm\CsSeo\UserFunc\StructuredData->getSiteSearch
    page.footerData.655.userFunc {
        pid < plugin.tx_csseo.structureddata.search.pid
        searchterm < plugin.tx_csseo.structureddata.search.searchtermkey
    }
[end]

### Breadcrumb ###
[userFunc = Clickstorm\CsSeo\UserFunc\StructuredData::breadcrumb()]
    page.footerData.656 = USER
    page.footerData.656.userFunc = Clickstorm\CsSeo\UserFunc\StructuredData->getBreadcrumb
[end]