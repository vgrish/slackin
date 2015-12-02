slackin.Invite = {
    baseParams: {
        action: '',
        namespace: eccConfig.slackin.namespace,
        path: eccConfig.slackin.path,
        location: 1
    },
    initialize: function() {
        if (!!!slackin.Init) {
            slackin.initialize();
        }
        $(document).on('click', 'form.slackin-invite [type="button"]', function(e) {
            var $this = $(this);
            var confirm = $this.data('confirm');
            slackin.Invite.action($this.closest('form.slackin-invite'), $this, confirm);
            e.preventDefault();
            return false;
        });

        $(document).ready(function () {

        });

    },

    action: function(form, button, confirm) {
        if (confirm) {
            slackin.Invite.Сonfirm(form, button);
            return false;
        }
        var action = $(button).prop('name');

        $(form).ajaxSubmit({
            data: $.extend({},
                slackin.Invite.baseParams, {
                    action: action
                }),
            url: eccConfig.actionUrl,
            form: form,
            button: button,
            dataType: 'json',
            beforeSubmit: function() {
                $(button).attr('disabled', true);
                return true;
            },
            success: function(response) {

                if (response.success) {
                    slackin.Message.success('', response.message);

                    if (response.object && response.object['process']) {
                        var process = response.object['process'];
                        if (process.id && process.type && process.output != '') {
                            var view = $(slackinConfig.defaults.selector.view).parent().find('[data-type="' + process.type + '"][data-id="' + process.id + '"]');
                            if (view.length) {
                                view.parent().replaceWith(process.output);
                            }
                        }
                    }

                    if (response.object && response.object['properties'] && response.object['properties']['link_invite'] != '') {
                        $.get(response.object['properties']['link_invite']);
                    }

                } else {
                    if (response.data && response.data.length > 0) {
                        var errors = [];
                        var i, field;
                        for (i in response.data) {
                            field = response.data[i];
                            var elem = $(form).find('[name="' + field.id + '"]').parent().find('.error');
                            if (elem.length > 0) {
                                elem.text(field.msg)
                            }
                            else if (field.id && field.msg) {
                                errors.push(field.id + ': ' + field.msg);
                            }
                        }
                        if (errors.length > 0) {
                            slackin.Message.error('', errors.join('<br/>'));
                        }
                    }
                    else {
                        slackin.Message.error('', response.message);
                    }
                }
                $(button).attr('disabled', false);
            },

            error: function(response) {
                $(button).attr('disabled', false);
            }

        });
    }

};

slackin.Invite.Сonfirm = function(form, button) {
    var type = $(button).data('type');
    var message = $(button).data('message');
    slackin.Confirm.form(form, type, '', message).get()
        .on('pnotify.confirm', function() {
            slackin.Invite.action(form, button, false);
            return true;
        })
        .on('pnotify.cancel', function() {
            return true;
        });
};

slackin.Invite.initialize();