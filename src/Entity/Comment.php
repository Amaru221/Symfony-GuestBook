<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\Get;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;
use App\Repository\CommentRepository;
use ApiPlatform\Metadata\GetCollection;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity(repositoryClass: CommentRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    operations: [
        new Get(normalizationContext: ['groups' => 'comment:item']),
        new GetCollection(normalizationContext: ['groups' => 'comment:list']),
    ],
    order: ['createdAt' => 'DESC'],
    paginationEnabled: false,

)]
#[ApiFilter(SearchFilter::class, properties: ['conference' => 'exact'])]
class Comment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups('comment:item', 'comment:list')]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Groups('comment:item', 'comment:list')]
    private ?string $author = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank]
    #[Groups('comment:item', 'comment:list')]
    private ?string $text = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Email]
    #[Groups('comment:item', 'comment:list')]
    private ?string $email = null;

    #[ORM\Column]
    #[Groups('comment:item', 'comment:list')]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\ManyToOne(inversedBy: 'comments')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Conference $conference = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups('comment:item', 'comment:list')]
    private ?string $PhotoFilename = null;

    #[ORM\Column(length: 255, options: ['default' => 'submitted'])]
    private ?string $state = 'submitted';

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAuthor(): ?string
    {
        return $this->author;
    }

    public function setAuthor(string $author): static
    {
        $this->author = $author;

        return $this;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(string $text): static
    {
        $this->text = $text;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getConference(): ?Conference
    {
        return $this->conference;
    }

    public function setConference(?Conference $conference): static
    {
        $this->conference = $conference;

        return $this;
    }

    public function getPhotoFilename(): ?string
    {
        return $this->PhotoFilename;
    }

    public function setPhotoFilename(?string $PhotoFilename): static
    {
        $this->PhotoFilename = $PhotoFilename;

        return $this;
    }

    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(string $state): static
    {
        $this->state = $state;

        return $this;
    }

}
