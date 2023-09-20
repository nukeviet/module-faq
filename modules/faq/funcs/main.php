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

$page_title = $mod_title = $module_info['custom_title'];
$key_words = $module_info['keywords'];
$description = $nv_Lang->getModule('faq_welcome');
$page_url = NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module_name;
/* if ('mobile_default' != $global_config['module_theme']) {
    $module_info['layout_funcs'][$op_file] = 'main';
} */

if ($catid) {
    $page_title = $list_cats[$catid]['title'] . ' - ' . $module_info['custom_title'];
    $description = $list_cats[$catid]['description'];
    $mod_title = $list_cats[$catid]['name'];
    if (!empty($list_cats[$catid]['keywords'])) {
        $key_words = $list_cats[$catid]['keywords'];
    }
    $page_url .= '/' . $list_cats[$catid]['alias'];
}

$base_url = $page_url;
if ($page > 1) {
    $page_url .= '/page-' . $page;
}

$db->sqlreset()
    ->select('COUNT(*)')
    ->from(NV_PREFIXLANG . '_' . $module_data)
    ->where('catid=' . $catid . ' AND status=1');

$num_items = $db->query($db->sql())
    ->fetchColumn();

betweenURLs($page, ceil($num_items / $per_page), $base_url, '/page-', $prevPage, $nextPage);

$orderby = 'weight ASC';
$db->select('id, title, alias, question, answer, addtime')
    ->order($orderby)
    ->limit($per_page)
    ->offset(($page - 1) * $per_page);

$result = $db->query($db->sql());

$items = [];
while ($row = $result->fetch()) {
    $row['expanded'] = $row['id'] == $id ? 'true' : 'false';
    $row['in'] = $row['id'] == $id ? ' in' : '';
    $row['panel_css'] = $row['id'] == $id ? 'primary' : 'default';
    $row['location'] = $page_url . '/question-' . $row['id'];
    $items[$row['id']] = $row;
}

if (!empty($id) and !isset($items[$id])) {
    nv_redirect_location($page_url);
}

if ($id) {
    $page_url .= '/question-' . $id;
}

$canonicalUrl = getCanonicalUrl($page_url, true, true);

$pages = nv_alias_page($page_title, $base_url, $num_items, $per_page, $page);

$subcats = $catid ? $list_cats[$catid]['subcats'] : $home_subcats;
$subitems = [];
if (!empty($subcats)) {
    $pr = (int) $module_setting['per_cat'] + 1;
    foreach ($subcats as $subcat) {
        $subitems[$subcat] = [
            'items' => [],
            'more' => false
        ];
        $db->sqlreset()
            ->select('id, title, alias, question, weight')
            ->from(NV_PREFIXLANG . '_' . $module_data)
            ->where('catid=' . $subcat . ' AND status=1')
            ->order('weight ASC')
            ->limit($pr);
        $result = $db->query($db->sql());
        $i = 0;
        while ($row = $result->fetch()) {
            ++$i;
            if ($i == $pr) {
                $subitems[$subcat]['more'] = true;
                break;
            }
            $pg = ceil($row['weight'] / $per_page);
            $row['link'] = $list_cats[$subcat]['link'] . ($pg > 1 ? '/page-' . $pg : '') . '/question-' . $row['id'];
            $subitems[$subcat]['items'][$row['id']] = $row;
        }
    }
}

$contents = theme_main_faq($catid, $items, $subcats, $subitems, $pages);

include NV_ROOTDIR . '/includes/header.php';
echo nv_site_theme($contents);
include NV_ROOTDIR . '/includes/footer.php';
