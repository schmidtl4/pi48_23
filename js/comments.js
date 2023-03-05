//comments.js

$(function () {

    $(document).on('click', '.send-btn', function (e) {
        e.preventDefault();

        let $btn = $(this);
        let comment = $(document).find('.comment').text();
        $.ajax({
            data: {
                action: 'record_comment',
                originPage: $btn.data('pageName'),
                comment: comment
            },
            url: '',
            type: 'POST'

        }).done ( function (response) {

            $('.msg').html(response);

        })
    });
});
