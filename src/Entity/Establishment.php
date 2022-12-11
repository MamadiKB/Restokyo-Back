<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\EstablishmentRepository;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: EstablishmentRepository::class)]
class Establishment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["getEstablishment"])]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Groups(["getEstablishment"])]
    private ?string $name = null;

    #[ORM\Column(length: 25)]
    #[Groups(["getEstablishment"])]
    private ?string $type = null;

    #[ORM\Column(length: 200, nullable: true)]
    #[Groups(["getEstablishment"])]
    private ?string $description = null;

    #[ORM\Column(length: 200)]
    #[Groups(["getEstablishment"])]
    private ?string $address = null;

    #[ORM\Column(nullable: true)]
    #[Groups(["getEstablishment"])]
    private ?int $price = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(["getEstablishment"])]
    private ?string $website = null;

    #[ORM\Column(type: Types::BIGINT, nullable: true)]
    #[Groups(["getEstablishment"])]
    private ?string $phone = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 3, scale: 1, nullable: true)]
    #[Groups(["getEstablishment", "getDistrict"])]
    private ?string $rating = null;

    #[ORM\Column(length: 255)]
    #[Groups(["getEstablishment", "getDistrict"])]
    private ?string $slug = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(["getEstablishment", "getDistrict"])]
    private ?string $picture = null;

    #[ORM\Column]
    #[Groups(["getEstablishment", "getDistrict"])]
    private ?int $status = null;

    #[ORM\Column(length: 200, nullable: true)]
    #[Groups(["getEstablishment", "getDistrict"])]
    private ?string $opening_time = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups(["getEstablishment", "getDistrict"])]
    private ?\DateTimeInterface $updated_at = null;

    #[ORM\ManyToOne(inversedBy: 'establishment')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(["getEstablishment"])]
    private ?District $district = null;

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
}
