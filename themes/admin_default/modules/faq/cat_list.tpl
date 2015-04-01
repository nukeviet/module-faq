<!-- BEGIN: main -->
<div id="users">
    <table class="table table-striped table-bordered table-hover">
        <caption>{TABLE_CAPTION}</caption>
        <thead>
            <tr>
                <td>
                    {LANG.faq_category_cat_sort}
                </td>
                <td>
                    {LANG.faq_category_cat_name}
                </td>
                <td>
                    {LANG.faq_category_cat_parent}
                </td>
                <td style="width:20px;text-align:center">
                    {LANG.faq_category_cat_active}
                </td>
                <td style="width:100px;white-space:nowrap;text-align:center">
                    {LANG.faq_category_cat_feature}
                </td>
            </tr>
        </thead>
        <tbody>
        <!-- BEGIN: row -->
            <tr>
                <td style="width:15px">
                    <select class="form-control" name="weight" id="weight{ROW.id}" onchange="nv_chang_weight({ROW.id});">
                        <!-- BEGIN: weight -->
                        <option value="{WEIGHT.pos}"{WEIGHT.selected}>{WEIGHT.pos}</option>
                        <!-- END: weight -->
                    </select>
                </td>
                <td>
                    <strong><a href="{ROW.titlelink}">{ROW.title}</a></strong>{ROW.numsub}
                </td>
                <td>
                    {ROW.parentid}
                </td>
                <td style="width:20px;white-space:nowrap;text-align:center">
                    <input type="checkbox" name="active" id="change_status{ROW.id}" value="1"{ROW.status} onclick="nv_chang_status({ROW.id});" />
                </td>
                <td style="white-space:nowrap;text-align:center">
                    <span class="edit_icon"><a href="{EDIT_URL}">{GLANG.edit}</a></span>
                    &nbsp;&nbsp;<span class="delete_icon"><a href="javascript:void(0);" onclick="nv_cat_del({ROW.id});">{GLANG.delete}</a></span>
                </td>
            </tr>
        <!-- END: row -->
        <tbody>
    </table>
</div>
<div style="margin-top:8px;">
    <a class="button1" href="{ADD_NEW_CAT}"><span><span>{LANG.faq_addcat_titlebox}</span></span></a>
</div>
<!-- END: main -->