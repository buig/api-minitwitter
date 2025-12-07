<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GetCollection as ApiGetCollection;
use App\Controller\Hashtag\HashtagPostsAction;
use App\Repository\HashtagRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource(
    operations: [
        new Get(),           // GET /api/hashtags/{id}
        new GetCollection(), // GET /api/hashtags
        new ApiGetCollection(
            uriTemplate: '/hashtags/{id}/posts',
            controller: HashtagPostsAction::class,
            read: false,
            deserialize: false,
            name: 'hashtag_posts'
        ),
    ],
    normalizationContext: ['groups' => ['hashtag:read']],
)]
#[ORM\Entity(repositoryClass: HashtagRepository::class)]
#[ORM\Table(name: 'hashtags')]
#[ORM\UniqueConstraint(
    name: 'uniq_hashtag_tag',
    columns: ['tag']
)]
class Hashtag
{
    #[Groups(['hashtag:read', 'post:read'])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    // guardamos sin # y en minúsculas
    #[Groups(['hashtag:read', 'post:read'])]
    #[ORM\Column(length: 100)]
    private ?string $tag = null;

    #[Groups(['hashtag:read'])]
    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\OneToMany(targetEntity: PostHashtag::class, mappedBy: 'hashtag', orphanRemoval: true)]
    private Collection $postHashtags;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->postHashtags = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTag(): ?string
    {
        return $this->tag;
    }

    public function setTag(string $tag): static
    {
        // aquí puedes normalizar (minúsculas, sin #)
        $this->tag = ltrim(mb_strtolower($tag), '#');

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
            $postHashtag->setHashtag($this);
        }

        return $this;
    }

    public function removePostHashtag(PostHashtag $postHashtag): static
    {
        if ($this->postHashtags->removeElement($postHashtag)) {
            if ($postHashtag->getHashtag() === $this) {
                $postHashtag->setHashtag(null);
            }
        }

        return $this;
    }
}
