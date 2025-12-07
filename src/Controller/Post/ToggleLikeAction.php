<?php

namespace App\Controller\Post;

use App\Entity\Post;
use App\Entity\PostLike;
use App\Entity\User;
use App\Repository\PostLikeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Serializer\SerializerInterface;

#[AsController]
class ToggleLikeAction
{
    public function __invoke(
        Post $post,
        Request $request,
        EntityManagerInterface $em,
        SerializerInterface $serializer,
        PostLikeRepository $postLikeRepository
    ): JsonResponse {
        // De momento pillamos el userId por query param (?userId=1)
        // Más adelante esto vendrá del usuario autenticado (Security).
        $userId = (int) $request->query->get('userId');

        if ($userId <= 0) {
            // En un proyecto real lanzarías una excepción HTTP 400
            throw new \InvalidArgumentException('Missing or invalid userId');
        }

        /** @var User|null $user */
        $user = $em->getRepository(User::class)->find($userId);

        if (!$user) {
            throw new \InvalidArgumentException('User not found');
        }

        // ¿Ya existe el like?
        $existingLike = $postLikeRepository->findOneBy([
            'user' => $user,
            'post' => $post,
        ]);

        if ($existingLike) {
            // UNLIKE: eliminar like y decrementar contador
            $em->remove($existingLike);
            $post->incrementLikesCount(-1);
        } else {
            // LIKE: crear like y sumar contador
            $like = new PostLike();
            $like->setUser($user);
            $like->setPost($post);
            $em->persist($like);
            $post->incrementLikesCount(1);
        }

        $em->flush();

        // Serializamos el Post usando los grupos de API Platform
        $json = $serializer->serialize(
            $post,
            'json',
            ['groups' => ['post:read']]
        );

        // El último parámetro `true` indica que $json YA es JSON
        return new JsonResponse($json, 200, [], true);
    }
}
