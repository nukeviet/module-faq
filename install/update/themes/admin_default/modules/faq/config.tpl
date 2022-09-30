<!-- BEGIN: main -->
<div id="users">
    <form class="form-inline" action="{FORM_ACTION}" method="post">
        <table class="table table-striped table-bordered table-hover">
            <tbody>
                <tr>
                    <td width="260">{LANG.per_page}</td>
                    <td><select class="form-control" name="per_page">
                            <!-- BEGIN: per_page -->
                            <option value="{PP_OPTION.num}"{PP_OPTION.sel}>{PP_OPTION.num}</option>
                            <!-- END: per_page -->
                    </select></td>
                </tr>
                <tr>
                    <td width="260">{LANG.per_cat}</td>
                    <td><select class="form-control" name="per_cat">
                            <!-- BEGIN: per_cat -->
                            <option value="{PC_OPTION.num}"{PC_OPTION.sel}>{PC_OPTION.num}</option>
                            <!-- END: per_cat -->
                    </select></td>
                </tr>
            </tbody>
        </table>
        <div style="text-align: center; padding-top: 15px">
            <input class="btn btn-primary" type="submit" name="submit" value="{LANG.faq_save}" />
        </div>
    </form>
</div>
<!-- END: main -->