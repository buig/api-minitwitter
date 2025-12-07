<?php

namespace App\Controller\Hashtag;

use App\Entity\Hashtag;
use App\Entity\Post;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\SerializerInterface;

#[AsController]
class HashtagPostsAction
{
    public function __invoke(
        int $id,
        EntityManagerInterface $em,
        SerializerInterface $serializer
    ): JsonResponse {
        $hashtag = $em->getRepository(Hashtag::class)->find($id);

        if (!$hashtag) {
            throw new NotFoundHttpException('Hashtag not found');
        }

        $qb = $em->createQueryBuilder();
        $qb->select('p')
            ->from(Post::class, 'p')
            ->join('p.postHashtags', 'ph')
            ->where('ph.hashtag = :hashtag')
            ->setParameter('hashtag', $hashtag)
            ->orderBy('p.createdAt', 'DESC');

        $posts = $qb->getQuery()->getResult();

        $json = $serializer->serialize(
            $posts,
            'json',
            ['groups' => ['post:read']]
        );

        return new JsonResponse($json, 200, [], true);
    }
}
