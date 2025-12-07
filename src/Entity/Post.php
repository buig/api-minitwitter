<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post as ApiPost;
use App\Controller\Post\ToggleBookmarkAction;
use App\Controller\Post\ToggleLikeAction;
use App\Controller\Post\ToggleRetweetAction;
use App\Repository\PostRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource(
    operations: [
        new Get(),            // GET /api/posts/{id}
        new GetCollection(),  // GET /api/posts
        // POST normal para crear posts
        new ApiPost(),           // POST /api/posts
        // POST custom para like/unlike
        new ApiPost(
            uriTemplate: '/posts/{id}/like',
            controller: ToggleLikeAction::class,
            read: true, // que nos inyecte el Post a partir del {id}
            deserialize: false, // no mapeamos el body a la entidad
            write: false, // no necesitamos deserializar JSON
            name: 'post_like'
        ),
        new ApiPost(
            uriTemplate: '/posts/{id}/bookmark',
            controller: ToggleBookmarkAction::class,
            read: true,
            deserialize: false,
            write: false,
            name: 'post_bookmark'
        ),
        new ApiPost(
            uriTemplate: '/posts/{id}/retweet',
            controller: ToggleRetweetAction::class,
            read: true,
            deserialize: false,
            write: false,
            name: 'post_retweet'
        ),
    ],
    normalizationContext: ['groups' => ['post:read']],
    denormalizationContext: ['groups' => ['post:write']],
)]
#[ORM\Entity(repositoryClass: PostRepository::class)]
class Post
{
    #[Groups(['post:read'])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Groups(['post:read', 'post:write'])]
    #[ORM\Column(length: 280)]
    private ?string $content = null;

    #[Groups(['post:read'])]
    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[Groups(['post:read'])]
    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[Groups(['post:read'])]
    #[ORM\Column]
    private ?int $likesCount = null;

    #[Groups(['post:read'])]
    #[ORM\Column]
    private ?int $retweetsCount = null;

    #[Groups(['post:read'])]
    #[ORM\Column]
    private ?int $bookmarksCount = null;

    #[Groups(['post:read'])]
    #[ORM\Column]
    private ?int $repliesCount = null;

    #[Groups(['post:read', 'post:write'])]
    #[ORM\ManyToOne(inversedBy: 'posts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[Groups(['post:read', 'post:write'])]
    #[ORM\ManyToOne(targetEntity: self::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?self $inReplyTo = null;

    #[Groups(['post:read', 'post:write'])]
    #[ORM\ManyToOne(targetEntity: self::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?self $repostOf = null;

    /**
     * @var Collection<int, PostLike>
     */
    #[ORM\OneToMany(targetEntity: PostLike::class, mappedBy: 'post', orphanRemoval: true)]
    private Collection $likes;

    /**
     * @var Collection<int, Retweet>
     */
    #[ORM\OneToMany(targetEntity: Retweet::class, mappedBy: 'post', orphanRemoval: true)]
    private Collection $retweets;

    /**
     * @var Collection<int, Bookmark>
     */
    #[ORM\OneToMany(targetEntity: Bookmark::class, mappedBy: 'post', orphanRemoval: true)]
    private Collection $bookmarks;

    #[ORM\OneToMany(targetEntity: PostHashtag::class, mappedBy: 'post', orphanRemoval: true)]
    private Collection $postHashtags;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->likesCount = 0;
        $this->retweetsCount = 0;
        $this->bookmarksCount = 0;
        $this->repliesCount = 0;
        $this->likes = new ArrayCollection();
        $this->retweets = new ArrayCollection();
        $this->bookmarks = new ArrayCollection();
        $this->postHashtags = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getLikesCount(): ?int
    {
        return $this->likesCount;
    }

    public function setLikesCount(int $likesCount): static
    {
        $this->likesCount = $likesCount;

        return $this;
    }

    public function incrementLikesCount(int $by = 1): void
    {
        $this->likesCount += $by;
    }

    public function getRetweetsCount(): ?int
    {
        return $this->retweetsCount;
    }

    public function setRetweetsCount(int $retweetsCount): static
    {
        $this->retweetsCount = $retweetsCount;

        return $this;
    }

    public function incrementRetweetsCount(int $by = 1): void
    {
        $this->retweetsCount += $by;
    }

    public function getBookmarksCount(): ?int
    {
        return $this->bookmarksCount;
    }

    public function setBookmarksCount(int $bookmarksCount): static
    {
        $this->bookmarksCount = $bookmarksCount;

        return $this;
    }

    public function incrementBookmarksCount(int $by = 1): void
    {
        $this->bookmarksCount += $by;
    }

    public function getRepliesCount(): ?int
    {
        return $this->repliesCount;
    }

    public function setRepliesCount(int $repliesCount): static
    {
        $this->repliesCount = $repliesCount;

        return $this;
    }

    public function incrementRepliesCount(int $by = 1): void
    {
        $this->repliesCount += $by;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getInReplyTo(): ?self
    {
        return $this->inReplyTo;
    }

    public function setInReplyTo(?self $inReplyTo): static
    {
        $this->inReplyTo = $inReplyTo;

        return $this;
    }

    public function getRepostOf(): ?self
    {
        return $this->repostOf;
    }

    public function setRepostOf(?self $repostOf): static
    {
        $this->repostOf = $repostOf;

        return $this;
    }

    /**
     * @return Collection<int, PostLike>
     */
    public function getLikes(): Collection
    {
        return $this->likes;
    }

    public function addLike(PostLike $like): static
    {
        if (!$this->likes->contains($like)) {
            $this->likes->add($like);
            $like->setPost($this);
        }

        return $this;
    }

    public function removeLike(PostLike $like): static
    {
        if ($this->likes->removeElement($like)) {
            // set the owning side to null (unless already changed)
            if ($like->getPost() === $this) {
                $like->setPost(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Retweet>
     */
    public function getRetweets(): Collection
    {
        return $this->retweets;
    }

    public function addRetweet(Retweet $retweet): static
    {
        if (!$this->retweets->contains($retweet)) {
            $this->retweets->add($retweet);
            $retweet->setPost($this);
        }

        return $this;
    }

    public function removeRetweet(Retweet $retweet): static
    {
        if ($this->retweets->removeElement($retweet)) {
            // set the owning side to null (unless already changed)
            if ($retweet->getPost() === $this) {
                $retweet->setPost(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Bookmark>
     */
    public function getBookmarks(): Collection
    {
        return $this->bookmarks;
    }

    public function addBookmark(Bookmark $bookmark): static
    {
        if (!$this->bookmarks->contains($bookmark)) {
            $this->bookmarks->add($bookmark);
            $bookmark->setPost($this);
        }

        return $this;
    }

    public function removeBookmark(Bookmark $bookmark): static
    {
        if ($this->bookmarks->removeElement($bookmark)) {
            // set the owning side to null (unless already changed)
            if ($bookmark->getPost() === $this) {
                $bookmark->setPost(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, PostHashtag>
     */
    public function getPostHashtags(): Collection
    {
        return $this->postHashtags;
    }

    public function addPostHashtag(PostHashtag $postHashtag): static
    {
        if (!$this->postHashtags->contains($postHashtag)) {
            $this->postHashtags->add($postHashtag);
            $postHashtag->setPost($this);
        }

        return $this;
    }

    public function removePostHashtag(PostHashtag $postHashtag): static
    {
        if ($this->postHashtags->removeElement($postHashtag)) {
            if ($postHashtag->getPost() === $this) {
                $postHashtag->setPost(null);
            }
        }

        return $this;
    }

    /**
     * Devuelve solo los hashtags (no los PostHashtag).
     */
    #[Groups(['post:read'])]
    public function getHashtags(): array
    {
        return array_map(
            fn (PostHashtag $ph) => $ph->getHashtag(),
            $this->postHashtags->toArray()
        );
    }
}
