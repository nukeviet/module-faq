<!-- BEGIN: main -->
<div id="faq">
    <div class="flex-box">
        <div class="aside">
            <span class="sr-only">Module logo</span>
        </div>
        <div class="main">
            <h1 class="ptitle">{CAT.title}</h1>
            <p class="pdesc">{CAT.description}</p>
        </div>
    </div>
    <!-- BEGIN: items -->
    <p><strong>{LANG.general_questions}:</strong></p>
    <div class="panel-group items" id="item-list" role="tablist" aria-multiselectable="true">
        <!-- BEGIN: loop -->
        <div class="panel panel-{ITEM.panel_css}">
            <a class="panel-heading" role="button" id="question-{ITEM.id}" data-toggle="collapse" data-parent="#item-list" data-location="{ITEM.location}" href="#collapse-{ITEM.id}" aria-expanded="{ITEM.expanded}" aria-controls="collapse-{ITEM.id}">{ITEM.title}</a>
            <div id="collapse-{ITEM.id}" class="panel-collapse collapse{ITEM.in}" role="tabpanel" aria-labelledby="question-{ITEM.id}">
                <ul class="list-group">
                    <li class="list-group-item iquestion">
                        <span class="question">{LANG.faq_question}</span>
                        {ITEM.question}
                    </li>
                    <li class="list-group-item">
                        <span class="answer">{LANG.faq_answer}</span>
                        {ITEM.answer}
                    </li>
                </ul>
            </div>
        </div>
        <!-- END: loop -->
    </div>
    <!-- BEGIN: pages -->
    <div class="pages">
        {PAGES}
    </div>
    <!-- END: pages -->
    <!-- END: items -->
    <!-- BEGIN: subcats -->
    <div class="subcats">
        <!-- BEGIN: item -->
        <div class="item">
            <h2 class="ctitle"><a href="{SUBCAT.link}">{SUBCAT.title}</a></h2>
            <!-- BEGIN: questions -->
            <ul class="questions-list">
                <!-- BEGIN: loop -->
                <li class="questions-item"><a class="question" href="{SUBITEM.link}"><span>{SUBITEM.title}</span></a></li>
                <!-- END: loop -->
                <!-- BEGIN: more -->
                <li class="questions-item text-right"><a class="more" href="{SUBCAT.link}">{LANG.more}</a></li>
                <!-- END: more -->
            </ul>
            <!-- END: questions -->
        </div>
        <!-- END: item -->
    </div>
    <!-- END: subcats -->
</div>
<!-- BEGIN: scrollTop -->
<script>
    $(function() {
        $('html,body').stop().animate({
            scrollTop : $('#question-{ID}').offset().top
        }, 500);
    })
</script>
<!-- END: scrollTop -->
<!-- END: main -->