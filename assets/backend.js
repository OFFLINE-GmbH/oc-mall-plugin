/**
 * Initialize Sorting
 * @deprecated OctoberCMS v2.2+ only
 * @returns 
 */
function initializeSorting () {
    if (typeof Sortable === 'undefined') return;
    var $tbody = $('.drag-handle').parents('table.data tbody');
	$tbody.each(function () {
		var data = {};
		var field = this.closest('div.form-group[data-field-name]');

		if (field) {
			data.fieldName = field.dataset.fieldName;
		}
		Sortable.create(this, {
			handle: '.drag-handle',
			animation: 150,
			onEnd: function (evt) {
				var $inputs = $(evt.target).find('td>div.drag-handle>input');
				var $form = $('<form style="display: none;">');
				$form.append($inputs.clone())
					.request('onReorderRelation', {
						data: data,
						complete: function () {
							$form.remove();
						}
					});
			}
		});
	});
}

$(function () {
    initializeSorting();
    $(window).on('ajaxUpdateComplete', initializeSorting)
});