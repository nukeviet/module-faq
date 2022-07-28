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

$url = [];
$cacheFile = NV_LANG_DATA . '_Sitemap.cache';
$cacheTTL = 7200;

if (($cache = $nv_Cache->getItem($module_name, $cacheFile, $cacheTTL)) != false) {
    $url = unserialize($cache);
} else {
    $in = array_keys($list_cats);
    $in[] = 0;
    $in = implode(',', $in);
    $sql = 'SELECT id, catid, weight, addtime 
        FROM ' . NV_PREFIXLANG . '_' . $module_data . ' WHERE catid IN (' . $in . ') 
        AND status=1 ORDER BY addtime DESC LIMIT 1000';
    $result = $db->query($sql);

    while (list($id, $cid, $weight, $publtime) = $result->fetch(3)) {
        $pg = ceil($weight / $per_page);
        $url[] = [
            'link' => NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module_name . ($cid ? '&amp;' . NV_OP_VARIABLE . '=' . $list_cats[$cid]['alias'] : '') . ($pg > 1 ? '/page-' . $pg : '') . '/question-' . $id,
            'publtime' => $publtime
        ];
    }

    $cache = serialize($url);
    $nv_Cache->setItem($module_name, $cacheFile, $cache, $cacheTTL);
}

nv_xmlSitemap_generate($url);
exit();
