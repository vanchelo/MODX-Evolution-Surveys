var Survey = {

    delete: function (id) {
        if (!confirm('Вы действительно хотите удалить этот опрос?')) {
            return false;
        }

        jQuery.ajax({
            url: window.location.href + '&action=delete',
            type: 'get',
            data: { survey: id },
            dataType: 'json',
            success: function (d) {
                alert(d.message);
                if (!d.error) {
                    window.location.reload();
                }
            }
        });

        return false;
    },

    reset: function (id) {
        if (!confirm('Вы действительно хотите сбросить все голоса этот опрос?')) {
            return false;
        }

        jQuery.ajax({
            url: window.location.href + '&action=reset',
            type: 'get',
            data: { survey: id },
            dataType: 'json',
            success: function (d) {
                alert(d.message);
                if (!d.error) {
                    window.location.reload();
                }
            }
        });

        return false;
    },

    close: function (id) {
        if (!confirm('Вы действительно хотите закрыть этот опрос?')) {
            return false;
        }

        jQuery.ajax({
            url: window.location.href + '&action=close',
            type: 'get',
            data: {survey: id},
            dataType: 'json',
            success: function (d) {
                alert(d.message);
                if (!d.error) {
                    window.location.reload();
                }
            }
        });

        return false;
    },

    update: function (form) {
        if (!confirm('Вы действительно хотите сохранить этот опрос?')) {
            return false;
        }

        var $form = jQuery(form);

        jQuery.ajax({
            url: form.action,
            type: 'post',
            data: $form.serializeForm(),
            dataType: 'json',
            success: function (d) {
                if (d.errors) {
                    alert(d.message + "\n\n" + d.errors.join("\n"));
                } else {
                    alert(d.message);
                    window.location.reload();
                }
            }
        });

        return false;
    },

    create: function (form) {
        var $form = jQuery(form),
            data = $form.serializeForm();

        jQuery.ajax({
            url: form.action,
            type: 'post',
            data: data,
            dataType: 'json',
            success: function (d) {
                if (d.errors) {
                    alert(d.message + "\n\n" + d.errors.join("\n"));
                } else {
                    alert(d.message);
                    window.location.href = window.location.href.replace('&action=create', '')
                }
            }
        });

        return false;
    },

    addOption: function (btn) {
        var $btn = jQuery(btn),
            elem = $btn.parent().prev(),
            sort = parseInt(elem.find('input').next().val()) || 0,
            answer = jQuery('<li><input required type="text" name="new_option[]" /><input type="text" name="new_option_sort[]" value="0" /><span onclick="Survey.removeOption(this)">&times;</span></li>');

        answer.find('input').next().val(sort + 1);
        answer.insertAfter(elem);
    },

    removeOption: function (btn) {
        var $btn = jQuery(btn);

        if ($btn.parent().parent().find('li').length > 2) {
            $btn.parent().remove();
        }
    },

    init: function () {
    }

};

Survey.init();

$.fn.serializeForm = function () {
    if (this.length < 1) {
        return false
    }

    var data = {},
        lookup = data, //current reference of data
        selector = ':input[type!="checkbox"][type!="radio"]:not(:disabled), input:checked:not(:disabled)',
        parse = function () {

            // data[a][b] becomes [ data, a, b ]
            var named = this.name.replace(/\[([^\]]+)?\]/g, ',$1').split(','),
                cap = named.length - 1,
                $el = $(this);

            // Ensure that only elements with valid `name` properties will be serialized
            if (named[0]) {
                for (var i = 0; i < cap; i++) {
                    if (lookup[named[i]]) {
                        lookup = lookup[named[i]];
                    }
                    else {
                        // move down the tree - create objects or array if necessary
                        var node = ( named[i + 1] === "" || !isNaN(named[i + 1]) ) ? [] : {};
                        // push or assign the new node
                        if (lookup.length !== undefined) {
                            lookup.push(node);
                            lookup = node;
                        } else {
                            lookup = lookup[named[i]] = node;
                        }
                    }
                }

                // at the end, push or assign the value
                if (lookup.length !== undefined) {
                    lookup.push($el.val());
                } else {
                    lookup[named[cap]] = $el.val();
                }

                // assign the reference back to root
                lookup = data;
            }
        };

    // first, check for elements passed into this function
    this.filter(selector).each(parse);

    // then parse possible child elements
    this.find(selector).each(parse);

    // return data
    return data;
}
