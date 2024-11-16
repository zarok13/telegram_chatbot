<?php

namespace App\Tests\Integration;

use App\Entity\Chat;
use App\Entity\Message;
use App\Service\Interface\TelegramChatbotServiceInterface;
use App\Tests\DatabaseTestCase;

/**
 * @group integration
 */
class TelegramChatbotServiceTest extends DatabaseTestCase
{
    private  $telegramChatbotService;
    protected function setUp(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        $this->entityManager = $container->get('doctrine')->getManager();
        $this->telegramChatbotService = $container->get(TelegramChatbotServiceInterface::class);
    }

    public function testGetUpdateResponsing()
    {
        $response = $this->telegramChatbotService->getUpdate();
        $this->assertIsArray($response);
        $this->assertTrue(true, $response['ok']);
    }

    /**
     * @dataProvider messageProvider
     */
    public function testMessageCanBeReplied(int $update_id, array $message) {
        $newMessage = new Message();
        $newMessage->setMessageId($message['message_id']);
        $newMessage->setText('/start');
        $newMessage->setIsBot($message['from']['is_bot']);
        $newMessage->setFirstName($message['from']['first_name']);
        $newMessage->setLastName($message['from']['last_name']);
        $newMessage->setLanguageCode($message['from']['language_code']);
        $newChat = new Chat();
        $newChat->setChatId($message['chat']['id']);
        $newChat->setType($message['chat']['type']);
        $newMessage->setChat($newChat);
        $response = $this->telegramChatbotService->reply($newMessage);
        $this->assertEquals(0, $response['error']);
        $this->assertEquals('success', $response['message']);
        $this->assertCount(1, $response['replies']);
        $reply = $response['replies'][0];
        $this->assertTrue($reply['ok']);
        $this->assertSame('Welcome new user!', $reply['result']['text']);

        $newMessage->setText('test');
        $response = $this->telegramChatbotService->reply($newMessage);
        $reply = $response['replies'][0];
        $this->assertTrue($reply['ok']);
        $this->assertSame('incorrect code number', $reply['result']['text']);
        $this->assertEquals(0, $newMessage->getStateId());

        $newMessage->setText('123');
        $response = $this->telegramChatbotService->reply($newMessage);
        $reply1 = $response['replies'][0];
        $reply2 = $response['replies'][1];
        $this->assertTrue($reply1['ok']);
        $this->assertSame('Your code will be verified soon', $reply1['result']['text']);
        $this->assertTrue($reply2['ok']);
        $this->assertSame('Code is correct!', $reply2['result']['text']);
        $messageRepository = $this->entityManager->getRepository(Message::class);
        $newMessage = $messageRepository->findOneBy(['message_id' => $message['message_id']]);
        $this->assertInstanceOf(Message::class, $newMessage);
        $this->assertEquals(1, $newMessage->getStateId());
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
                        "first_name" => "Name",
                        "last_name" => "Surname",
                        "language_code" => "en"
                    ],
                    "chat" => [
                        "id" => 1250686964,
                        "first_name" => "Name",
                        "last_name" => "Surname",
                        "type" => "private"
                    ],
                    "date" => 1726421138,
                    "text" => '',
                ],
            ]
        ];
    }
}
