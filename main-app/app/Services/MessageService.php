<?php

namespace App\Services;

use App\Models\Message;
use Exception;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class MessageService
{
    protected $connection;
    protected $channel;

    protected Message $message;

    /**
     * @throws Exception
     */
    public function __construct()
    {
        $this->message = new Message();
        $this->setupRabbitMQ();
    }

    /**
     * @throws Exception
     */
    private function setupRabbitMQ(): void
    {
        $this->connection = new AMQPStreamConnection(
            env('RABBITMQ_HOST', '127.0.0.1'),
            env('RABBITMQ_PORT', 5672),
            env('RABBITMQ_USER', 'guest'),
            env('RABBITMQ_PASSWORD', 'guest')
        );
        $this->channel = $this->connection->channel();
        $this->channel->queue_declare('messages_queue', false, true, false, false);
    }


    public function sendMessage(array $data): array
    {

        if ($data['type'] === Message::TYPE_AUDIO) {
            $message = $this->message->create([
                'type' => $data['type'],
                'audio_link' => 'http://audio-link.com/'. time() . '.mp3'
            ]);
            $amqpMessage = new AMQPMessage(json_encode($message), ['delivery_mode' => 2]);
            $this->channel->basic_publish($amqpMessage, '', 'messages_queue');
        }

        return ['message' => 'Message sent successfully'];
    }

    public function getMessage(int $id): array
    {
        $message = $this->message->find($id);
        return ['message' => 'Message retrieved successfully', 'data' => $message];
    }

    public function __destruct()
    {
        $this->channel->close();
        $this->connection->close();
    }
}
