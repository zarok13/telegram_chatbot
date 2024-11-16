<?php

namespace App\Tests\Unit;

use App\Denormalizer\MessageDenormalizer;
use App\Entity\Chat;
use App\Entity\Message;
use Monolog\Test\TestCase;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

class MessageDenormalizerTest extends TestCase
{
    public SerializerInterface $serializer;
    public MessageDenormalizer $denormalizer;
    protected function setUp(): void
    {
        parent::setUp();
        $this->denormalizer = new MessageDenormalizer(new ObjectNormalizer());
        $this->serializer = new Serializer([$this->denormalizer]);
    }

    public function testDenormalizationSupported(): void {
        $supported = $this->serializer->supportsDenormalization([], Message::class);
        $this->assertTrue($supported);
        $supported = $this->serializer->supportsDenormalization([], Chat::class);
        $this->assertTrue($supported);
        $supported = $this->serializer->supportsDenormalization([], 'object');
        $this->assertFalse($supported);
    }

    public function testDenormalizationSupportedTypes(): void {
        $supportedTypes = $this->denormalizer->getSupportedTypes('*');
        $this->assertArrayHasKey(Message::class, $supportedTypes);
        $this->assertTrue($supportedTypes[Message::class]);
        $this->assertArrayHasKey(Chat::class, $supportedTypes);
        $this->assertTrue($supportedTypes[Chat::class]);
        $this->assertArrayHasKey('object', $supportedTypes);
        $this->assertNull($supportedTypes['object']);
    }

    /**
     * @dataProvider messageProvider
     */
    public function testDenormalization(int $update_id, array $message): void
    {
        $messageData = $message;
        $message = $this->serializer->denormalize(['message' => $messageData], Message::class);
        $this->assertInstanceOf(Message::class, $message);
        $this->assertEquals(261, $message->getMessageId());
        $this->assertEquals('123', $message->getText());
        $this->assertEquals('Tec', $message->getLastName());
        $this->assertEquals(false, $message->isBot());
        $chat = $this->serializer->denormalize(['message' => $messageData], Chat::class);
        $this->assertInstanceOf(Chat::class, $chat);
        $this->assertNotInstanceOf(Chat::class, $message->getChat());
        $message->setChat($chat);
        $this->assertInstanceOf(Chat::class, $message->getChat());
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
