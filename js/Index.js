//Index.js

$( function () {


    $(document).on('click', '.tile', function (e) {
        e.preventDefault();
        //alert ('tile clicked: '+$(this).data('name'));

        $.ajax({
            data: {
                pageName: $(this).data('name'),
                action: 'serve_page'
            },
            url: '',
            type: 'POST'
        }).done( function (url) {
            window.location.href=url;
        });
    });
});
