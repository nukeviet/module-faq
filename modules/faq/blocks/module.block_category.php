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

global $catid;

if (!nv_function_exists('nv_faq_category')) {
    function nv_faq_category()
    {
        global $list_cats, $module_name, $module_info, $catid, $nv_Lang;

        $xtpl = new XTemplate('block_category.tpl', NV_ROOTDIR . '/themes/' . $module_info['template'] . '/modules/' . $module_info['module_theme']);
        $xtpl->assign('TEMPLATE', $module_info['template']);
        $xtpl->assign('BLOCK_ID', 'web' . rand(1, 1000));

        if (!empty($list_cats)) {
            $licss = empty($catid) ? ' class="home active"' : ' class="home"';
            $html = '<ul class="level-0">';
            $html .= '<li' . $licss . '>';
            $html .= '<a title="' . $nv_Lang->getModule('main_page') . '" href="' . NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module_name . '">' . $nv_Lang->getModule('main_page') . '</a>';
            $html .= '</li>';

            foreach ($list_cats as $cat) {
                if ($cat['parentid'] == 0) {
                    $licss = $cat['id'] == $catid ? ' class="active"' : '';
                    $html .= '<li' . $licss . '>';
                    $html .= '<a title="' . $cat['title'] . '" href="' . $cat['link'] . '">' . $cat['title'] . '</a>';
                    $html .= nv_faq_sub_category($cat['id']);
                    $html .= '</li>';
                }
            }

            $html .= '</ul>';

            $xtpl->assign('HTML_CONTENT', $html);
            $xtpl->parse('main');

            return $xtpl->text('main');
        }
    }

    function nv_faq_sub_category($id, $level = 1)
    {
        global $list_cats, $catid;

        if (empty($id)) {
            return '';
        }
        $html = '';
        foreach ($list_cats as $cat) {
            if ($cat['parentid'] == $id) {
                if (empty($html)) {
                    $html .= '<ul class="level-' . $level . '">';
                }
                ++$level;
                $licss = $cat['id'] == $catid ? ' class="active"' : '';
                $html .= '<li' . $licss . '>';
                $html .= '<a title="' . $cat['title'] . '" href="' . $cat['link'] . '">' . $cat['title'] . '</a>';
                $html .= nv_faq_sub_category($cat['id'], $level);
                $html .= '</li>';
            }
        }
        if (!empty($html)) {
            $html .= '</ul>';
        }

        return $html;
    }
}

$content = nv_faq_category();
