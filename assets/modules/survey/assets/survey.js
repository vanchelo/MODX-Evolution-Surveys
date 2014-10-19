function Surveys(url) {
    this.vote = function (form) {
        var $form = jQuery(form),
            data = $form.serializeObject();

        if ( ! data.option) return false;

        data.a = 'vote';

        jQuery.ajax({
            url: this.url,
            type: 'get',
            data: data,
            dataType: 'json',
            success: function (respone) {
                alert(respone.message);
                if (respone.data.html) {
                    $form.parent().html(respone.data.html)
                }
            }
        });

        return false;
    };
}

(function () {
    $.fn.serializeObject = $.fn.serializeObject || function () {
        var o = {};
        var a = this.serializeArray();
        $.each(a, function() {
            if (o[this.name] !== undefined) {
                if (!o[this.name].push) {
                    o[this.name] = [o[this.name]];
                }
                o[this.name].push(this.value || '');
            } else {
                o[this.name] = this.value || '';
            }
        });
        return o;
    };
})();
