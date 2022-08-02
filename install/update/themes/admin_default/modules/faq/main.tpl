<!-- BEGIN: main -->
<div class="form-group">
    <label><strong>{LANG.faq_catid_faq}</strong></label>
    <select class="form-control w300" onchange="getRows(this)">
        <!-- BEGIN: listCat -->
        <option value="{CAT.id}"{CAT.sel}>{CAT.name}</option>
        <!-- END: listCat -->
    </select>
</div>
<!-- BEGIN: rows -->
<div class="table-responsive">
    <table class="table table-striped table-bordered table-hover">
        <caption>{TABLE_CAPTION}</caption>
        <thead>
            <tr>
                <th class="w100">{LANG.faq_pos}</th>
                <th>{LANG.faq_title_faq}</th>
                <th>{LANG.faq_catid_faq}</th>
                <th class="w100 text-center">{LANG.faq_active}</th>
                <th class="w200 text-center">{LANG.faq_feature}</th>
            </tr>
        </thead>
        <tbody>
            <!-- BEGIN: row -->
            <tr>
                <td><select class="form-control" name="weight" id="weight{ROW.id}" onchange="nv_change_row_weight({ROW.id});">
                        <!-- BEGIN: weight -->
                        <option value="{WEIGHT.pos}"{WEIGHT.selected}>{WEIGHT.pos}</option>
                        <!-- END: weight -->
                </select></td>
                <td>{ROW.title}</td>
                <td><a href="{ROW.catlink}">{ROW.cattitle}</a></td>
                <td class="text-center"><input name="status" id="change_status{ROW.id}" value="1" type="checkbox" {ROW.status} onclick="nv_change_row_status({ROW.id})" /></td>
                <td class="text-center"><em class="fa fa-edit fa-lg">&nbsp;</em> <a href="{EDIT_URL}">{GLANG.edit}</a> &nbsp;&nbsp; <em class="fa fa-trash-o fa-lg">&nbsp;</em> <a href="javascript:void(0);" onclick="nv_row_del({ROW.id});">{GLANG.delete}</a></td>
            </tr>
            <!-- END: row -->
        <tbody>
            <!-- BEGIN: generate_page -->
            <tr class="footer">
                <td colspan="8">{GENERATE_PAGE}</td>
            </tr>
            <!-- END: generate_page -->
    </table>
</div>
<!-- END: rows -->
<a class="btn btn-primary" href="{ADD_NEW_FAQ}">{LANG.faq_addfaq}</a>
<script>
function getRows(obj) {
    var v = parseInt($(obj).val()),
        url = '{URL}';
    if (v) {
        url += '&catid=' + v
    }
    window.location.href = url
}
</script>
<!-- END: main -->