<?php

namespace App\Controller\User;

use App\Entity\Message;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\SerializerInterface;

#[AsController]
class GetConversationMessagesAction
{
    public function __invoke(
        int $id,
        int $otherId,
        EntityManagerInterface $em,
        SerializerInterface $serializer
    ): JsonResponse {
        /** @var User|null $user */
        $user = $em->getRepository(User::class)->find($id);
        /** @var User|null $other */
        $other = $em->getRepository(User::class)->find($otherId);

        if (!$user || !$other) {
            throw new NotFoundHttpException('User not found');
        }

        $qb = $em->getRepository(Message::class)->createQueryBuilder('m');
        $qb->where('(m.sender = :user AND m.receiver = :other) OR (m.sender = :other AND m.receiver = :user)')
            ->setParameter('user', $user)
            ->setParameter('other', $other)
            ->orderBy('m.createdAt', 'ASC');

        $messages = $qb->getQuery()->getResult();

        $json = $serializer->serialize(
            $messages,
            'json',
            ['groups' => ['message:read']]
        );

        return new JsonResponse($json, 200, [], true);
    }
}
