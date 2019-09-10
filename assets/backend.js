function initializeSorting () {
    if (typeof Sortable === 'undefined') return;
    var $tbody = $('.drag-handle').parents('table.data tbody');
    Sortable.create($tbody[0], {
        handle: '.drag-handle',
        animation: 150,
        onEnd: function (evt) {
            var $inputs = $(evt.target).find('td>div.drag-handle>input');
            var $form = $('<form style="display: none;">');
            $form.append($inputs.clone())
                .request('onReorderRelation', {
                    complete: function () {
                        $form.remove();
                    }
                });
        }
    });
}

$(function () {
    initializeSorting();
    $(window).on('ajaxUpdateComplete', initializeSorting)
});