<?php

namespace App\DataFixtures;

use App\Entity\Follow;
use App\Entity\User;
use App\Entity\Post;
use App\Entity\PostLike;
use App\Entity\Bookmark;
use App\Entity\Retweet;
use App\Entity\Hashtag;
use App\Entity\PostHashtag;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('es_ES');

        $users = [];
        // --- Crear usuarios ---
        for ($i = 1; $i <= 20; $i++) {
            $user = new User();
            $user->setUsername($faker->userName());
            $user->setEmail($faker->unique()->safeEmail());
            $user->setPasswordHash(password_hash('123456', PASSWORD_BCRYPT));
            $user->setName($faker->name());
            $user->setBio($faker->sentence(8));
            $user->setAvatarUrl("https://i.pravatar.cc/150?u={$i}");
            $manager->persist($user);
            $users[] = $user;
        }

        // --- Crear hashtags ---
        $hashtags = [];
        $tags = ['dev', 'symfony', 'api', 'opensource', 'fun', 'coding', 'react', 'docker', 'ai', 'cloud'];
        foreach ($tags as $tag) {
            $hashtag = new Hashtag();
            $hashtag->setTag($tag);
            $manager->persist($hashtag);
            $hashtags[] = $hashtag;
        }

        // --- Crear posts ---
        $posts = [];
        for ($i = 1; $i <= 100; $i++) {
            $post = new Post();
            $post->setUser($faker->randomElement($users));
            $post->setContent($faker->realTextBetween(60, 200));
            $post->setCreatedAt(\DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-1 month', 'now')));
            $post->setLikesCount(0);
            $post->setRetweetsCount(0);
            $post->setBookmarksCount(0);
            $post->setRepliesCount(0);
            $manager->persist($post);
            $posts[] = $post;

            // vincular 0–3 hashtags
            foreach ($faker->randomElements($hashtags, rand(0, 3)) as $tag) {
                $ph = new PostHashtag();
                $ph->setPost($post);
                $ph->setHashtag($tag);
                $manager->persist($ph);
            }
        }

        // --- Marcar algunos posts como replies a otros ---
        $replyProbability = 0.3; // 30% de los posts serán respuesta

        foreach ($posts as $post) {
            if ($faker->boolean($replyProbability * 100)) {
                // Elegimos un posible post padre distinto del propio
                $possibleParents = array_filter(
                    $posts,
                    fn (Post $p) => $p !== $post && $p->getCreatedAt() <= $post->getCreatedAt()
                );

                if (empty($possibleParents)) {
                    continue;
                }

                /** @var Post $parent */
                $parent = $faker->randomElement($possibleParents);

                $post->setInReplyTo($parent);
                $parent->incrementRepliesCount();
            }
        }

        // --- Crear likes sin duplicados (user_id, post_id) ---
        $maxLikes = 200;
        $createdLikes = 0;
        $likePairs = [];

        while ($createdLikes < $maxLikes) {
            // elegimos índices para poder construir una clave estable
            $userIndex = array_rand($users);
            $postIndex = array_rand($posts);

            $key = $userIndex . '-' . $postIndex;

            if (isset($likePairs[$key])) {
                // ya existe este (user, post) → saltar
                continue;
            }

            $likePairs[$key] = true;

            $like = new PostLike();
            $user = $users[$userIndex];
            $post = $posts[$postIndex];

            $like->setUser($user);
            $like->setPost($post);
            $post->incrementLikesCount();

            $manager->persist($like);
            $createdLikes++;
        }

        // --- Crear retweets sin duplicados ---
        $maxRetweets = 80;
        $createdRetweets = 0;
        $retweetPairs = [];

        while ($createdRetweets < $maxRetweets) {
            $userIndex = array_rand($users);
            $postIndex = array_rand($posts);
            $key = $userIndex . '-' . $postIndex;

            if (isset($retweetPairs[$key])) {
                continue;
            }

            $retweetPairs[$key] = true;

            $rt = new Retweet();
            $user = $users[$userIndex];
            $post = $posts[$postIndex];

            $rt->setUser($user);
            $rt->setPost($post);
            $post->incrementRetweetsCount();

            $manager->persist($rt);
            $createdRetweets++;
        }

        // --- Crear bookmarks sin duplicados ---
        $maxBookmarks = 120;
        $createdBookmarks = 0;
        $bookmarkPairs = [];

        while ($createdBookmarks < $maxBookmarks) {
            $userIndex = array_rand($users);
            $postIndex = array_rand($posts);
            $key = $userIndex . '-' . $postIndex;

            if (isset($bookmarkPairs[$key])) {
                continue;
            }

            $bookmarkPairs[$key] = true;

            $bm = new Bookmark();
            $user = $users[$userIndex];
            $post = $posts[$postIndex];

            $bm->setUser($user);
            $bm->setPost($post);
            $post->incrementBookmarksCount();

            $manager->persist($bm);
            $createdBookmarks++;
        }

        // --- Crear follows sin duplicados y sin auto-follow ---
        $maxFollows = 100;
        $createdFollows = 0;
        $followPairs = [];

        while ($createdFollows < $maxFollows) {
            $followerIndex = array_rand($users);
            $followedIndex = array_rand($users);

            // no te sigas a ti mismo
            if ($followerIndex === $followedIndex) {
                continue;
            }

            $key = $followerIndex . '-' . $followedIndex;

            if (isset($followPairs[$key])) {
                // ya existe este par follower-followed
                continue;
            }

            $followPairs[$key] = true;

            $follow = new Follow();
            $follow->setFollower($users[$followerIndex]);
            $follow->setFollowed($users[$followedIndex]);

            $manager->persist($follow);
            $createdFollows++;
        }

        $manager->flush();
    }
}
