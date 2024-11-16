<?php

namespace App\Entity;

use App\Repository\MessageRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MessageRepository::class)]
#[ORM\Table(name: 'messages')]
class Message
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $message_id = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $state_id = null;

    #[ORM\Column]
    private ?bool $is_bot = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $first_name = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $last_name = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $text = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $language_code = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updated_at = null;

    #[ORM\ManyToOne(inversedBy: 'messages', cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Chat $chat = null;

    const STATE_START = 0;
    const STATE_SUCCESS = 1;
    
    public function __construct()
    {
        $this->setCreatedAt(new \DateTimeImmutable('now'));
        $this->setUpdatedAt(new \DateTimeImmutable('now'));
        $this->setStateId(self::STATE_START);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMessageId(): ?int
    {
        return $this->message_id;
    }

    public function setMessageId(int $message_id): static
    {
        $this->message_id = $message_id;

        return $this;
    }

    public function getStateId(): ?int
    {
        return $this->state_id;
    }

    public function setStateId(int $state_id): static
    {
        $this->state_id = $state_id;

        return $this;
    }

    public function isBot(): ?bool
    {
        return $this->is_bot;
    }

    public function setIsBot(bool $is_bot): static
    {
        $this->is_bot = $is_bot;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->first_name;
    }

    public function setFirstName(?string $first_name): static
    {
        $this->first_name = $first_name;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->last_name;
    }

    public function setLastName(?string $last_name): static
    {
        $this->last_name = $last_name;

        return $this;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(string $text): static
    {
        $this->text = $text;

        return $this;
    }

    public function getLanguageCode(): ?string
    {
        return $this->language_code;
    }

    public function setLanguageCode(?string $language_code): static
    {
        $this->language_code = $language_code;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(?\DateTimeImmutable $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updated_at): static
    {
        $this->updated_at = $updated_at;

        return $this;
    }

    public function getChat(): ?Chat
    {
        return $this->chat;
    }

    public function setChat(?Chat $chat): static
    {
        $this->chat = $chat;

        return $this;
    }
}
