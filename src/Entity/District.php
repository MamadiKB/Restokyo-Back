<?php

namespace App\Entity;

use App\Repository\DistrictRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DistrictRepository::class)]
class District
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $name = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $kanji = null;

    #[ORM\Column(length: 100)]
    private ?string $slug = null;

    #[ORM\OneToMany(mappedBy: 'district', targetEntity: Establishment::class)]
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
