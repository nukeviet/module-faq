<?php

/**
 * NukeViet Content Management System
 * @version 4.x
 * @author VINADES.,JSC <contact@vinades.vn>
 * @copyright (C) 2009-2021 VINADES.,JSC. All rights reserved
 * @license GNU/GPL version 2 or any later version
 * @see https://github.com/nukeviet The NukeViet CMS GitHub project
 */

if (!defined('NV_ADMIN') or !defined('NV_MAINFILE') or !defined('NV_IS_MODADMIN')) {
    exit('Stop!!!');
}

$submenu['cat'] = $nv_Lang->getModule('faq_catmanager');
$submenu['config'] = $nv_Lang->getModule('config');

$allow_func = [
    'main',
    'cat',
    'config'
];

define('NV_IS_FILE_ADMIN', true);

/**
 * nv_setcats()
 *
 * @param mixed $list2
 * @param mixed $id
 * @param mixed $list
 * @param int   $m
 * @param int   $num
 * @return
 */
function nv_setcats($list2, $id, $list, $m = 0, $num = 0)
{
    ++$num;
    $defis = '';
    for ($i = 0; $i < $num; ++$i) {
        $defis .= '--';
    }

    if (isset($list[$id])) {
        foreach ($list[$id] as $value) {
            if ($value['id'] != $m) {
                $list2[$value['id']] = $value;
                $list2[$value['id']]['name'] = '|' . $defis . '&gt; ' . $list2[$value['id']]['name'];
                if (isset($list[$value['id']])) {
                    $list2 = nv_setcats($list2, $value['id'], $list, $m, $num);
                }
            }
        }
    }

    return $list2;
}

/**
 * nv_listcats()
 *
 * @param mixed $parentid
 * @param int   $m
 * @return
 */
function nv_listcats($parentid, $m = 0)
{
    global $db, $module_data;

    $sql = 'SELECT * FROM ' . NV_PREFIXLANG . '_' . $module_data . '_categories ORDER BY parentid,weight ASC';
    $result = $db->query($sql);
    $list = [];
    while ($row = $result->fetch()) {
        $list[$row['parentid']][] = [
            'id' => (int) $row['id'],
            'parentid' => (int) $row['parentid'],
            'title' => $row['title'],
            'alias' => $row['alias'],
            'description' => $row['description'],
            'groups_view' => !empty($row['groups_view']) ? explode(',', $row['groups_view']) : [],
            'weight' => (int) $row['weight'],
            'status' => $row['weight'],
            'name' => $row['title'],
            'selected' => $parentid == $row['id'] ? ' selected="selected"' : ''
        ];
    }

    if (empty($list)) {
        return $list;
    }

    $list2 = [];
    foreach ($list[0] as $value) {
        if ($value['id'] != $m) {
            $list2[$value['id']] = $value;
            if (isset($list[$value['id']])) {
                $list2 = nv_setcats($list2, $value['id'], $list, $m);
            }
        }
    }

    return $list2;
}

/**
 * nv_update_keywords()
 *
 * @param int $catid
 * @return
 */
function nv_update_keywords($catid)
{
    global $db, $module_data;

    $catid = (int) $catid;

    if (empty($catid)) {
        return '';
    }

    $content = [];

    $sql = 'SELECT * FROM ' . NV_PREFIXLANG . '_' . $module_data . ' WHERE catid=' . $catid . ' AND status=1';
    $result = $db->query($sql);

    while ($row = $result->fetch()) {
        $content[] = $row['title'] . ' ' . $row['question'] . ' ' . $row['answer'];
    }

    $content = implode(' ', $content);

    $keywords = nv_get_keywords($content);

    if (!empty($keywords)) {
        $db->query('UPDATE ' . NV_PREFIXLANG . '_' . $module_data . '_categories SET keywords=' . $db->quote($keywords) . ' WHERE id=' . $catid);
    }

    return $keywords;
}

/**
 * nv_FixWeight()
 *
 * @param int $catid
 */
function nv_FixWeight($catid)
{
    global $db, $module_data;

    $sql = 'SELECT id FROM ' . NV_PREFIXLANG . '_' . $module_data . ' WHERE catid=' . $catid . ' ORDER BY weight ASC';
    $result = $db->query($sql);
    $weight = 0;
    $rs = [];
    $in = [];
    while ($row = $result->fetch()) {
        ++$weight;
        $rs[] = 'WHEN id = ' . $row['id'] . ' THEN ' . $weight;
        $in[] = $row['id'];
    }
    if (!empty($rs)) {
        $rs = '(CASE ' . implode(' ', $rs) . ' END)';
        $in = implode(',', $in);
        $db->query('UPDATE ' . NV_PREFIXLANG . '_' . $module_data . ' SET weight = ' . $rs . ' WHERE id IN (' . $in . ')');
    }
}

/**
 * nv_FixWeightCat()
 *
 * @param int $parentid
 * @return
 */
function nv_FixWeightCat($parentid = 0)
{
    global $db, $module_data;

    $sql = 'SELECT id FROM ' . NV_PREFIXLANG . '_' . $module_data . '_categories WHERE parentid=' . $parentid . ' ORDER BY weight ASC';
    $result = $db->query($sql);
    $weight = 0;
    $rs = [];
    $in = [];
    while ($row = $result->fetch()) {
        ++$weight;
        $rs[] = 'WHEN id = ' . $row['id'] . ' THEN ' . $weight;
        $in[] = $row['id'];
    }
    if (!empty($rs)) {
        $rs = '(CASE ' . implode(' ', $rs) . ' END)';
        $in = implode(',', $in);
        $db->query('UPDATE ' . NV_PREFIXLANG . '_' . $module_data . '_categories SET weight = ' . $rs . ' WHERE id IN (' . $in . ')');
    }
}
