<?php

namespace App\Controller\Post;

use App\Entity\Bookmark;
use App\Entity\Post;
use App\Entity\User;
use App\Repository\BookmarkRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Serializer\SerializerInterface;

#[AsController]
class ToggleBookmarkAction
{
    public function __invoke(
        Post $post,
        Request $request,
        EntityManagerInterface $em,
        SerializerInterface $serializer,
        BookmarkRepository $bookmarkRepository
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

        // ¿Ya existe el bookmark?
        $existingBookmark = $bookmarkRepository->findOneBy([
            'user' => $user,
            'post' => $post,
        ]);

        if ($existingBookmark) {
            // UNBOOKMARK: eliminar bookmark y decrementar contador
            $em->remove($existingBookmark);
            $post->incrementBookmarksCount(-1);
        } else {
            // BOOKMARK: crear bookmark y sumar contador
            $bookmark = new Bookmark();
            $bookmark->setUser($user);
            $bookmark->setPost($post);
            $em->persist($bookmark);
            $post->incrementBookmarksCount(1);
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