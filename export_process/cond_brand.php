<?php

$BRN_CODE = substr($row['BRN_CODE'],0,2);

$BRN_NAME = "";

switch ($BRN_CODE) {
    case "AP":
        $IMG = "product-ap.png";
        $BRN_NAME = "Apollo";
        break;
    case "AT":
        $IMG = "product-atl.png";
        $BRN_NAME = "Atlas";
        break;
    case "BS":
        $IMG = "product-bs.png";
        $BRN_NAME = "Bridgestone";
        break;
    case "DS":
        $IMG = "product-ds.png";
        $BRN_NAME = "Deestone";
        break;
    case "DL":
        $IMG = "product-dl.png";
        $BRN_NAME = "Dunlop";
        break;
    case "DT":
        $IMG = "product-dt.png";
        $BRN_NAME = "Dayton";
        break;
    case "FS":
        $IMG = "product-fs.png";
        $BRN_NAME = "Firestone";
        break;
    case "HK":
        $IMG = "product-hk.png";
        $BRN_NAME = "Hankook";
        break;
    case "LL":
        $IMG = "product-llit.png";
        $BRN_NAME = "Linglong";
        break;
    case "LE":
        $IMG = "product-leao.png";
        $BRN_NAME = "LEAO";
        break;
    case "ML":
        $IMG = "product-ml.png";
        $BRN_NAME = "Michelin";
        break;
    case "PL":
        $IMG = "product-plli.png";
        $BRN_NAME = "Pirelli";
        break;
    case "VB":
        $IMG = "product-vrb.png";
        $BRN_NAME = "Vee Rubber";
        break;
    case "YK":
        $IMG = "product-yk.png";
        $BRN_NAME = "Yokohama";
        break;
    default:
        $IMG = "product.png";
        $BRN_NAME = "Other Brand";
        break;
}



