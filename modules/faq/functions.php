<?php

/**
 * NukeViet Content Management System
 * @version 4.x
 * @author VINADES.,JSC <contact@vinades.vn>
 * @copyright (C) 2009-2021 VINADES.,JSC. All rights reserved
 * @license GNU/GPL version 2 or any later version
 * @see https://github.com/nukeviet The NukeViet CMS GitHub project
 */

if (!defined('NV_SYSTEM')) {
    exit('Stop!!!');
}

define('NV_IS_MOD_FAQ', true);

/**
 * nv_setcats()
 *
 * @param mixed $id
 * @param mixed $list
 * @param mixed $name
 * @param mixed $is_parentlink
 * @return
 */
function nv_setcats($id, $list, $name, $is_parentlink)
{
    global $module_name;

    if ($is_parentlink) {
        $name = '<a href="' . NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module_name . '&amp;' . NV_OP_VARIABLE . '=' . $list[$id]['alias'] . '">' . $list[$id]['title'] . '</a> &raquo; ' . $name;
    } else {
        $name = $list[$id]['title'] . ' &raquo; ' . $name;
    }
    $parentid = $list[$id]['parentid'];
    if ($parentid) {
        $name = nv_setcats($parentid, $list, $name, $is_parentlink);
    }

    return $name;
}

function nv_list_cats(&$list_cats, &$home_subcats, $is_link = false, $is_parentlink = true)
{
    global $db, $module_data, $module_name, $module_info;

    $sql = 'SELECT * FROM ' . NV_PREFIXLANG . '_' . $module_data . '_categories WHERE status=1 ORDER BY parentid,weight ASC';
    $result = $db->query($sql);

    $list_cats = [];
    $home_subcats = [];

    $list = [];
    while ($row = $result->fetch()) {
        if (nv_user_in_groups($row['groups_view'])) {
            $list[$row['id']] = [
                'id' => (int) $row['id'],
                'title' => $row['title'],
                'alias' => $row['alias'],
                'description' => $row['description'],
                'parentid' => (int) $row['parentid'],
                'subcats' => [],
                'keywords' => $row['keywords']
            ];
            if (empty($row['parentid'])) {
                $home_subcats[] = (int) $row['id'];
            }
        }
    }

    if (!empty($list)) {
        foreach ($list as $row) {
            if (!$row['parentid'] or isset($list[$row['parentid']])) {
                $list_cats[$row['id']] = $list[$row['id']];
                $list_cats[$row['id']]['link'] = NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module_name . '&amp;' . NV_OP_VARIABLE . '=' . $list_cats[$row['id']]['alias'];
                $list_cats[$row['id']]['name'] = $list[$row['id']]['title'];
                if ($is_link) {
                    $list_cats[$row['id']]['name'] = '<a href="' . NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module_name . '&amp;' . NV_OP_VARIABLE . '=' . $list_cats[$row['id']]['alias'] . '">' . $list_cats[$row['id']]['name'] . '</a>';
                }

                if ($row['parentid']) {
                    $list_cats[$row['parentid']]['subcats'][] = $row['id'];

                    $list_cats[$row['id']]['name'] = nv_setcats($row['parentid'], $list, $list_cats[$row['id']]['name'], $is_parentlink);
                }

                if ($is_parentlink) {
                    $list_cats[$row['id']]['name'] = '<a href="' . NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module_name . '">' . $module_info['custom_title'] . '</a> &raquo; ' . $list_cats[$row['id']]['name'];
                }
            }
        }
    }
}

/**
 * initial_config_data()
 *
 * @return
 */
function initial_config_data()
{
    global $module_data, $nv_Cache, $module_name;

    $sql = 'SELECT config_name, config_value FROM ' . NV_PREFIXLANG . '_' . $module_data . '_config';

    $list = $nv_Cache->db($sql, '', $module_name);

    $module_setting = [];
    foreach ($list as $values) {
        $module_setting[$values['config_name']] = $values['config_value'];
    }

    return $module_setting;
}

$module_setting = initial_config_data();
($module_setting['per_cat'] > $module_setting['per_page']) && $module_setting['per_cat'] = $module_setting['per_page'];

/**
 * update_keywords()
 *
 * @param mixed $catid
 * @param mixed $faq
 * @return
 */
function update_keywords($catid, $faq)
{
    global $db, $module_data;

    $content = [];
    foreach ($faq as $row) {
        $content[] = $row['title'] . ' ' . $row['question'] . ' ' . $row['answer'];
    }

    $content = implode(' ', $content);

    $keywords = nv_get_keywords($content);

    if (!empty($keywords)) {
        $db->query('UPDATE ' . NV_PREFIXLANG . '_' . $module_data . '_categories SET keywords=' . $db->quote($keywords) . ' WHERE id=' . $catid);
    }

    return $keywords;
}

$per_page = (int) $module_setting['per_page'];
$catid = 0;
$alias = '';
$page = 1;
$id = 0;
if (!empty($array_op)) {
    if (substr($array_op[0], 0, 5) == 'page-') {
        $page = (int) (substr($array_op[0], 5));
    } elseif (substr($array_op[0], 0, 9) == 'question-') {
        $id = (int) (substr($array_op[0], 9));
    } else {
        $alias = $array_op[0];
    }
    if (isset($array_op[1])) {
        if (!empty($alias) and substr($array_op[1], 0, 5) == 'page-') {
            $page = (int) (substr($array_op[1], 5));

            if (isset($array_op[2]) and substr($array_op[2], 0, 9) == 'question-') {
                $id = (int) (substr($array_op[2], 9));
            }
        } elseif (substr($array_op[1], 0, 9) == 'question-') {
            $id = (int) (substr($array_op[1], 9));
        }
    }
}

$list_cats = [];
$home_subcats = [];
nv_list_cats($list_cats, $home_subcats, true);

// Xac dinh ID cua chu de
foreach ($list_cats as $c) {
    if (!empty($c['alias']) and $c['alias'] == $alias) {
        $catid = (int) ($c['id']);
        break;
    }
}

if (empty($catid)) {
    $alias = '';
}

//Xac dinh menu
$nv_vertical_menu = [];

//Xac dinh RSS
if ($module_info['rss']) {
    $rss[] = [
        'title' => $module_info['custom_title'],
        'src' => NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module_name . '&amp;' . NV_OP_VARIABLE . '=rss'
    ];

    foreach ($list_cats as $c) {
        $rss[] = [
            'title' => $module_info['custom_title'] . ' - ' . $c['title'],
            'src' => NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module_name . '&amp;' . NV_OP_VARIABLE . '=rss/' . $c['alias']
        ];
    }
}

if ($catid > 0) {
    $parentid = $catid;
    while ($parentid > 0) {
        $c = $list_cats[$parentid];
        $array_mod_title[] = [
            'catid' => $parentid,
            'title' => $c['title'],
            'link' => NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module_name . '&amp;' . NV_OP_VARIABLE . '=' . $c['alias']
        ];
        $parentid = $c['parentid'];
    }
    krsort($array_mod_title, SORT_NUMERIC);
}
