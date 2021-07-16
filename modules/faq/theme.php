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

/**
 * theme_main_faq()
 *
 * @param int    $catid
 * @param array  $items
 * @param array  $subcats
 * @param array  $subitems
 * @param string $pages
 * @return string
 */
function theme_main_faq($catid, $items, $subcats, $subitems, $pages)
{
    global $list_cats, $id, $lang_module, $lang_global, $module_info;

    $xtpl = new XTemplate('main.tpl', NV_ROOTDIR . '/themes/' . $module_info['template'] . '/modules/' . $module_info['module_theme']);
    $xtpl->assign('LANG', $lang_module);
    $xtpl->assign('GLANG', $lang_global);

    $cat = $catid ? $list_cats[$catid] : [
        'title' => $lang_module['faq'],
        'description' => $lang_module['faq_welcome']
    ];

    $xtpl->assign('CAT', $cat);

    if (!empty($items)) {
        foreach ($items as $item) {
            $xtpl->assign('ITEM', $item);
            $xtpl->parse('main.items.loop');
        }

        if (!empty($pages)) {
            $xtpl->assign('PAGES', $pages);
            $xtpl->parse('main.items.pages');
        }
        $xtpl->parse('main.items');
    }

    if ($id) {
        $xtpl->assign('ID', $id);
        $xtpl->parse('main.scrollTop');
    }

    if (!empty($subcats)) {
        foreach ($subcats as $subcat) {
            $xtpl->assign('SUBCAT', $list_cats[$subcat]);

            if (!empty($subitems[$subcat]['items'])) {
                foreach ($subitems[$subcat]['items'] as $subitem) {
                    $xtpl->assign('SUBITEM', $subitem);
                    $xtpl->parse('main.subcats.item.questions.loop');
                }

                if (!empty($subitems[$subcat]['more'])) {
                    $xtpl->parse('main.subcats.item.questions.more');
                }
                $xtpl->parse('main.subcats.item.questions');
            }
            $xtpl->parse('main.subcats.item');
        }
        $xtpl->parse('main.subcats');
    }

    $xtpl->parse('main');

    return $xtpl->text('main');
}
