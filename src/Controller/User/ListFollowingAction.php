<?php

namespace App\Controller\User;

use App\Entity\Follow;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\SerializerInterface;

#[AsController]
class ListFollowingAction
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

        // Buscamos Follow donde follower = este user
        $follows = $em->getRepository(Follow::class)->createQueryBuilder('f')
            ->where('f.follower = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();

        // Extraemos solo los followed (users)
        $following = array_map(
            fn (Follow $f) => $f->getFollowed(),
            $follows
        );

        $json = $serializer->serialize(
            $following,
            'json',
            ['groups' => ['user:read']]
        );

        return new JsonResponse($json, 200, [], true);
    }
}
