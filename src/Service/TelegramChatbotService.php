<?php

namespace App\Service;

use App\Entity\Chat;
use App\Entity\Message;
use App\Service\Interface\TelegramChatbotServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

final class TelegramChatbotService implements TelegramChatbotServiceInterface
{
    private LoggerInterface $logger;
    private ParameterBagInterface $parameters;
    private HttpClientInterface $client;
    private EntityManagerInterface $entityManager;
    private string $baseUrl = 'https://api.telegram.org/bot';
    private const PARSEMODE_HTML = 'HTML';
    private const PARSEMODE_MARKDOWN = 'MarkdownV2';
    public function __construct(
        LoggerInterface $telegramLogger,
        ParameterBagInterface $parameterBug,
        HttpClientInterface $client,
        EntityManagerInterface $entityManager,
    ) {
        $this->logger = $telegramLogger;
        $this->parameters = $parameterBug;
        $this->client = $client;
        $this->entityManager = $entityManager;
    }
    public static int $test = 1;

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
                $chat = $this->entityManager->getRepository(Chat::class)->findOneBy(['chat_id' => $chatId]);
                if(!empty($chat)) {
                    $message->setChat($chat);
                }
                $message = $this->entityManager->getRepository(Message::class)->findOrCreate($message);
                if($message->getStateId() == Message::STATE_START) {
                    if(!preg_match('/^\d+$/', $text)){
                        $return['replies'][] = $this->sendMessage($chatId, 'incorrect code number');
                        break; 
                    }
                    $return['replies'][] = $this->sendMessage($chatId, 'Your code will be verified soon');
                    sleep(3);
                    $return['replies'][] = $message->setStateId(Message::STATE_SUCCESS);
                    $this->entityManager->persist($message);
                    $this->entityManager->flush();
                    $this->sendMessage($chatId, 'Code is correct!');
                } else {
                    if(!empty($message)) {
                        $return['replies'][] = $this->sendMessage($chatId, 'You edited: ' . $text);
                    }
                }
                break;
        }
        return $return;
    }

    public function getUpdate(): array
    {
        $return = ['error' => 0, 'message' => 'success'];
        $params['json'] = [
            'offset' => -1, //-1 last message only,
            'limit' => null,
            'timeout' => null,
            'allowed_updates' => [],
        ];
        $response = $this->post('getUpdates', $params);
        $statusCode = $response->getStatusCode();
        if ($statusCode != 200) {
            $this->logger->debug('TelegramChatbotService:getUpdate Unable to get updates');
            $content = $response->getContent();
            if (is_array($content)) {
                $this->logger->error('TelegramChatbotService:getUpdate $content error:', ['data' => $content]);
            }
            $return = ['error' => 1, 'message' => 'failure', 'status_code' => $statusCode];
            return $return;
        }
        $response = json_decode($response->getContent(), true);
        if (!empty($response['ok']) && !empty($response['result'])) { // update confirmation
            $params['json']['offset'] = $response['result'][0]['update_id'] + 1;
            $this->post('getUpdates', $params);
        }
        if(!empty($response['result'][0])) {
            return $response['result'][0];
        }
        return [];
    }

    public function sendMessage(int $chatId, string $text, string $parseMode = self::PARSEMODE_MARKDOWN): array
    {
        $return = ['error' => 0, 'message' => 'success'];
        if (empty($chatId)) {
            $this->logger->debug('TelegramChatbotService:sendMessage Warning: \'chatId\' is empty or different!');
            $return['error'] = 1;
            $return['message'] = '\'chatId\' is empty or different!';
            return $return;
        }
        if (empty($text)) {
            $this->logger->debug('TelegramChatbotService:sendMessage Warning: \'text\' is empty!');
            $return['error'] = 1;
            $return['message'] = '\'text\' is empty!';
            return $return;
        }
        if ($parseMode == self::PARSEMODE_HTML) {
            $text = strip_tags($text, 'p, span, a, b, i, u');
        }
        $params['json'] = [
            'chat_id' => strval($chatId),
            'text' => $text,
            //'parse_mode' => self::PARSEMODE_MARKDOWN //'Markdown' //$parseMode,
        ];
        $response = $this->post('sendMessage', $params);
        $statusCode = $response->getStatusCode();
        if ($statusCode != 200) {
            $this->logger->debug('TelegramChatbotService:sendMessage Unable to send message, chat_id={' . $chatId . '} status_code: ' . $statusCode, ['data' => $response]);
            $content = $response->getContent();
            if (is_array($content)) {
                $this->logger->error('TelegramChatbotService:sendMessage $content error:', ['data' => $content]);
            }
        }
        $return = json_decode($response->getContent(), true);
        return $return;
    }

    private function post($endpoint, $params): ResponseInterface
    {
        $apiToken = $this->parameters->get('app.telegram_token');
        if (empty($apiToken)) {
            $this->logger->debug('TelegramChatbotService:post Warning: \'telegram_token\' is empty!');
            return ['error' => 1, 'message' => 'failure'];
        }
        $response = $this->client->request(
            'POST',
            $this->baseUrl . $apiToken . '/' . $endpoint,
            $params
        );
        return $response;
    }
}
