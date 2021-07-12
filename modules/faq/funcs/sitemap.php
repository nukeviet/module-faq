<?php

/**
 * @Project NUKEVIET 4.x
 * @Author VINADES.,JSC (contact@vinades.vn)
 * @Copyright (C) 2014 VINADES.,JSC.
 * All rights reserved
 * @License GNU/GPL version 2 or any later version
 * @Createdate 4/12/2010, 1:27
 */
if (!defined('NV_IS_MOD_FAQ')) {
    die('Stop!!!');
}

$page_url = $base_url = NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module_name;
$page = 1;
if (isset($array_op[0]) and substr($array_op[0], 0, 5) == 'page-') {
    $page = intval(substr($array_op[0], 5));
}
if ($page > 1) {
    $page_url .= '&amp;' . NV_OP_VARIABLE . '=page-' . $page;
}
$canonicalUrl = getCanonicalUrl($page_url, true, true);

$url = array();
$cacheFile = NV_LANG_DATA . '_Sitemap.cache';
$cacheTTL = 7200;

if (($cache = $nv_Cache->getItem($module_name, $cacheFile, $cacheTTL)) != false) {
    $url = unserialize($cache);
} else {
    $list_cats = nv_list_cats();
    $in = array_keys($list_cats);
    $in = implode(',', $in);
    
    $sql = 'SELECT id, catid, addtime 
        FROM ' . NV_PREFIXLANG . '_' . $module_data . ' WHERE catid IN (' . $in . ') 
        AND status=1 ORDER BY weight ASC LIMIT 1000';
    $result = $db->query($sql);
    
    while (list ($id, $cid, $publtime) = $result->fetch(3)) {
        $url[] = array(
            'link' => NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module_name . '&amp;' . NV_OP_VARIABLE . '=' . $list_cats[$cid]['alias'] . '#faq' . $id,
            'publtime' => $publtime
        );
    }
    
    $cache = serialize($url);
    $nv_Cache->setItem($module_name, $cacheFile, $cache, $cacheTTL);
}

nv_xmlSitemap_generate($url);
die();
