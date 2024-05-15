<?php

namespace App\Entity;

use App\Repository\FormationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FormationRepository::class)]
class Formation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column]
    private ?int $duree = null;

    /**
     * @var Collection<int, Groupe>
     */
    #[ORM\OneToMany(targetEntity: Groupe::class, mappedBy: 'formation')]
    private Collection $Groupes;

    #[ORM\ManyToOne(inversedBy: 'formations')]
    private ?Formateur $Formateur = null;

    /**
     * @var Collection<int, Apprenant>
     */
    #[ORM\ManyToMany(targetEntity: Apprenant::class, inversedBy: 'formations')]
    private Collection $Apprenants;

    public function __construct()
    {
        $this->Groupes = new ArrayCollection();
        $this->Apprenants = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getDuree(): ?int
    {
        return $this->duree;
    }

    public function setDuree(int $duree): static
    {
        $this->duree = $duree;

        return $this;
    }

    /**
     * @return Collection<int, Groupe>
     */
    public function getGroupes(): Collection
    {
        return $this->Groupes;
    }

    public function addGroupe(Groupe $groupe): static
    {
        if (!$this->Groupes->contains($groupe)) {
            $this->Groupes->add($groupe);
            $groupe->setFormation($this);
        }

        return $this;
    }

    public function removeGroupe(Groupe $groupe): static
    {
        if ($this->Groupes->removeElement($groupe)) {
            // set the owning side to null (unless already changed)
            if ($groupe->getFormation() === $this) {
                $groupe->setFormation(null);
            }
        }

        return $this;
    }

    public function getFormateur(): ?Formateur
    {
        return $this->Formateur;
    }

    public function setFormateur(?Formateur $Formateur): static
    {
        $this->Formateur = $Formateur;

        return $this;
    }

    /**
     * @return Collection<int, Apprenant>
     */
    public function getApprenants(): Collection
    {
        return $this->Apprenants;
    }

    public function addApprenant(Apprenant $apprenant): static
    {
        if (!$this->Apprenants->contains($apprenant)) {
            $this->Apprenants->add($apprenant);
        }

        return $this;
    }

    public function removeApprenant(Apprenant $apprenant): static
    {
        $this->Apprenants->removeElement($apprenant);

        return $this;
    }
}
