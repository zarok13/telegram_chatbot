<?php

namespace App\Controller;

use App\Denormalizer\MessageDenormalizer;
use App\Entity\Chat;
use App\Entity\Message;
use App\Service\TelegramChatbotService;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Serializer;


class MessagesController extends AbstractController
{
    private LoggerInterface $logger;

    public function __construct(
        LoggerInterface $telegramLogger,
    ) {
        $this->logger = $telegramLogger;
    }

    #[Route('/telegram/reply', name: 'app_chatbot_reply')]
    public function chatbotReply(TelegramChatbotService $telegramChatbotService, MessageDenormalizer $denormalizer): JsonResponse
    {
        $results = $telegramChatbotService->getUpdate();
        $this->logger->debug('MessagesController:chatbotReply $message', ['data' => $results]);
        if (empty($results)) {
            return $this->json(['no updates']);
        }
        $serializer = new Serializer([$denormalizer]);
        $message = $serializer->denormalize($results, Message::class);
        $chat = $serializer->denormalize($results, Chat::class);
        $message->setChat($chat);
        try {
            $telegramChatbotService->reply($message);
        } catch (\Throwable | \Error $e) {
            $this->logger->debug('MessagesController:chatbotReply catch ' . $e->getMessage() . ': ' . $e->getFile() . ': ' . $e->getLine());
        }
        return $this->json([]);
    }
}
