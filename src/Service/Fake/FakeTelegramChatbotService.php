<?php

namespace App\Service\Fake;

use App\Entity\Message;
use App\Service\Interface\TelegramChatbotServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;

class FakeTelegramChatbotService implements TelegramChatbotServiceInterface
{
    private EntityManagerInterface $entityManager;
    protected static $content = '';
    protected static $statusCode = 200;

    public function __construct(
        EntityManagerInterface $entityManager,
    ) {
        $this->entityManager = $entityManager;
        $metaData = $this->entityManager->getMetadataFactory()->getAllMetadata();
        $schemaTool = new SchemaTool($this->entityManager);
        $schemaTool->updateSchema($metaData);
    }

    /**
     * simulates update receiving
     *
     * @return array
     */
    public function getUpdate(): array
    {
        self::$content = '{"ok":true,"result":{"message_id":320,"from":{"id":1250686964,"is_bot":true,"first_name":"zarok_bot","username":"zarok13_bot"},"chat":{"id":1250686964,"first_name":"Zarok","last_name":"Tec","type":"private"},"date":1731360574,"text":"Welcome new user!"}}';

        return json_decode(self::$content, true);
    }

    /**
     * simulates sending message
     *
     * @param integer $chatId
     * @param string $text
     * @param string|null $parseMode
     * @return array
     */
    public function sendMessage(int $chatId, string $text, string $parseMode = null): array
    {
        $return = ['error' => 0, 'message' => 'success'];
        if (empty($chatId)) {
            $return['error'] = 1;
            $return['message'] = '\'chatId\' is empty or different!';
            return $return;
        }
        if (empty($text)) {
            $return['error'] = 1;
            $return['message'] = '\'text\' is empty!';
            return $return;
        }
        self::$content = '{"ok":true,"result":{"message_id":320,"from":{"id":' . $chatId . ',"is_bot":true,"first_name":"zarok_bot","username":"zarok13_bot"},"chat":{"id":' . $chatId . ',"first_name":"Zarok","last_name":"Tec","type":"private"},"date":1731360574,"text":"' . $text . '"}}';
        if (self::$statusCode != 200) {
            return ['error' => 1, 'message' => 'failure'];
        }
        $return = json_decode(self::$content, true);
        return $return;
    }

    public function reply(Message $message): array
    {
        $return = ['error' => 0, 'message' => 'success'];
        $chatId = $message->getChat()->getChatId();
        $text = $message->getText();
        switch ($text) {
            case '/start':
                $return['replies'][] = $this->sendMessage($chatId, 'Welcome new user!');
                break;
            default:
                $message = $this->entityManager->getRepository(Message::class)->findOrCreate($message);
                if($message->getStateId() == Message::STATE_START) {
                    if(!preg_match('/^\d+$/', $text)){
                        $return['replies'][] = $this->sendMessage($chatId, 'incorrect code number');
                        break; 
                    }
                    $return['replies'][] = $this->sendMessage($chatId, 'Your code will be verified soon');
                    $message->setStateId(Message::STATE_SUCCESS);
                    $this->entityManager->persist($message);
                    $this->entityManager->flush();
                    $return['replies'][] = $this->sendMessage($chatId, 'Code is correct!');
                } else {
                    if(!empty($message)) {
                        $return['replies'][] = $this->sendMessage($chatId, 'You sent: ' . $text);
                    }
                }
                break;
        }
        return $return;
    }
}
