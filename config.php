<?php

if (!defined('POSTCODE_TMP_DIR')) {
    define('POSTCODE_TMP_DIR', dirname(__FILE__));
}

if (!defined('POSTCODE_ONS_URL')) {
    define('POSTCODE_ONS_URL', 'https://geoportal.statistics.gov.uk/Docs/PostCodes/');
}

if (!defined('POSTCODE_ONS_PREFIX')) {
    define('POSTCODE_ONS_PREFIX', 'ONSPD_FEB_2015');
}

if (!defined('POSTCODE_ONS_FILE')) {
    define('POSTCODE_ONS_FILE', POSTCODE_ONS_PREFIX.'_multi_csv.zip');
}

if (!defined('POSTCODE_ONS_FILE_SHA1')) {
    define('POSTCODE_ONS_FILE_SHA1', '879589c0b8e83650e8708ffce6137a473b0c122c');
}
