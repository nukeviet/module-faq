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

$groups_list = nv_groups_list();

$array = [];
$error = '';

//them chu de
if ($nv_Request->isset_request('add', 'get')) {
    $page_title = $lang_module['faq_addcat_titlebox'];

    $is_error = false;

    if ($nv_Request->isset_request('submit', 'post')) {
        $array['parentid'] = $nv_Request->get_int('parentid', 'post', 0);
        $array['title'] = $nv_Request->get_title('title', 'post', '', 1);
        $array['description'] = $nv_Request->get_title('description', 'post', '');
        $array['keywords'] = $nv_Request->get_title('keywords', 'post', '');
        $_groups_post = $nv_Request->get_array('groups_view', 'post', []);
        $array['groups_view'] = !empty($_groups_post) ? implode(',', nv_groups_post(array_intersect($_groups_post, array_keys($groups_list)))) : '';

        $array['alias'] = change_alias($array['title']);

        if (empty($array['title'])) {
            $error = $lang_module['faq_error_cat2'];
            $is_error = true;
        } else {
            if (!empty($array['parentid'])) {
                $sql = 'SELECT COUNT(*) AS count FROM ' . NV_PREFIXLANG . '_' . $module_data . '_categories WHERE id=' . $array['parentid'];
                $result = $db->query($sql);
                $count = $result->fetchColumn();

                if (!$count) {
                    $error = $lang_module['faq_error_cat3'];
                    $is_error = true;
                }
            }

            if (!$is_error) {
                $sql = 'SELECT COUNT(*) AS count FROM ' . NV_PREFIXLANG . '_' . $module_data . '_categories WHERE alias=' . $db->quote($array['alias']);
                $result = $db->query($sql);
                $count = $result->fetchColumn();

                if ($count) {
                    $error = $lang_module['faq_error_cat1'];
                    $is_error = true;
                }
            }
        }

        if (!$is_error) {
            $weight = $db->query('SELECT max(weight) FROM ' . NV_PREFIXLANG . '_' . $module_data . '_categories WHERE parentid=' . (int) ($array['parentid']) . '')->fetchColumn();
            $weight = (int) $weight + 1;

            $stmt = $db->prepare('INSERT INTO ' . NV_PREFIXLANG . '_' . $module_data . '_categories SET
				parentid =' . (int) ($array['parentid']) . ', 
				weight =' . (int) $weight . ', 
				status =1, 
				title =:title, 
				alias =:alias, 
				description =:description, 
				keywords =:keywords,
				groups_view=:groups_view');
            $stmt->bindParam(':title', $array['title'], PDO::PARAM_STR);
            $stmt->bindParam(':alias', $array['alias'], PDO::PARAM_STR);
            $stmt->bindParam(':description', $array['description'], PDO::PARAM_STR);
            $stmt->bindParam(':keywords', $array['keywords'], PDO::PARAM_STR);
            $stmt->bindParam(':groups_view', $array['groups_view'], PDO::PARAM_STR);
            $stmt->execute();
            if (!$catid = $db->lastInsertId()) {
                $error = $lang_module['faq_error_cat4'];
                $is_error = true;
            } else {
                $nv_Cache->delMod($module_name);
                nv_insert_logs(NV_LANG_DATA, $module_name, 'log_add_cat', 'cat ' . $catid, $admin_info['userid']);
                header('Location: ' . NV_BASE_ADMINURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&' . NV_NAME_VARIABLE . '=' . $module_name . '&' . NV_OP_VARIABLE . '=cat');
                exit();
            }
        }
    } else {
        $array['parentid'] = 0;
        $array['title'] = '';
        $array['alias'] = '';
        $array['description'] = '';
        $array['groups_view'] = 6;
    }

    $listcats = [
        [
            'id' => 0,
            'name' => $lang_module['faq_category_cat_maincat'],
            'selected' => ''
        ]
    ];
    $listcats = $listcats + nv_listcats($array['parentid']);

    $groups_view = array_map('intval', explode(',', $array['groups_view']));

    $groups_views = [];
    foreach ($groups_list as $group_id => $grtl) {
        $groups_views[] = [
            'value' => $group_id,
            'checked' => in_array((int) $group_id, $groups_view, true) ? ' checked="checked"' : '',
            'title' => $grtl
        ];
    }

    $xtpl = new XTemplate('cat_add.tpl', NV_ROOTDIR . '/themes/' . $global_config['module_theme'] . '/modules/' . $module_file);
    $xtpl->assign('FORM_ACTION', NV_BASE_ADMINURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module_name . '&amp;' . NV_OP_VARIABLE . '=' . $op . '&amp;add=1');
    $xtpl->assign('LANG', $lang_module);
    $xtpl->assign('GLANG', $lang_global);
    $xtpl->assign('DATA', $array);

    if (!empty($error)) {
        $xtpl->assign('ERROR', $error);
        $xtpl->parse('main.error');
    }

    foreach ($listcats as $cat) {
        $xtpl->assign('LISTCATS', $cat);
        $xtpl->parse('main.parentid');
    }

    foreach ($groups_views as $data) {
        $xtpl->assign('groups_views', $data);
        $xtpl->parse('main.groups_views');
    }

    $xtpl->parse('main');
    $contents = $xtpl->text('main');

    include NV_ROOTDIR . '/includes/header.php';
    echo nv_admin_theme($contents);
    include NV_ROOTDIR . '/includes/footer.php';

    exit();
}

//Sua chu de
if ($nv_Request->isset_request('edit', 'get')) {
    $page_title = $lang_module['faq_editcat_cat'];

    $catid = $nv_Request->get_int('catid', 'get', 0);

    if (empty($catid)) {
        header('Location: ' . NV_BASE_ADMINURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&' . NV_NAME_VARIABLE . '=' . $module_name . '&' . NV_OP_VARIABLE . '=cat');
        exit();
    }

    $sql = 'SELECT * FROM ' . NV_PREFIXLANG . '_' . $module_data . '_categories WHERE id=' . $catid;
    $result = $db->query($sql);
    $numcat = $result->rowCount();

    if ($numcat != 1) {
        header('Location: ' . NV_BASE_ADMINURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&' . NV_NAME_VARIABLE . '=' . $module_name . '&' . NV_OP_VARIABLE . '=cat');
        exit();
    }

    $row = $result->fetch();

    $is_error = false;

    if ($nv_Request->isset_request('submit', 'post')) {
        $array['parentid'] = $nv_Request->get_int('parentid', 'post', 0);
        $array['title'] = $nv_Request->get_title('title', 'post', '', 1);
        $array['description'] = $nv_Request->get_title('description', 'post', '');
        $array['keywords'] = $nv_Request->get_title('keywords', 'post', '');
        $_groups_post = $nv_Request->get_array('groups_view', 'post', []);
        $array['groups_view'] = !empty($_groups_post) ? implode(',', nv_groups_post(array_intersect($_groups_post, array_keys($groups_list)))) : '';

        $array['alias'] = change_alias($array['title']);

        if (empty($array['title'])) {
            $error = $lang_module['faq_error_cat2'];
            $is_error = true;
        } else {
            if (!empty($array['parentid'])) {
                $sql = 'SELECT COUNT(*) AS count FROM ' . NV_PREFIXLANG . '_' . $module_data . '_categories WHERE id=' . $array['parentid'];
                $result = $db->query($sql);
                $count = $result->fetchColumn();

                if (!$count) {
                    $error = $lang_module['faq_error_cat3'];
                    $is_error = true;
                }
            }

            if (!$is_error) {
                $sql = 'SELECT COUNT(*) AS count FROM ' . NV_PREFIXLANG . '_' . $module_data . '_categories WHERE id!=' . $catid . ' AND alias=' . $db->quote($array['alias']) . ' AND parentid=' . $array['parentid'];
                $result = $db->query($sql);
                $count = $result->fetchColumn();

                if ($count) {
                    $error = $lang_module['faq_error_cat1'];
                    $is_error = true;
                }
            }
        }

        if (!$is_error) {
            if ($array['parentid'] != $row['parentid']) {
                $new_weight = $db->query('SELECT MAX(weight) FROM ' . NV_PREFIXLANG . '_' . $module_data . '_categories WHERE parentid=' . $array['parentid'])->fetchColumn();
                $new_weight = (int) $new_weight;
                ++$new_weight;
            } else {
                $new_weight = $row['weight'];
            }

            $stmt = $db->prepare('UPDATE ' . NV_PREFIXLANG . '_' . $module_data . '_categories SET
				parentid =' . (int) ($array['parentid']) . ', 
				weight =' . (int) $new_weight . ', 
				title =:title, 
				alias =:alias, 
				description =:description, 
				keywords =:keywords, 
				groups_view=:groups_view WHERE id=' . $catid);
            $stmt->bindParam(':title', $array['title'], PDO::PARAM_STR);
            $stmt->bindParam(':alias', $array['alias'], PDO::PARAM_STR);
            $stmt->bindParam(':description', $array['description'], PDO::PARAM_STR);
            $stmt->bindParam(':keywords', $array['keywords'], PDO::PARAM_STR);
            $stmt->bindParam(':groups_view', $array['groups_view'], PDO::PARAM_STR);

            if (!$stmt->execute()) {
                $error = $lang_module['faq_error_cat5'];
                $is_error = true;
            } else {
                if ($array['parentid'] != $row['parentid']) {
                    nv_FixWeightCat($row['parentid']);
                }
                $nv_Cache->delMod($module_name);
                header('Location: ' . NV_BASE_ADMINURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&' . NV_NAME_VARIABLE . '=' . $module_name . '&' . NV_OP_VARIABLE . '=cat');
                exit();
            }
        }
    } else {
        $array['parentid'] = (int) $row['parentid'];
        $array['title'] = $row['title'];
        $array['description'] = $row['description'];
        $array['keywords'] = $row['keywords'];
        $array['groups_view'] = $row['groups_view'];
    }

    $listcats = [
        [
            'id' => 0,
            'name' => $lang_module['faq_category_cat_maincat'],
            'selected' => ''
        ]
    ];
    $listcats = $listcats + nv_listcats($array['parentid'], $catid);

    $groups_view = array_map('intval', explode(',', $array['groups_view']));
    $groups_views = [];
    foreach ($groups_list as $group_id => $grtl) {
        $groups_views[] = [
            'value' => $group_id,
            'checked' => in_array((int) $group_id, $groups_view, true) ? ' checked="checked"' : '',
            'title' => $grtl
        ];
    }

    $xtpl = new XTemplate('cat_add.tpl', NV_ROOTDIR . '/themes/' . $global_config['module_theme'] . '/modules/' . $module_file);
    $xtpl->assign('FORM_ACTION', NV_BASE_ADMINURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module_name . '&amp;' . NV_OP_VARIABLE . '=' . $op . '&amp;edit=1&amp;catid=' . $catid);
    $xtpl->assign('LANG', $lang_module);
    $xtpl->assign('GLANG', $lang_global);
    $xtpl->assign('DATA', $array);

    if (!empty($error)) {
        $xtpl->assign('ERROR', $error);
        $xtpl->parse('main.error');
    }

    foreach ($listcats as $cat) {
        $xtpl->assign('LISTCATS', $cat);
        $xtpl->parse('main.parentid');
    }

    foreach ($groups_views as $data) {
        $xtpl->assign('groups_views', $data);
        $xtpl->parse('main.groups_views');
    }

    $xtpl->parse('main');
    $contents = $xtpl->text('main');

    include NV_ROOTDIR . '/includes/header.php';
    echo nv_admin_theme($contents);
    include NV_ROOTDIR . '/includes/footer.php';

    exit();
}

//Xoa chu de
if ($nv_Request->isset_request('del', 'post')) {
    if (!defined('NV_IS_AJAX')) {
        exit('Wrong URL');
    }

    $catid = $nv_Request->get_int('catid', 'post', 0);

    if (empty($catid)) {
        exit('NO_' . $lang_module['faq_cat_notfound']);
    }

    $sql = 'SELECT COUNT(*) AS count, parentid FROM ' . NV_PREFIXLANG . '_' . $module_data . '_categories WHERE id=' . $catid;
    $result = $db->query($sql);
    list($count, $parentid) = $result->fetch(3);

    if ($count != 1) {
        exit('NO_' . $lang_module['faq_cat_notfound']);
    }

    $check_exists = $db->query('SELECT COUNT(*) FROM ' . NV_PREFIXLANG . '_' . $module_data . '_categories WHERE parentid = ' . $catid)->fetchColumn();
    if (!empty($check_exists)) {
        exit('NO_' . sprintf($lang_module['delcat_msg_cat'], $check_exists));
    }

    $check_exists = $db->query('SELECT COUNT(*) FROM ' . NV_PREFIXLANG . '_' . $module_data . ' WHERE catid = ' . $catid)->fetchColumn();
    if (!empty($check_exists)) {
        exit('NO_' . sprintf($lang_module['delcat_msg_rows'], $check_exists));
    }

    $sql = 'DELETE FROM ' . NV_PREFIXLANG . '_' . $module_data . '_categories WHERE id=' . $catid;
    $db->query($sql);

    nv_FixWeightCat($parentid);
    $nv_Cache->delMod($module_name);

    exit('OK');
}

//Chinh thu tu chu de
if ($nv_Request->isset_request('changeweight', 'post')) {
    if (!defined('NV_IS_AJAX')) {
        exit('Wrong URL');
    }

    $catid = $nv_Request->get_int('catid', 'post', 0);
    $new = $nv_Request->get_int('new', 'post', 0);

    if (empty($catid)) {
        exit('NO');
    }

    $query = 'SELECT parentid FROM ' . NV_PREFIXLANG . '_' . $module_data . '_categories WHERE id=' . $catid;
    $result = $db->query($query);
    $numrows = $result->rowCount();
    if ($numrows != 1) {
        exit('NO');
    }
    $parentid = $result->fetchColumn();

    $query = 'SELECT id FROM ' . NV_PREFIXLANG . '_' . $module_data . '_categories WHERE id!=' . $catid . ' AND parentid=' . $parentid . ' ORDER BY weight ASC';
    $result = $db->query($query);
    $weight = 0;
    while ($row = $result->fetch()) {
        ++$weight;
        if ($weight == $new) {
            ++$weight;
        }
        $sql = 'UPDATE ' . NV_PREFIXLANG . '_' . $module_data . '_categories SET weight=' . $weight . ' WHERE id=' . $row['id'];
        $db->query($sql);
    }
    $sql = 'UPDATE ' . NV_PREFIXLANG . '_' . $module_data . '_categories SET weight=' . $new . ' WHERE id=' . $catid;
    $db->query($sql);
    $nv_Cache->delMod($module_name);
    exit('OK');
}

//Kich hoat - dinh chi
if ($nv_Request->isset_request('changestatus', 'post')) {
    if (!defined('NV_IS_AJAX')) {
        exit('Wrong URL');
    }

    $catid = $nv_Request->get_int('catid', 'post', 0);

    if (empty($catid)) {
        exit('NO');
    }

    $query = 'SELECT status FROM ' . NV_PREFIXLANG . '_' . $module_data . '_categories WHERE id=' . $catid;
    $result = $db->query($query);
    $numrows = $result->rowCount();
    if ($numrows != 1) {
        exit('NO');
    }

    $status = $result->fetchColumn();
    $status = $status ? 0 : 1;

    $sql = 'UPDATE ' . NV_PREFIXLANG . '_' . $module_data . '_categories SET status=' . $status . ' WHERE id=' . $catid;
    $db->query($sql);
    $nv_Cache->delMod($module_name);
    exit('OK');
}

//Danh sach chu de
$page_title = $lang_module['faq_catmanager'];

$pid = $nv_Request->get_int('pid', 'get', 0);

$sql = 'SELECT * FROM ' . NV_PREFIXLANG . '_' . $module_data . '_categories WHERE parentid=' . $pid . ' ORDER BY weight ASC';
$result = $db->query($sql);
$num = $result->rowCount();

if (!$num) {
    if ($pid) {
        header('Location: ' . NV_BASE_ADMINURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&' . NV_NAME_VARIABLE . '=' . $module_name . '&' . NV_OP_VARIABLE . '=cat');
    } else {
        header('Location: ' . NV_BASE_ADMINURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&' . NV_NAME_VARIABLE . '=' . $module_name . '&' . NV_OP_VARIABLE . '=cat&add=1');
    }
    exit();
}

if ($pid) {
    $sql2 = 'SELECT title,parentid FROM ' . NV_PREFIXLANG . '_' . $module_data . '_categories WHERE id=' . $pid;
    $result2 = $db->query($sql2);
    list($parentid, $parentid2) = $result2->fetch(3);
    $caption = sprintf($lang_module['faq_table_caption2'], $parentid);
    $parentid = '<a href="' . NV_BASE_ADMINURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module_name . '&amp;' . NV_OP_VARIABLE . '=cat&amp;pid=' . $parentid2 . '">' . $parentid . '</a>';
} else {
    $caption = $lang_module['faq_table_caption1'];
    $parentid = $lang_module['faq_category_cat_maincat'];
}

$list = [];
$a = 0;

while ($row = $result->fetch()) {
    $numsub = $db->query('SELECT id FROM ' . NV_PREFIXLANG . '_' . $module_data . '_categories WHERE parentid=' . $row['id'])->rowCount();
    if ($numsub) {
        $numsub = ' (<a href="' . NV_BASE_ADMINURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module_name . '&amp;' . NV_OP_VARIABLE . '=cat&amp;pid=' . $row['id'] . '">' . $numsub . ' ' . $lang_module['faq_category_cat_sub'] . '</a>)';
    } else {
        $numsub = '';
    }

    $weight = [];
    for ($i = 1; $i <= $num; ++$i) {
        $weight[$i]['title'] = $i;
        $weight[$i]['pos'] = $i;
        $weight[$i]['selected'] = ($i == $row['weight']) ? ' selected="selected"' : '';
    }

    $class = ($a % 2) ? ' class="second"' : '';

    $list[$row['id']] = [
        'id' => (int) $row['id'],
        'title' => $row['title'],
        'titlelink' => NV_BASE_ADMINURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module_name . '&amp;catid=' . $row['id'],
        'numsub' => $numsub,
        'parentid' => $parentid,
        'weight' => $weight,
        'status' => $row['status'] ? ' checked="checked"' : '',
        'class' => $class
    ];

    ++$a;
}

$xtpl = new XTemplate('cat_list.tpl', NV_ROOTDIR . '/themes/' . $global_config['module_theme'] . '/modules/' . $module_file);
$xtpl->assign('ADD_NEW_CAT', NV_BASE_ADMINURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module_name . '&amp;' . NV_OP_VARIABLE . '=cat&amp;add=1');
$xtpl->assign('TABLE_CAPTION', $caption);
$xtpl->assign('GLANG', $lang_global);
$xtpl->assign('LANG', $lang_module);

foreach ($list as $row) {
    $xtpl->assign('ROW', $row);

    foreach ($row['weight'] as $weight) {
        $xtpl->assign('WEIGHT', $weight);
        $xtpl->parse('main.row.weight');
    }

    $xtpl->assign('EDIT_URL', NV_BASE_ADMINURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module_name . '&amp;' . NV_OP_VARIABLE . '=cat&amp;edit=1&amp;catid=' . $row['id']);
    $xtpl->parse('main.row');
}

$xtpl->parse('main');
$contents = $xtpl->text('main');

include NV_ROOTDIR . '/includes/header.php';
echo nv_admin_theme($contents);
include NV_ROOTDIR . '/includes/footer.php';
