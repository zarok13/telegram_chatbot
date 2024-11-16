<?php

namespace App\Denormalizer;

use App\Entity\Chat;
use App\Entity\Message;
use Exception;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerInterface;

class MessageDenormalizer implements DenormalizerInterface, SerializerAwareInterface
{
    public function __construct(
        private DenormalizerInterface $denormalizer,
    ) {}

    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): mixed
    {
        $new_data = [];
        if (!empty($data['message']) && is_array($data['message'])) {
            $data = $data['message'];
        } elseif (!empty($data['edited_message']) && is_array($data['edited_message'])) {
            $data = $data['edited_message'];
        } else {
            throw new Exception('Unknow data');
        }
        if ($type == Message::class) {
            $new_data['message_id'] = $data['message_id'];
            $new_data['text'] = $data['text'];
            foreach ($data['from'] as $index => $value) {
                if ($index == 'id') {
                    continue;
                }
                $new_data[$index] = $value;
            }
        } elseif ($type == Chat::class) {
            if (isset($data['chat']) && is_array($data['chat'])) {
                $new_data = $data['chat'];
                $new_data['chat_id'] = $new_data['id'];
            }
        }
        $result = $this->denormalizer->denormalize($new_data, $type);
        return $result;
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return $this->denormalizer->supportsDenormalization($data, $type);
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Message::class => true,
            Chat::class => true,
            'object' => null,
        ];
    }

    public function setSerializer(SerializerInterface $serializer): void
    {
        if ($this->denormalizer instanceof SerializerAwareInterface) {
            $this->denormalizer->setSerializer($serializer);
        }
    }
}
