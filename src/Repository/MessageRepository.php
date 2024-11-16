<?php

namespace App\Repository;

use App\Entity\Message;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Message>
 */
class MessageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Message::class);
    }

    public function findOrCreate(Message $message){

        $query = $this->createQueryBuilder('m')
            ->select('m')
            ->innerJoin('m.chat', 'c')
            ->andWhere('m.message_id = :message_id')
            ->andWhere('c.chat_id = :chat_id')
            ->setParameter('chat_id', $message->getChat()->getChatId())
            ->setParameter('message_id', $message->getMessageId())
            ->orderBy('m.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
        ;
        $result = $query->getOneOrNullResult();
        if(empty($result)){
            $this->getEntityManager()->persist($message);
            $this->getEntityManager()->flush();
            $result = $message;
        }
        return $result;
    }
}
