function save_wallabag(active) {
    if (active.length === 0) {
	return false;
    }

    var wallabagBtn = active.find("a.wallabag");
    var url = wallabagBtn.attr("href");
    if (url === undefined) {
	return false;
    }
    wallabagBtn.text('.');

    if (pending_entries[active.attr('id')]) {
	return false;
    }
    pending_entries[active.attr('id')] = true;

    $.ajax({
	type: 'POST',
	url: url,
	data : {
	    ajax: true,
	    _csrf: context.csrf,
	},
    }).done(function (data) {
	delete pending_entries[active.attr('id')];
        wallabagBtn.text('✓');
    }).fail(function (data) {
	openNotification(i18n.notif_request_failed, 'bad');
	delete pending_entries[active.attr('id')];
    });
}

$(document).ready(function() {
    $('#stream .flux a.wallabag').on('click', function () {
        var active = $(this).parents(".flux");
        save_wallabag(active);
        return false;
    });
});
