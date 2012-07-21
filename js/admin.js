function selectTab(name) {
    $('#tabcontrol > div').hide();
    $('ul.tabs > li').removeClass('active');
    $('#tab'+name).show();
    $('#tabHead'+name).addClass('active');
}

function removeElementFn(element) {
    setTimeout(function() {
        element.slideUp('slow', function() { element.remove(); });
    }, 5000);

    return function() {
        element.slideUp('slow', function() { element.remove(); });
    }
}

function displayPostResult(fn) {
    return function (data) {
        result = jQuery.parseJSON(data);
        if (result.success && result.message) {
            msgdiv = jQuery('<div/>', {
                class: 'alert-message '+ result.success ? 'success' : 'error'
            });
            closelink = jQuery('<a />', {
                class: 'close',
                href: '#',
                text: '×',
                click: removeElementFn(msgdiv)
            });
            msgdiv.append(closelink);
            msgdiv.append(jQuery('<p />', {
                text: result.message
            }));
            msgdiv.hide();
            msgdiv.appendTo($('#messages')).slideDown('slow');
        }
        if (fn) {
            fn(result);
        }
    };
}

function dropwikicat(id) {
    $.get('adminctrl.php', {
        'action': 'drop',
        'id' : id
    }, displayPostResult(function(d) {
        if (d.status == 'success') {
            tableRow = $('#trwikicat'+id);
            tableRow.hide('slow', function(){ tableRow.remove(); });
        }
    }));
}

$(document).ready(function(e) {
    selectTab('Categories');
    $('#tableCategories').tablesorter({
        sortList: [[0,0]],
        headers: {
            4: { sorter: false }
        }
    });

    $('#postwikicat').validate({
        debug: false,
        rules: {
            name: {
                required: true,
                maxlength: 50
            },
            zoom: {
                required: true,
                min: 6,
                max: 12
            },
            lat: {
                required: true,
                number: true
            },
            lon: {
                required: true,
                number: true
            }
        },
        submitHandler: function(form) {
            $.get('adminctrl.php', $('#postwikicat').serialize(), displayPostResult(function(data) {
                if (data.status == 'success') {
                    cat = data.data;
                    row = "<tr id=\"trwikicat"+cat.id+"\"><td>"+cat.name+"</td><td>"
                        + cat.lat + "</td><td>" + cat.lon + "</td><td>" + cat.zoom + "</td><td>"
                        + "<a class=\"close\" onclick=\"javascript:dropwikicat("
                        + cat.id+");\">&times;</a></td></tr>";
                    $('#tableCategories tbody').append(row);
                    $('#tableCategories').trigger("update");
                    $('#postwikicat')[0].reset();
                }
            }));
        }
    });
});