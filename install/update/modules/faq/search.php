<?php

/**
 * NukeViet Content Management System
 * @version 4.x
 * @author VINADES.,JSC <contact@vinades.vn>
 * @copyright (C) 2009-2021 VINADES.,JSC. All rights reserved
 * @license GNU/GPL version 2 or any later version
 * @see https://github.com/nukeviet The NukeViet CMS GitHub project
 */

if (!defined('NV_IS_MOD_SEARCH')) {
    exit('Stop!!!');
}


$p_page = (int) $db->query('SELECT config_value FROM ' . NV_PREFIXLANG . '_' . $m_values['module_data'] . "_config WHERE config_name='per_page'")->fetchColumn();

$sql = 'SELECT id, title, alias, groups_view FROM ' . NV_PREFIXLANG . '_' . $m_values['module_data'] . '_categories WHERE status=1';
$result = $db->query($sql);

$list_cats = [];
while ($row = $result->fetch()) {
    if (nv_user_in_groups($row['groups_view'])) {
        $list_cats[$row['id']] = [
            'id' => (int) $row['id'],
            'title' => $row['title'],
            'alias' => $row['alias']
        ];
    }
}
$in = array_keys($list_cats);
$in[] = 0;
$in = implode(',', $in);
$num_items = 0;
$result_array = [];

if (!empty($in)) {
    $db->sqlreset()
        ->select('COUNT(*)')
        ->from(NV_PREFIXLANG . '_' . $m_values['module_data'])
        ->where('catid IN (' . $in . ')
    	AND
    	(' . nv_like_logic('question', $dbkeyword, $logic) . '
        OR ' . nv_like_logic('title', $dbkeyword, $logic) . '
    	OR ' . nv_like_logic('answer', $dbkeyword, $logic) . ')');

    $num_items = $db->query($db->sql())
        ->fetchColumn();

    if ($num_items) {
        $db->select('id, title, question, answer, weight, catid')
            ->order('id DESC')
            ->limit($limit)
            ->offset(($page - 1) * $limit);

        $tmp_re = $db->query($db->sql());
        $link = NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $m_values['module_name'];
        while (list($id, $title, $question, $answer, $weight, $catid) = $tmp_re->fetch(3)) {
            if ($catid) {
                $link .= '&amp;' . NV_OP_VARIABLE . '=' . $list_cats[$catid]['alias'];
            }
            $pg = ceil($weight / $p_page);
            if ($pg > 1) {
                $link .= '/page-' . $pg;
            }

            $result_array[] = [
                'link' => $link . '/question-' . $id,
                'title' => BoldKeywordInStr($title, $key, $logic),
                'content' => '<strong>' . BoldKeywordInStr($question, $key, $logic) . '</strong><br/>' . BoldKeywordInStr($answer, $key, $logic)
            ];
        }
    }
}
