<?php

if (!defined('POSTCODE_TMP_DIR')) {
    define('POSTCODE_TMP_DIR', dirname(__FILE__));
}

if (!defined('POSTCODE_ONS_URL')) {
    define('POSTCODE_ONS_URL', 'https://geoportal.statistics.gov.uk/Docs/PostCodes/');
}

if (!defined('POSTCODE_ONS_PREFIX')) {
    define('POSTCODE_ONS_PREFIX', 'ONSPD_AUG_2015');
}

if (!defined('POSTCODE_ONS_SUFFIX')) {
    define('POSTCODE_ONS_SUFFIX', '_csv.zip');
}

if (!defined('POSTCODE_ONS_FILE')) {
    define('POSTCODE_ONS_FILE', POSTCODE_ONS_PREFIX . POSTCODE_ONS_SUFFIX);
}

if (!defined('POSTCODE_ONS_FILE_SHA1')) {
    define('POSTCODE_ONS_FILE_SHA1', '9844b9eb4d649ebff4272cd16d7a63b489f45557');
}
