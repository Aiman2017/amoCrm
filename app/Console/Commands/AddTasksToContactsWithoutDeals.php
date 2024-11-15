<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class AddTasksToContactsWithoutDeals extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'amoCrm:tasks';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    protected string $baseUrl = 'https://aymanalraidy2017.amocrm.ru/api/v4';

    protected string $accessToken  = "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6IjI0MTI1NDlhMzJhMTIwNmNmMjhlMjE0YzdjYjM1NzlkNmY1YWUwZTJhZTI5OWZlY2U5Y2U4ZWYxYTljZjhhN2E5YmZkNWVhYjQwNmI2MjVjIn0.eyJhdWQiOiI4MDNiYmRkMC1mZjlhLTQyZDgtOGM2Ni0wYjYyMzJiMTkzNmUiLCJqdGkiOiIyNDEyNTQ5YTMyYTEyMDZjZjI4ZTIxNGM3Y2IzNTc5ZDZmNWFlMGUyYWUyOTlmZWNlOWNlOGVmMWE5Y2Y4YTdhOWJmZDVlYWI0MDZiNjI1YyIsImlhdCI6MTczMTU0ODI5MSwibmJmIjoxNzMxNTQ4MjkxLCJleHAiOjE3MzIyMzM2MDAsInN1YiI6IjExNzc0ODQ2IiwiZ3JhbnRfdHlwZSI6IiIsImFjY291bnRfaWQiOjMyMDY1Njc0LCJiYXNlX2RvbWFpbiI6ImFtb2NybS5ydSIsInZlcnNpb24iOjIsInNjb3BlcyI6WyJjcm0iLCJmaWxlcyIsImZpbGVzX2RlbGV0ZSIsIm5vdGlmaWNhdGlvbnMiLCJwdXNoX25vdGlmaWNhdGlvbnMiXSwiaGFzaF91dWlkIjoiNjY3NGU0MDMtZjIzMy00ZjM3LWI1MzctMGM3MjliMzAxMzUyIiwiYXBpX2RvbWFpbiI6ImFwaS1iLmFtb2NybS5ydSJ9.jU9JfTxOrUFF8QQR-VRqsF7i0OjbfGTkW2FOYJyTHJXDnIZ-YZtMot_r0dGk_6nATGsKrLm4hT1PC7xI7OOHPEmhYIbrMiXQeSIhUixUBYxhn8lXAGwXMJNYqeWwbwQ8X24EvsF8qeczjMC7rvNci97_eBnsolKEPxvqUSMyigEoOWw4UbV8-rtfECp25z1hOYGwKHH_ML8dumsCl6S_DzLqhtv0aEwDpuRQpXhRRpFb24rCxaIP24nBfD0CdiMiQ6CALphMITmksMbvr7qlrQ9thm4-_6P6AJhVuWJ0TFa0AqRrubK1Iedn21VxXNXN5gHsdVqPjQHgEUnG6ZLC5A";

    /**
     * Execute the console command.
     */


//    public function createContact($name, $phone): void
//    {
//        $response = Http::withToken($this->accessToken)->post("{$this->baseUrl}/contacts", [
//            'name' => $name, // Имя контакта
//            'custom_fields_values' => [
//                [
//                    'field_id' => 12345, // ID поля телефона (вы можете получить его из интерфейса AmoCRM)
//                    'values' => [
//                        [
//                            'value' => $phone, // Номер телефона
//                            'enum_code' => 'MOB', // Тип телефона (MOB - мобильный)
//                        ]
//                    ]
//                ]
//            ]
//        ]);
//
//        if ($response->successful()) {
//            $this->info("Контакт с именем {$name} и телефоном {$phone} успешно добавлен");
//        } else {
//            $this->error("Ошибка при добавлении контакта с именем {$name}");
//        }
//    }

    public function handle()
    {
        $page = 1; // Начинаем с первой страницы
        $limit = 25; // Лимит контактов на страницу
        while (true) {
            $response = Http::withToken($this->accessToken)->get("{$this->baseUrl}/contacts", [
                'limit' => $limit,
                'with' => 'leads',
                'page' => $page,
            ]);
            // Проверка ответа
            if ($response->failed()) {
                $this->error('Ошибка при получении контактов');
                return;
            }


            $contacts = $response->json()['_embedded']['contacts'] ?? [];

            // Если нет контактов, выходим из цикла
            if (empty($contacts)) {
                $this->info('Контакты без сделок обработаны');
                break;
            }

            foreach ($contacts as $key => $contact) {
                if (empty($contact['_embedded']['leads'])) {
                    $this->createTaskForContact($contact['id']);

                }
            }

            // Переход к следующей странице
            $page++;
        }
    }

    protected function createTaskForContact($contactId): void
    {
        $response = Http::withToken($this->accessToken)->post("{$this->baseUrl}/tasks", [
            [
                'text' => 'Контакт без сделок',
                'complete_till' => now()->addDay()->timestamp, // Срок выполнения через 24 часа
                'entity_id' => $contactId,
                'entity_type' => 'contacts',
            ]
        ]);

        if ($response->successful()) {

            $this->info("Задача создана для контакта с ID {$contactId}");
//            $taskId = $response->json()['_embedded']['tasks'][0]['id'];
//            $this->closeTask($taskId);

        } else {
            $this->error("Ошибка при создании задачи для контакта с ID {$contactId}");
        }
    }

//    protected function closeTask($taskId): void
//    {
//        // Получаем информацию о задаче
//        $response = Http::withToken($this->accessToken)->get("{$this->baseUrl}/tasks/{$taskId}");
//
//        if ($response->successful()) {
//            $task = $response->json();
//
//            // Проверяем текущий статус задачи
//            if ($task['status'] != 5) {
//                // Закрываем задачу только если она не закрыта
//                $response = Http::withToken($this->accessToken)->put("{$this->baseUrl}/tasks/{$taskId}", [
//                    'status' => 5, // Статус "Выполнена" в AmoCRM
//                ]);
//
//                if ($response->successful()) {
//                    $this->info("Задача с ID {$taskId} успешно закрыта");
//                } else {
//                    $this->error("Ошибка при закрытии задачи с ID {$taskId}. Ответ API: " . $response->body());
//                }
//            } else {
//                $this->info("Задача с ID {$taskId} уже закрыта.");
//            }
//        } else {
//            $this->error("Ошибка при получении задачи с ID {$taskId}. Ответ API: " . $response->body());
//        }
//    }


}
