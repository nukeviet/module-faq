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

//Add, edit file
if ($nv_Request->isset_request('add', 'get') or $nv_Request->isset_request('edit', 'get')) {
    if ($nv_Request->isset_request('edit', 'get')) {
        $id = $nv_Request->get_int('id', 'get', 0);
        if (empty($id)) {
            header('Location: ' . NV_BASE_ADMINURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&' . NV_NAME_VARIABLE . '=' . $module_name);
            exit();
        }

        $query = 'SELECT * FROM ' . NV_PREFIXLANG . '_' . $module_data . ' WHERE id=' . $id;
        $result = $db->query($query);
        $numrows = $result->rowCount();
        if ($numrows != 1) {
            header('Location: ' . NV_BASE_ADMINURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&' . NV_NAME_VARIABLE . '=' . $module_name);
            exit();
        }

        define('IS_EDIT', true);
        $page_title = $nv_Lang->getModule('faq_editfaq');

        $row = $result->fetch();
    } else {
        define('IS_ADD', true);
        $page_title = $nv_Lang->getModule('faq_addfaq');
    }

    $array = [];
    $is_error = false;
    $error = '';

    if ($nv_Request->isset_request('submit', 'post')) {
        $array['catid'] = $nv_Request->get_int('catid', 'post', 0);
        $array['title'] = $nv_Request->get_title('title', 'post', '', 1);
        $array['question'] = $nv_Request->get_textarea('question', '', NV_ALLOWED_HTML_TAGS);
        $array['answer'] = $nv_Request->get_editor('answer', '', NV_ALLOWED_HTML_TAGS);

        $alias = change_alias($array['title']);
        $sql = 'SELECT COUNT(*) FROM ' . NV_PREFIXLANG . '_' . $module_data . ' WHERE alias=' . $db->quote($alias);
        defined('IS_EDIT') && $sql .= ' AND id!=' . $id;

        if (empty($array['title'])) {
            $is_error = true;
            $error = $nv_Lang->getModule('faq_error_title');
        } elseif ($db->query($sql)->fetchColumn()) {
            $is_error = true;
            $error = $nv_Lang->getModule('faq_title_exists');
        } elseif (empty($array['question'])) {
            $is_error = true;
            $error = $nv_Lang->getModule('faq_error_question');
        } elseif (empty($array['answer'])) {
            $is_error = true;
            $error = $nv_Lang->getModule('faq_error_answer');
        } else {
            $array['question'] = nv_nl2br($array['question'], '<br />');
            $array['answer'] = nv_editor_nl2br($array['answer']);

            if (defined('IS_EDIT')) {
                if ($array['catid'] != $row['catid']) {
                    $new_weight = $db->query('SELECT MAX(weight) AS new_weight FROM ' . NV_PREFIXLANG . '_' . $module_data . ' WHERE catid=' . $array['catid'])->fetchColumn();
                    $new_weight = (int) $new_weight;
                    ++$new_weight;
                } else {
                    $new_weight = $row['weight'];
                }

                $sql = 'UPDATE ' . NV_PREFIXLANG . '_' . $module_data . ' SET 
                catid=' . $array['catid'] . ', 
                title=' . $db->quote($array['title']) . ', 
                alias=' . $db->quote($alias) . ', 
                question=' . $db->quote($array['question']) . ', 
                answer=' . $db->quote($array['answer']) . ', 
                weight=' . $new_weight . ' 
                WHERE id=' . $id;
                $result = $db->query($sql);

                if (!$result) {
                    $is_error = true;
                    $error = $nv_Lang->getModule('faq_error_notResult');
                } else {
                    $array['catid'] && nv_update_keywords($array['catid']);

                    if ($array['catid'] != $row['catid']) {
                        nv_FixWeight($row['catid']);
                        nv_update_keywords($row['catid']);
                    }

                    header('Location: ' . NV_BASE_ADMINURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&' . NV_NAME_VARIABLE . '=' . $module_name);
                    exit();
                }
            } elseif (defined('IS_ADD')) {
                $new_weight = $db->query('SELECT MAX(weight) AS new_weight FROM ' . NV_PREFIXLANG . '_' . $module_data . ' WHERE catid=' . $array['catid'])->fetchColumn();
                $new_weight = (int) $new_weight;
                ++$new_weight;

                $sql = 'INSERT INTO ' . NV_PREFIXLANG . '_' . $module_data . ' VALUES (
                NULL, 
                ' . $array['catid'] . ', 
                ' . $db->quote($array['title']) . ', 
                ' . $db->quote($alias) . ', 
                ' . $db->quote($array['question']) . ', 
                ' . $db->quote($array['answer']) . ', 
                ' . $new_weight . ', 
                1, ' . NV_CURRENTTIME . ')';

                if (!$db->insert_id($sql)) {
                    $is_error = true;
                    $error = $nv_Lang->getModule('faq_error_notResult2');
                } else {
                    $array['catid'] && nv_update_keywords($array['catid']);

                    header('Location: ' . NV_BASE_ADMINURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&' . NV_NAME_VARIABLE . '=' . $module_name);
                    exit();
                }
            }
        }
    } else {
        if (defined('IS_EDIT')) {
            $array['catid'] = (int) $row['catid'];
            $array['title'] = $row['title'];
            $array['answer'] = nv_editor_br2nl($row['answer']);
            $array['question'] = nv_br2nl($row['question']);
        } else {
            $array['catid'] = 0;
            $array['title'] = $array['answer'] = $array['question'] = '';
        }
    }

    if (!empty($array['answer'])) {
        $array['answer'] = nv_htmlspecialchars($array['answer']);
    }
    if (!empty($array['question'])) {
        $array['question'] = nv_htmlspecialchars($array['question']);
    }

    $listcats = [];
    $listcats[0] = [
        'id' => 0,
        'name' => $nv_Lang->getModule('nocat'),
        'selected' => $array['catid'] == 0 ? ' selected="selected"' : ''
    ];
    $listcats = $listcats + nv_listcats($array['catid']);
    if (empty($listcats)) {
        header('Location: ' . NV_BASE_ADMINURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&' . NV_NAME_VARIABLE . '=' . $module_name . '&' . NV_OP_VARIABLE . '=cat&add=1');
        exit();
    }

    if (defined('NV_EDITOR')) {
        require_once NV_ROOTDIR . '/' . NV_EDITORSDIR . '/' . NV_EDITOR . '/nv.php';
    }

    if (defined('NV_EDITOR') and nv_function_exists('nv_aleditor')) {
        $array['answer'] = nv_aleditor('answer', '100%', '300px', $array['answer']);
    } else {
        $array['answer'] = '<textarea style="width:100%; height:300px" name="answer" id="answer">' . $array['answer'] . '</textarea>';
    }

    $xtpl = new XTemplate('content.tpl', NV_ROOTDIR . '/themes/' . $global_config['module_theme'] . '/modules/' . $module_file);

    if (defined('IS_EDIT')) {
        $xtpl->assign('FORM_ACTION', NV_BASE_ADMINURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module_name . '&amp;edit=1&amp;id=' . $id);
    } else {
        $xtpl->assign('FORM_ACTION', NV_BASE_ADMINURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module_name . '&amp;add=1');
    }

    $xtpl->assign('LANG', \NukeViet\Core\Language::$lang_module);
    $xtpl->assign('DATA', $array);

    if (!empty($error)) {
        $xtpl->assign('ERROR', $error);
        $xtpl->parse('main.error');
    }

    foreach ($listcats as $cat) {
        $xtpl->assign('LISTCATS', $cat);
        $xtpl->parse('main.catid');
    }

    $xtpl->parse('main');
    $contents = $xtpl->text('main');

    include NV_ROOTDIR . '/includes/header.php';
    echo nv_admin_theme($contents);
    include NV_ROOTDIR . '/includes/footer.php';
    exit();
}

//change weight
if ($nv_Request->isset_request('changeweight', 'post')) {
    if (!defined('NV_IS_AJAX')) {
        exit('Wrong URL');
    }

    $id = $nv_Request->get_int('id', 'post', 0);
    $new = $nv_Request->get_int('new', 'post', 0);

    if (empty($id)) {
        exit('NO');
    }

    $query = 'SELECT catid FROM ' . NV_PREFIXLANG . '_' . $module_data . ' WHERE id=' . $id;
    $result = $db->query($query);
    $numrows = $result->rowCount();
    if ($numrows != 1) {
        exit('NO');
    }
    $catid = $result->fetchColumn();
    $query = 'SELECT id FROM ' . NV_PREFIXLANG . '_' . $module_data . ' WHERE catid=' . $catid . ' ORDER BY weight ASC';
    $result = $db->query($query);
    $weight = 0;
    $rs = [];
    $in = [];
    while ($row = $result->fetch()) {
        if ($row['id'] == $id) {
            $rs[] = 'WHEN id = ' . $row['id'] . ' THEN ' . $new;
        } else {
            ++$weight;
            if ($weight == $new) {
                ++$weight;
            }
            $rs[] = 'WHEN id = ' . $row['id'] . ' THEN ' . $weight;
        }

        $in[] = $row['id'];
    }
    if (!empty($rs)) {
        $rs = '(CASE ' . implode(' ', $rs) . ' END)';
        $in = implode(',', $in);
        $db->query('UPDATE ' . NV_PREFIXLANG . '_' . $module_data . ' SET weight = ' . $rs . ' WHERE id IN (' . $in . ')');
    }

    exit('OK');
}

//Kich hoat - dinh chi
if ($nv_Request->isset_request('changestatus', 'post')) {
    if (!defined('NV_IS_AJAX')) {
        exit('Wrong URL');
    }

    $id = $nv_Request->get_int('id', 'post', 0);

    if (empty($id)) {
        exit('NO');
    }

    $query = 'SELECT catid, status FROM ' . NV_PREFIXLANG . '_' . $module_data . ' WHERE id=' . $id;
    $result = $db->query($query);
    $numrows = $result->rowCount();
    if ($numrows != 1) {
        exit('NO');
    }

    list($catid, $status) = $result->fetch(3);
    $status = $status ? 0 : 1;

    $sql = 'UPDATE ' . NV_PREFIXLANG . '_' . $module_data . ' SET status=' . $status . ' WHERE id=' . $id;
    $db->query($sql);

    $catid && nv_update_keywords($catid);

    exit('OK');
}

//Xoa
if ($nv_Request->isset_request('del', 'post')) {
    if (!defined('NV_IS_AJAX')) {
        exit('Wrong URL');
    }

    $id = $nv_Request->get_int('id', 'post', 0);

    if (empty($id)) {
        exit('NO');
    }

    $sql = 'SELECT COUNT(*) AS count, catid FROM ' . NV_PREFIXLANG . '_' . $module_data . ' WHERE id=' . $id;
    $result = $db->query($sql);
    list($count, $catid) = $result->fetch(3);

    if ($count != 1) {
        exit('NO');
    }

    $sql = 'DELETE FROM ' . NV_PREFIXLANG . '_' . $module_data . ' WHERE id=' . $id;
    $db->query($sql);

    nv_FixWeight($catid);
    $catid && nv_update_keywords($catid);

    exit('OK');
}

//List faq
$listcats = [];
$listcats[0] = [
    'id' => 0,
    'name' => $nv_Lang->getModule('nocat'),
    'title' => $nv_Lang->getModule('nocat')
];
$listcats = $listcats + nv_listcats(0);
if (empty($listcats)) {
    header('Location: ' . NV_BASE_ADMINURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&' . NV_NAME_VARIABLE . '=' . $module_name . '&' . NV_OP_VARIABLE . '=cat&add=1');
    exit();
}

$page_title = $nv_Lang->getModule('faq_manager');

$page = $nv_Request->get_int('page', 'get', 0);
$per_page = 30;

$base_url = NV_BASE_ADMINURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&' . NV_NAME_VARIABLE . '=' . $module_name;

$catid = $nv_Request->get_int('catid', 'get', 0);
!isset($listcats[$catid]) && $catid = 0;
$caption = $catid ? sprintf($nv_Lang->getModule('faq_list_by_cat'), $listcats[$catid]['title']) : $nv_Lang->getModule('faq_manager');

if ($catid) {
    $base_url .= '&amp;catid=' . $catid;
    define('NV_IS_CAT', true);
}

$sql = 'SELECT SQL_CALC_FOUND_ROWS * FROM ' . NV_PREFIXLANG . '_' . $module_data . ' WHERE catid=' . $catid . ' ORDER BY weight ASC LIMIT ' . $page . ', ' . $per_page;
$query = $db->query($sql);
$result = $db->query('SELECT FOUND_ROWS()');
$all_page = $result->fetchColumn();

if (!$all_page) {
    if (!defined('NV_IS_CAT')) {
        header('Location: ' . NV_BASE_ADMINURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&' . NV_NAME_VARIABLE . '=' . $module_name . '&add=1');
        exit();
    }
}

$array = [];

while ($row = $query->fetch()) {
    $array[$row['id']] = [
        'id' => (int) $row['id'],
        'title' => $row['title'],
        'cattitle' => $listcats[$row['catid']]['title'],
        'catlink' => NV_BASE_ADMINURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module_name . (!empty($row['catid']) ? '&amp;catid=' . $row['catid'] : ''),
        'status' => $row['status'] ? ' checked="checked"' : ''
    ];

    $weight = [];
    for ($i = 1; $i <= $all_page; ++$i) {
        $weight[$i]['title'] = $i;
        $weight[$i]['pos'] = $i;
        $weight[$i]['selected'] = ($i == $row['weight']) ? ' selected="selected"' : '';
    }
    $array[$row['id']]['weight'] = $weight;
}

$generate_page = nv_generate_page($base_url, $all_page, $per_page, $page);

$xtpl = new XTemplate('main.tpl', NV_ROOTDIR . '/themes/' . $global_config['module_theme'] . '/modules/' . $module_file);
$xtpl->assign('LANG', \NukeViet\Core\Language::$lang_module);
$xtpl->assign('GLANG', \NukeViet\Core\Language::$lang_global);
$xtpl->assign('TABLE_CAPTION', $caption);
$xtpl->assign('ADD_NEW_FAQ', NV_BASE_ADMINURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module_name . '&amp;add=1');
$xtpl->assign('URL', NV_BASE_ADMINURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&' . NV_NAME_VARIABLE . '=' . $module_name);

foreach ($listcats as $cat) {
    $cat['sel'] = $cat['id'] == $catid ? ' selected="selected"' : '';
    $xtpl->assign('CAT', $cat);
    $xtpl->parse('main.listCat');
}

if (!empty($array)) {
    $a = 0;
    foreach ($array as $row) {
        $xtpl->assign('CLASS', $a % 2 == 1 ? ' class="second"' : '');
        $xtpl->assign('ROW', $row);

        foreach ($row['weight'] as $weight) {
            $xtpl->assign('WEIGHT', $weight);
            $xtpl->parse('main.rows.row.weight');
        }

        $xtpl->assign('EDIT_URL', NV_BASE_ADMINURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module_name . '&amp;edit=1&amp;id=' . $row['id']);
        $xtpl->parse('main.rows.row');
        ++$a;
    }
    $xtpl->parse('main.rows');
}

if (!empty($generate_page)) {
    $xtpl->assign('GENERATE_PAGE', $generate_page);
    $xtpl->parse('main.generate_page');
}

$xtpl->parse('main');
$contents = $xtpl->text('main');

include NV_ROOTDIR . '/includes/header.php';
echo nv_admin_theme($contents);
include NV_ROOTDIR . '/includes/footer.php';
