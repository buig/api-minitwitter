<?php

namespace App\Controller\Post;

use App\Entity\Post;
use App\Entity\Retweet;
use App\Entity\User;
use App\Repository\RetweetRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Serializer\SerializerInterface;

#[AsController]
class ToggleRetweetAction
{
    public function __invoke(
        Post $post,
        Request $request,
        EntityManagerInterface $em,
        SerializerInterface $serializer,
        RetweetRepository $retweetRepository
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

        // ¿Ya existe el retweet?
        $existingRetweet = $retweetRepository->findOneBy([
            'user' => $user,
            'post' => $post,
        ]);

        if ($existingRetweet) {
            // UNRETWEET: eliminar retweet y decrementar contador
            $em->remove($existingRetweet);
            $post->incrementRetweetsCount(-1);
        } else {
            // RETWEET: crear retweet y sumar contador
            $retweet = new Retweet();
            $retweet->setUser($user);
            $retweet->setPost($post);
            $em->persist($retweet);
            $post->incrementRetweetsCount(1);
        }

        $em->flush();

        // Devolvemos el Post; API Platform lo serializa con grupo post:read
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