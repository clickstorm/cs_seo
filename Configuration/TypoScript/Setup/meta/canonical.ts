page {
  headerData.654 = COA
  headerData.654 {
    ### canonical ###
    10 =< lib.currentUrl
    10 {
      wrap = <link rel="canonical" href="|" />
      if.isFalse.field = tx_csseo_no_index
    }
  }
}
