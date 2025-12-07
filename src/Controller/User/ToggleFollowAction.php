<?php

namespace App\Controller\User;

use App\Entity\Follow;
use App\Entity\User;
use App\Repository\FollowRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

#[AsController]
class ToggleFollowAction
{
    public function __invoke(
        int $id, // usuario objetivo (al que se sigue/deja de seguir)
        Request $request,
        EntityManagerInterface $em,
        FollowRepository $followRepository
    ): JsonResponse {
        // Usuario que realiza la acción (de momento por query param ?userId=1)
        $followerId = (int) $request->query->get('userId');

        if ($followerId <= 0) {
            throw new \InvalidArgumentException('Missing or invalid userId');
        }

        if ($followerId === $id) {
            throw new \InvalidArgumentException('User cannot follow himself');
        }

        /** @var User|null $follower */
        $follower = $em->getRepository(User::class)->find($followerId);
        /** @var User|null $followed */
        $followed = $em->getRepository(User::class)->find($id);

        if (!$follower || !$followed) {
            throw new NotFoundHttpException('User not found');
        }

        // ¿Ya existe relación follow?
        $existing = $followRepository->findOneBy([
            'follower' => $follower,
            'followed' => $followed,
        ]);

        if ($existing) {
            // UNFOLLOW
            $em->remove($existing);
            $following = false;
        } else {
            // FOLLOW
            $follow = new Follow();
            $follow->setFollower($follower);
            $follow->setFollowed($followed);
            $em->persist($follow);
            $following = true;
        }

        $em->flush();

        return new JsonResponse([
            'followerId' => $follower->getId(),
            'followedId' => $followed->getId(),
            'following'  => $following,
        ]);
    }
}
