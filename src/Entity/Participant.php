<?php

namespace App\Entity;

use App\Repository\ParticipantsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ParticipantsRepository::class)]
class Participant
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $no_participant = null;

    #[ORM\Column(length: 30)]
    private ?string $pseudo = null;

    #[ORM\Column(length: 30)]
    private ?string $nom = null;

    #[ORM\Column(length: 30)]
    private ?string $prenom = null;

    #[ORM\Column(length: 15, nullable: true)]
    private ?string $telephone = null;

    #[ORM\Column(length: 50)]
    private ?string $mail = null;

    #[ORM\Column(length: 255)]
    private ?string $mot_de_passe = null;

    #[ORM\Column]
    private ?bool $administrateur = null;

    #[ORM\Column]
    private ?bool $actif = null;

    #[ORM\ManyToOne(inversedBy: 'participants')]
    #[ORM\JoinColumn(nullable: false)]
    private ?sites $sites = null;

    /**
     * @var Collection<int, Sorties>
     */
    #[ORM\OneToMany(targetEntity: Sorties::class, mappedBy: 'organisateur', orphanRemoval: true)]
    private Collection $sortiesOrganisateur;

    /**
     * @var Collection<int, sorties>
     */
    #[ORM\ManyToMany(targetEntity: sorties::class, inversedBy: 'participants')]
    private Collection $date_inscription;

    public function __construct()
    {
        $this->sortiesOrganisateur = new ArrayCollection();
        $this->date_inscription = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNoParticipant(): ?int
    {
        return $this->no_participant;
    }

    public function setNoParticipant(int $no_participant): static
    {
        $this->no_participant = $no_participant;

        return $this;
    }

    public function getPseudo(): ?string
    {
        return $this->pseudo;
    }

    public function setPseudo(string $pseudo): static
    {
        $this->pseudo = $pseudo;

        return $this;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): static
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(?string $telephone): static
    {
        $this->telephone = $telephone;

        return $this;
    }

    public function getMail(): ?string
    {
        return $this->mail;
    }

    public function setMail(string $mail): static
    {
        $this->mail = $mail;

        return $this;
    }

    public function getMotDePasse(): ?string
    {
        return $this->mot_de_passe;
    }

    public function setMotDePasse(string $mot_de_passe): static
    {
        $this->mot_de_passe = $mot_de_passe;

        return $this;
    }

    public function isAdministrateur(): ?bool
    {
        return $this->administrateur;
    }

    public function setAdministrateur(bool $administrateur): static
    {
        $this->administrateur = $administrateur;

        return $this;
    }

    public function isActif(): ?bool
    {
        return $this->actif;
    }

    public function setActif(bool $actif): static
    {
        $this->actif = $actif;

        return $this;
    }

    public function getSites(): ?sites
    {
        return $this->sites;
    }

    public function setSites(?sites $sites): static
    {
        $this->sites = $sites;

        return $this;
    }

    /**
     * @return Collection<int, Sorties>
     */
    public function getSortiesOrganisateur(): Collection
    {
        return $this->sortiesOrganisateur;
    }

    public function addSortiesOrganisateur(Sorties $sortiesOrganisateur): static
    {
        if (!$this->sortiesOrganisateur->contains($sortiesOrganisateur)) {
            $this->sortiesOrganisateur->add($sortiesOrganisateur);
            $sortiesOrganisateur->setOrganisateur($this);
        }

        return $this;
    }

    public function removeSortiesOrganisateur(Sorties $sortiesOrganisateur): static
    {
        if ($this->sortiesOrganisateur->removeElement($sortiesOrganisateur)) {
            // set the owning side to null (unless already changed)
            if ($sortiesOrganisateur->getOrganisateur() === $this) {
                $sortiesOrganisateur->setOrganisateur(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, sorties>
     */
    public function getDateInscription(): Collection
    {
        return $this->date_inscription;
    }

    public function addDateInscription(sorties $dateInscription): static
    {
        if (!$this->date_inscription->contains($dateInscription)) {
            $this->date_inscription->add($dateInscription);
        }

        return $this;
    }

    public function removeDateInscription(sorties $dateInscription): static
    {
        $this->date_inscription->removeElement($dateInscription);

        return $this;
    }
}
