<!-- resources/views/index.blade.php -->

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Контакты без сделок</title>

    <!-- Подключение Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">

    <!-- Подключение jQuery и Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>

    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
<div class="container mt-5">
    <h1 class="mb-4">Контакты без сделок</h1>
    <button class="btn btn-primary" onclick="loadAll()">Load All Contacts</button>
    <div id="messageDiv" class="alert alert-info" style="display: none"></div>
    <table class="table table-bordered table-striped">
        <thead class="thead-dark">
        <tr>
            <th>Имя</th>
            <th>Действия</th>
        </tr>
        </thead>
        <tbody id="getRowOfTable">

        </tbody>
    </table>
</div>

<script>

    let limit = 25;
    let page = 1;

    function loadAll() {
        $.ajax({
            url: "{{ route('amoCrm.contacts.without.deals') }}",
            type: "GET",
            data: {
                limit: limit,
                page:page,
                _token: "{{ csrf_token() }}",
            },
            success: function(response) {
                $('#getRowOfTable').empty();
                if(response.message) {
                    $('#messageDiv').text(response.message).show();

                    setTimeout(function() {
                        $('#messageDiv').fadeOut();
                    }, 3000);
                }
                if (response.contacts && response.contacts.length > 0) {
                    response.contacts.forEach(function(contact) {
                        let row = '<tr><td>' + contact.name +
                            `</td><td><button class="btn btn-info" data-contact-id="${contact.id}" onclick="createTask(this)">Creat Task</button></td></tr>`;
                        $('#getRowOfTable').append(row);
                    });
                } else {
                    $('#getRowOfTable').append('<tr><td colspan="2">Контакты не найдены</td></tr>');
                }
            },
            error: function() {
                alert('Ошибка при получении данных');
            }
        });
    }

    function createTask(button) {
        let contactId = $(button).data('contact-id');
        $.ajax({
            url: "{{ route('amoCrm.task.create') }}",
            type: "POST",

            data: {
                _token: "{{csrf_token()}}",
                contact_id: contactId
            },
            success: function (response) {
                console.log(response)
                alert('Задача успешно создана для контакта с ID: ' + contactId);
            },
            error: function (xhr) {
                alert('Ошибка при создании задачи');
            }
        });
    }
</script>

</body>
</html>
