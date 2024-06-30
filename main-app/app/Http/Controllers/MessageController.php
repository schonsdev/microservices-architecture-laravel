<?php

namespace App\Http\Controllers;

use App\Models\Message;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Services\MessageService;

class MessageController extends Controller
{
    public function sendMessage(Request $request): JsonResponse
    {
        $request->validate([
            'type' => 'required|in:text,audio',
            'message' => 'required_if:type,text',
            'audio_file' => 'required_if:type,audio'
        ]);

        $data = [
            'type' => $request->type,
            'message' => $request->message ?? null,
            'audio_link' => $request->audio_file ?? null
        ];

        $messageService = new MessageService();
        $messageService->sendMessage($data);

        return response()->json([
            'message' => 'Message sent successfully'
        ]);



    }

    public function getMessage($id): JsonResponse
    {
        $messageService = new MessageService();
        $message = $messageService->getMessage($id);

        return response()->json($message);
    }
}
