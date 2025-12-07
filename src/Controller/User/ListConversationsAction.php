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
class ListConversationsAction
{
    public function __invoke(
        int $id,
        EntityManagerInterface $em,
        SerializerInterface $serializer
    ): JsonResponse {
        /** @var User|null $user */
        $user = $em->getRepository(User::class)->find($id);

        if (!$user) {
            throw new NotFoundHttpException('User not found');
        }

        // Obtenemos todos los mensajes donde participa el usuario
        $messages = $em->getRepository(Message::class)->createQueryBuilder('m')
            ->where('m.sender = :user OR m.receiver = :user')
            ->setParameter('user', $user)
            ->orderBy('m.createdAt', 'DESC')
            ->getQuery()
            ->getResult();

        // Calculamos los "otros" usuarios con los que ha hablado
        $conversors = [];
        /** @var Message $msg */
        foreach ($messages as $msg) {
            $other = $msg->getSender() === $user ? $msg->getReceiver() : $msg->getSender();
            $conversors[$other->getId()] = $other; // clave por id para evitar duplicados
        }

        $others = array_values($conversors);

        $json = $serializer->serialize(
            $others,
            'json',
            ['groups' => ['user:read']]
        );

        return new JsonResponse($json, 200, [], true);
    }
}
