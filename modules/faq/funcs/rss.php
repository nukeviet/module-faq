<?php

/**
 * NukeViet Content Management System
 * @version 4.x
 * @author VINADES.,JSC <contact@vinades.vn>
 * @copyright (C) 2009-2021 VINADES.,JSC. All rights reserved
 * @license GNU/GPL version 2 or any later version
 * @see https://github.com/nukeviet The NukeViet CMS GitHub project
 */

if (!defined('NV_IS_MOD_FAQ')) {
    exit('Stop!!!');
}

$channel = [];
$items = [];

$channel['title'] = $module_info['custom_title'] . ' - ' . $nv_Lang->getModule('general_questions');
$channel['link'] = NV_MY_DOMAIN . NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module_name;
$channel['description'] = !empty($module_info['description']) ? $module_info['description'] : $global_config['site_description'];
$atomlink = NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module_name . '&amp;' . NV_OP_VARIABLE . '=' . $module_info['alias']['rss'];

$catalias = isset($array_op[1]) ? $array_op[1] : '';
$catid = 0;
if (!empty($catalias)) {
    foreach ($list_cats as $c) {
        if ($c['alias'] == $catalias) {
            $catid = (int) $c['id'];
            break;
        }
    }
}
if (empty($catid)) {
    $catalias = '';
}

if ($catid > 0) {
    $channel['title'] = $module_info['custom_title'] . ' - ' . $list_cats[$catid]['title'];
    $channel['link'] = NV_MY_DOMAIN . NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module_name . '&amp;' . NV_OP_VARIABLE . '=' . $list_cats[$catid]['alias'];
    $channel['description'] = $list_cats[$catid]['description'];
    $atomlink .= '/' . $catalias;
}

$sql = 'SELECT id, catid, title, question, weight, addtime
        FROM ' . NV_PREFIXLANG . '_' . $module_data . ' WHERE catid=' . $catid . '
        AND status=1 ORDER BY weight ASC LIMIT ' . $per_page;

if ($module_info['rss']) {
    if (($result = $db->query($sql)) !== false) {
        $link = NV_MY_DOMAIN . NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module_name;
        while (list($id, $cid, $title, $question, $weight, $addtime) = $result->fetch(3)) {
            if ($cid) {
                $link .= '&amp;' . NV_OP_VARIABLE . '=' . $list_cats[$cid]['alias'];
            }
            $pg = ceil($weight / $per_page);
            if ($pg > 1) {
                $link .= '/page-' . $pg;
            }
            $items[] = [
                'title' => $title,
                'link' => $link . '/question-' . $id,
                'guid' => $module_name . '_' . $id,
                'description' => $nv_Lang->getModule('faq_question') . ': ' . $question,
                'pubdate' => $addtime
            ];
        }
    }
}

nv_rss_generate($channel, $items, $atomlink);
exit();
