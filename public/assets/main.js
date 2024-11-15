const limit = 25; // лимит контактов за один запрос
let page = 1; // начальная страница для пагинации
let getContactsListQueryUrl = '/api/v4/contacts';
let createTaskUrl = '/api/v4/tasks';

function getContactsWithoutDeals() {
    $.ajax({
        url: getContactsListQueryUrl,
        method: 'GET',
        data: {
            limit: limit,
            with: 'leads', // чтобы получить сделки, связанные с контактами
            page: page
        }
    }).done(function(data) {
        if (data && data._embedded && data._embedded.contacts) {
            const contacts = data._embedded.contacts;

            // Проходимся по каждому контакту
            contacts.forEach(contact => {
                // Проверяем, что у контакта нет сделок
                if (!contact._embedded.leads || contact._embedded.leads.length === 0) {
                    createTaskForContact(contact.id);
                }
            });

            // Если есть еще страницы, загружаем следующую
            if (data._page !== data._page_count) {
                page++;
                getContactsWithoutDeals();
            }
        } else {
            console.log('Контактов нет');
            return false;
        }
    }).fail(function(error) {
        console.log('Ошибка при получении контактов');
        console.log(error);
        return false;
    });
}

function createTaskForContact(contactId) {
    $.ajax({
        url: createTaskUrl,
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({
            text: "Контакт без сделок", // текст задачи
            complete_till: Math.floor(Date.now() / 1000) + 86400, // срок выполнения задачи через 24 часа
            entity_id: contactId, // ID контакта
            entity_type: "contacts" // Тип сущности, в данном случае контакт
        })
    }).done(function(response) {
        console.log(`Задача создана для контакта с ID ${contactId}`);
    }).fail(function(error) {
        console.log(`Ошибка при создании задачи для контакта с ID ${contactId}`);
        console.log(error);
    });
}

// Запускаем процесс
getContactsWithoutDeals();
