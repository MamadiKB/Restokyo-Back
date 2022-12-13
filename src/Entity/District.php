<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\DistrictRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: DistrictRepository::class)]
class District
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["getEstablishment", "getDistrict", "getTag"])]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Groups(["getEstablishment", "getDistrict", "getTag"])]
    #[Assert\NotBlank(message: "le nom du district est obligatoire")]
    #[Assert\Length(min: 1, max: 100, minMessage: "le nom doit faire au moins {{ limit }} caractère", maxMessage: "Le nom ne peut pas faire plus de {{ limit }} caractères")]
    private ?string $name = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(["getEstablishment", "getDistrict", "getTag"])]
    private ?string $kanji = null;

    #[ORM\Column(length: 100)]
    #[Groups(["getEstablishment", "getDistrict", "getTag"])]
    #[Assert\NotBlank(message: "Le slug du district est obligatoire")]
    #[Assert\Length(min: 1, max: 100, minMessage: "Le slug doit faire au moins {{ limit }} caractère", maxMessage: "Le slugne peut pas faire plus de {{ limit }} caractères")]
    private ?string $slug = null;

    #[ORM\OneToMany(mappedBy: 'district', targetEntity: Establishment::class)]
    #[Groups(["getDistrict"])]
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

    public function getKanji(): ?string
    {
        return $this->kanji;
    }

    public function setKanji(?string $kanji): self
    {
        $this->kanji = $kanji;

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
            $establishment->setDistrict($this);
        }

        return $this;
    }

    public function removeEstablishment(Establishment $establishment): self
    {
        if ($this->establishment->removeElement($establishment)) {
            // set the owning side to null (unless already changed)
            if ($establishment->getDistrict() === $this) {
                $establishment->setDistrict(null);
            }
        }

        return $this;
    }
}
