jQuery(document).ready(function ($) {
    $('select[name="user_tags[]"]').select2({
        ajax: {
            url: utp_ajax.ajax_url,
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return { action: 'search_user_tags', q: params.term, nonce:utp_ajax.nonce };
            },
            processResults: function (data) {
               // console.log('Select2 Response:', data); // Debug response in console
                return { results: data.data.results || [] };
            },
            cache: true
        },
        minimumInputLength: 2
    });
    var el = jQuery("[name='user_tag_filter']");
    el.change(function() {
        el.val(jQuery(this).val());
    });
});