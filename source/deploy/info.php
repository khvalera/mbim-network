<?php

/////////////////////////////////////////////////////////////////////////////
// General information
/////////////////////////////////////////////////////////////////////////////
$app['basename'] = 'network_mbim';
$app['version'] = '1.0.0';
$app['release'] = '1';
$app['vendor'] = 'khvalera';
$app['packager'] = 'khvalera';
$app['license'] = 'GPLv3';
$app['license_core'] = 'GPLv3';
$app['description'] = lang('network_mbim_app_description');

/////////////////////////////////////////////////////////////////////////////
// App name and categories
/////////////////////////////////////////////////////////////////////////////
$app['name'] = lang('network_mbim_app_name');
$app['category'] = lang('base_category_network');
$app['subcategory'] = lang('base_subcategory_settings');

/////////////////////////////////////////////////////////////////////////////
// Controllers
/////////////////////////////////////////////////////////////////////////////
$app['controllers']['settings']['title'] = lang('base_settings');

/////////////////////////////////////////////////////////////////////////////
// Packaging
/////////////////////////////////////////////////////////////////////////////
$app['requires'] = array(
    'app-network',
);

$app['core_requires'] = array(
    'app-network-core >= 1:2.4.2',
    'libmbim-utils >= 1.14.2',
);

$app['core_directory_manifest'] = array(
    '/var/clearos/network_mbim' => array(),
);
