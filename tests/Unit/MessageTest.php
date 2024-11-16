<?php

namespace App\Tests\Unit;

use App\Entity\Chat;
use App\Entity\Message;
use App\Tests\DatabaseTestCase;

class MessageTest extends DatabaseTestCase
{
    public Message $message;
    
    public function testMessageCanBeCreatedWithParentChat() {
        $chat = new Chat();
        $chat->setChatId(4235345);
        $chat->setType('private');
        $message = new Message();
        $message->setMessageId(11);
        $message->setChat($chat);
        $message->setIsBot(false);
        $message->setFirstName('firstname');
        $message->setLastName('lastname');
        $message->setText('message test...');
        $message->setLanguageCode('en');
        $this->entityManager->persist($message);
        $this->entityManager->flush();
        $messageRepository = $this->entityManager->getRepository(Message::class);
        $messageRow = $messageRepository->findOneBy(['message_id' => 11]);
        $this->assertEquals('firstname', $messageRow->getFirstName());
        $this->assertEquals('lastname', $messageRow->getLastName());
        $this->assertEquals('message test...', $messageRow->getText());
        $this->assertEquals($chat, $messageRow->getChat());
        $this->assertEquals($chat->getChatId(), $messageRow->getChat()->getChatId());
        $this->assertInstanceOf(Chat::class, $message->getChat());
        $message2 = $message;
        $message2->setMessageId(12);
        $message2->setText('new message');
        $chatRepository = $this->entityManager->getRepository(Chat::class);
        $chatRow = $chatRepository->findOneBy(['chat_id' => 4235345]);
        $message2->setChat($chatRow);
        $this->entityManager->persist($message2);
        $this->entityManager->flush();
        $messageRow2 = $messageRepository->findOneBy(['message_id' => 12]);
        $this->assertEquals('new message', $messageRow2->getText());
        $this->assertEquals($chat, $messageRow->getChat());
        $this->assertEquals($messageRow->getChat()->getChatId(), $messageRow2->getChat()->getChatId());
    }

    /**
     * @dataProvider messageProvider
     */
    public function testApiMessageStructureIsCorrect(int $update_id, array $message): void
    {
        $this->assertIsArray($message);
        $this->assertArrayHasKey('message_id', $message);
        $this->assertArrayHasKey('from', $message);
        $this->assertIsInt($update_id);
        $this->assertIsInt($message['chat']['id']);
        $this->assertIsString($message['text']);
    }

    public static function messageProvider(): array
    {
        return [
            [
                "update_id" => 725612152,
                "message" => [
                    "message_id" => 261,
                    "from" => [
                        "id" => 1250686964,
                        "is_bot" => false,
                        "first_name" => "Zarok",
                        "last_name" => "Tec",
                        "language_code" => "en"
                    ],
                    "chat" => [
                        "id" => 1250686964,
                        "first_name" => "Zarok",
                        "last_name" => "Tec",
                        "type" => "private"
                    ],
                    "date" => 1726421138,
                    "text" => '123',
                ],
            ]
        ];
    }
}
