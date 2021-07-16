<?php

/**
 * NukeViet Content Management System
 * @version 4.x
 * @author VINADES.,JSC <contact@vinades.vn>
 * @copyright (C) 2009-2021 VINADES.,JSC. All rights reserved
 * @license GNU/GPL version 2 or any later version
 * @see https://github.com/nukeviet The NukeViet CMS GitHub project
 */

if (!defined('NV_IS_FILE_ADMIN')) {
    exit('Stop!!!');
}

$page_title = $lang_module['config'];

$array_config = [];

if ($nv_Request->isset_request('submit', 'post')) {
    $array_config['per_page'] = $nv_Request->get_int('per_page', 'post', 20);
    $array_config['per_cat'] = $nv_Request->get_int('per_cat', 'post', 5);

    foreach ($array_config as $config_name => $config_value) {
        $query = 'REPLACE INTO ' . NV_PREFIXLANG . '_' . $module_data . '_config VALUES (' . $db->quote($config_name) . ',' . $db->quote($config_value) . ')';
        $db->query($query);
    }

    $nv_Cache->delMod($module_name);

    header('Location: ' . NV_BASE_ADMINURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&' . NV_NAME_VARIABLE . '=' . $module_name . '&' . NV_OP_VARIABLE . '=' . $op);
    exit();
}

$sql = 'SELECT config_name, config_value FROM ' . NV_PREFIXLANG . '_' . $module_data . '_config';
$result = $db->query($sql);
while (list($c_config_name, $c_config_value) = $result->fetch(3)) {
    $array_config[$c_config_name] = $c_config_value;
}

$xtpl = new XTemplate('config.tpl', NV_ROOTDIR . '/themes/' . $global_config['module_theme'] . '/modules/' . $module_file);
$xtpl->assign('FORM_ACTION', NV_BASE_ADMINURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module_name . '&amp;' . NV_OP_VARIABLE . '=' . $op);
$xtpl->assign('LANG', $lang_module);
$xtpl->assign('DATA', $array_config);

for ($i = 2; $i <= 20; ++$i) {
    $y = $i * 5;
    $xtpl->assign('PP_OPTION', [
        'num' => $y,
        'sel' => $y == $array_config['per_page'] ? ' selected="selected"' : ''
    ]);
    $xtpl->parse('main.per_page');
}

for ($i = 2; $i <= 10; ++$i) {
    $xtpl->assign('PC_OPTION', [
        'num' => $i,
        'sel' => $i == $array_config['per_cat'] ? ' selected="selected"' : ''
    ]);
    $xtpl->parse('main.per_cat');
}

$xtpl->parse('main');
$contents = $xtpl->text('main');

include NV_ROOTDIR . '/includes/header.php';
echo nv_admin_theme($contents);
include NV_ROOTDIR . '/includes/footer.php';
