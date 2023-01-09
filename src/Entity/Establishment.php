<?php

namespace App\Entity;


use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use App\Repository\EstablishmentRepository;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EstablishmentRepository::class)]
class Establishment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["getEstablishment", "getDistrict", "getTag", "getComment", "getUser"])]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Groups(["getEstablishment", "getDistrict", "getTag", "getComment", "getUser"])]
    #[Assert\NotBlank(message: "Le nom du restauran est obligatoire")]
    #[Assert\Length(min: 1, max: 100, minMessage: "Le nom doit faire au moins {{ limit }} caractère", maxMessage: "Le nom ne peut pas faire plus de {{ limit }} caractères")]
    private ?string $name = null;

    #[ORM\Column(length: 25)]
    #[Groups(["getEstablishment", "getDistrict", "getTag", "getComment", "getUser"])]
    #[Assert\NotBlank(message: "Le type du restauran est obligatoire")]
    #[Assert\Length(min: 1, max: 25, minMessage: "Le type doit faire au moins {{ limit }} caractère", maxMessage: "Le type ne peut pas faire plus de {{ limit }} caractères")]
    private ?string $type = null;

    #[ORM\Column(length: 200, nullable: true)]
    #[Groups(["getEstablishment", "getDistrict", "getTag", "getComment", "getUser"])]
    private ?string $description = null;

    #[ORM\Column(length: 200)]
    #[Groups(["getEstablishment", "getDistrict", "getTag", "getComment", "getUser"])]
    #[Assert\NotBlank(message: "L'adresse du restauran est obligatoire")]
    #[Assert\Length(min: 1, max: 200, minMessage: "L'adresse doit faire au moins {{ limit }} caractère", maxMessage: "L'adresse ne peut pas faire plus de {{ limit }} caractères")]
    private ?string $address = null;

    #[ORM\Column(nullable: true)]
    #[Groups(["getEstablishment", "getDistrict", "getTag", "getComment", "getUser"])]
    private ?int $price = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(["getEstablishment", "getDistrict", "getTag", "getComment", "getUser"])]
    private ?string $website = null;

    #[ORM\Column(type: Types::BIGINT, nullable: true)]
    #[Groups(["getEstablishment", "getDistrict", "getTag", "getComment", "getUser"])]
    private ?string $phone = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 3, scale: 1, nullable: true)]
    #[Groups(["getEstablishment", "getDistrict", "getTag", "getComment", "getUser"])]
    private ?string $rating = null;

    #[ORM\Column(length: 255)]
    #[Groups(["getEstablishment", "getDistrict", "getTag", "getComment", "getUser"])]
    #[Assert\NotBlank(message: "le slug du restauran est obligatoire")]
    #[Assert\Length(min: 1, max: 255, minMessage: "le slug doit faire au moins {{ limit }} caractère", maxMessage: "le slug ne peut pas faire plus de {{ limit }} caractères")]
    private ?string $slug = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(["getEstablishment", "getDistrict", "getTag", "getComment", "getUser"])]
    private ?string $picture = null;

    #[ORM\Column]
    #[Groups(["getEstablishment", "getDistrict", "getTag", "getComment", "getUser"])]
    #[Assert\NotBlank(message: "Le status du restauran est obligatoire")]
    private ?int $status = null;

    #[ORM\Column(length: 200, nullable: true)]
    #[Groups(["getEstablishment", "getDistrict", "getTag", "getComment", "getUser"])]
    private ?string $opening_time = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups(["getEstablishment", "getDistrict", "getTag", "getComment", "getUser"])]
    private ?\DateTimeInterface $updated_at = null;

    #[ORM\ManyToOne(inversedBy: 'establishment', cascade: ['persist'] )]
    #[ORM\JoinColumn(nullable: true, onDelete:"CASCADE")]
    #[Groups(["getEstablishment", "getTag", "getComment", "getUser"])]
    private ?District $district = null;

    #[ORM\ManyToMany(targetEntity: Tag::class, mappedBy: 'establishment', cascade: ['persist'])]
    #[Groups(["getEstablishment", "getDistrict", "getUser"])]
    private Collection $tags;

    #[ORM\OneToMany(mappedBy: 'establishment', targetEntity: Comment::class, orphanRemoval: true)]
    #[Groups(["getEstablishment", "getDistrict"])]
    private Collection $comments;

    #[ORM\ManyToMany(targetEntity: User::class, mappedBy: 'favoris',)]
    private Collection $users;

    public function __construct()
    {
        $this->tags = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->updated_at = new \DateTimeImmutable();
        $this->users = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(?int $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getWebsite(): ?string
    {
        return $this->website;
    }

    public function setWebsite(?string $website): self
    {
        $this->website = $website;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getRating(): ?string
    {
        return $this->rating;
    }

    public function setRating(?string $rating): self
    {
        $this->rating = $rating;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getPicture(): ?string
    {
        return $this->picture;
    }

    public function setPicture(?string $picture): self
    {
        $this->picture = $picture;

        return $this;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getOpeningTime(): ?string
    {
        return $this->opening_time;
    }

    public function setOpeningTime(?string $opening_time): self
    {
        $this->opening_time = $opening_time;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(?\DateTimeInterface $updated_at): self
    {
        $this->updated_at = $updated_at;

        return $this;
    }

    public function getDistrict(): ?District
    {
        return $this->district;
    }

    public function setDistrict(?District $district): self
    {
        $this->district = $district;

        return $this;
    }

    /**
     * @return Collection<int, Tag>
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function addTag(Tag $tag): self
    {
        if (!$this->tags->contains($tag)) {
            $this->tags->add($tag);
            $tag->addEstablishment($this);
        }

        return $this;
    }

    public function removeTag(Tag $tag): self
    {
        if ($this->tags->removeElement($tag)) {
            $tag->removeEstablishment($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Comment>
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->setEstablishment($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getEstablishment() === $this) {
                $comment->setEstablishment(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
            $user->addFavori($this);
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        if ($this->users->removeElement($user)) {
            $user->removeFavori($this);
        }

        return $this;
    }
}
