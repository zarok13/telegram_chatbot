<?php

namespace App\Service\Interface;

interface TelegramChatbotServiceInterface
{
    public function getUpdate(): array;
    
    public function sendMessage(int $chatId, string $text, string $parseMode): array;
}
