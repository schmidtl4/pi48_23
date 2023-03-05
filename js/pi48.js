//pi48.js

$( function () {

    $(document).on('click', '#login', function (e) {
        e.preventDefault();

        $.ajax({
            data: {
                action: 'login'
            },
            url: '',
            type: 'POST'

        }).done ( function (response) {

            $("#modalDiv").html(''); //clear div
            $("#modalDiv").html(response);
            $(".modal").modal('show');

            $('#messages').html(response);

        }).fail ( function (response) {

        });
    });
});
