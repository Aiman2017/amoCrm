<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Dflydev\DotAccessData\Data;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class AmoController extends Controller
{
    private $accessToken;
    private $baseUrl;
    public function __construct()
    {
        $this->accessToken = config('amocrm.access_token');
        $this->baseUrl = config('amocrm.base_url');
    }

    public function index()
    {
        return view('contacts.index');
    }

    public function getContactsWithoutDeals(): JsonResponse
    {
        if (!request()->ajax()) {
            abort(Response::HTTP_FORBIDDEN);
        }
        $response = Http::withToken($this->accessToken)
            ->get($this->baseUrl.'/api/v4/contacts', [
                'with' => 'leads',
                'text' => 'Контакт без сделок',
            ]);
        $contacts = $response->json()['_embedded']['contacts'] ?? [];

        return response()->json([
            'message' => 'Данные получены',
            'contacts' => $contacts
        ]);
    }
    public function createTask(Request $request): JsonResponse
    {
        $contactId = (int)$request->contact_id;

        $response = Http::withToken($this->accessToken)
            ->post($this->baseUrl.'/api/v4/tasks', [
                [
                    'entity_id' => $contactId,
                    'entity_type' => 'contacts',
                    'text' => 'Контакт без сделок',
                    'complete_till' => now()->addDay()->timestamp,
                ]
            ]);
        if ($response->failed()) {
            return response()->json(['error' => 'Ошибка при создании задачи в AmoCRM', 'details' => $response->json()],
                500);
        }
        Log::info('AmoCRM Response:', $response->json());
        return response()->json($response->json([
            'message' => 'сообщение создано',
        ]));
    }
}
