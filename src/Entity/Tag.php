<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\TagRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TagRepository::class)]
class Tag
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["getEstablishment", "getDistrict", "getTag"])]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Groups(["getEstablishment", "getDistrict", "getTag"])]
    #[Assert\NotBlank(message: "le nom du tag est obligatoire")]
    #[Assert\Length(min: 1, max: 100, minMessage: "le nom doit faire au moins {{ limit }} caractère", maxMessage: "Le nom ne peut pas faire plus de {{ limit }} caractères")]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    #[Groups(["getEstablishment", "getDistrict", "getTag"])]
    #[Assert\NotBlank(message: "le slug du tag est obligatoire")]
    #[Assert\Length(min: 1, max: 255, minMessage: "le nom doit faire au moins {{ limit }} caractère", maxMessage: "Le nom ne peut pas faire plus de {{ limit }} caractères")]
    private ?string $slug = null;

    #[ORM\ManyToMany(targetEntity: Establishment::class, inversedBy: 'tags')]
    #[Groups(["getTag"])]
    private Collection $establishment;

    public function __construct()
    {
        $this->establishment = new ArrayCollection();
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

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * @return Collection<int, Establishment>
     */
    public function getEstablishment(): Collection
    {
        return $this->establishment;
    }

    public function addEstablishment(Establishment $establishment): self
    {
        if (!$this->establishment->contains($establishment)) {
            $this->establishment->add($establishment);
        }

        return $this;
    }

    public function removeEstablishment(Establishment $establishment): self
    {
        $this->establishment->removeElement($establishment);

        return $this;
    }
}
