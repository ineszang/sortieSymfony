<?php

namespace App\Entity;

use App\Repository\ParticipantRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Table(name: 'participant')]
#[ORM\Entity(repositoryClass: ParticipantRepository::class)]
#[UniqueEntity(fields: ['pseudo'], message: 'Nom d\'utilisateur déjà utilisé')]
#[UniqueEntity(fields: ['mail'], message: 'E-mail déjà utilisé')]
class Participant implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id')]
    private ?int $id = null;

    #[Assert\Length(min: 5, max: 30,
        minMessage: 'Le pseudo doit contenir au moins 5 caractères.',
        maxMessage: 'Le pseudo ne peut pas dépasser 30 caractères.'
    )]
    #[Assert\NotBlank(message: 'Le pseudo ne peut pas être vide.')]
    #[ORM\Column(name: 'pseudo', length: 30)]
    private ?string $pseudo = null;

    #[Assert\Length(max: 40,
        maxMessage: 'Le nom ne peut pas dépasser 40 caractères.'
    )]
    #[Assert\NotBlank(message: 'Le nom ne peut pas être vide.')]
    #[ORM\Column(name:'nom', length: 40)]
    private ?string $nom = null;

    #[Assert\Length(max: 40,
        maxMessage: 'Le prénom ne peut pas dépasser 40 caractères.'
    )]
    #[Assert\NotBlank(message: 'Le prénom ne peut pas être vide.')]
    #[ORM\Column(name: 'prenom', length: 40)]
    private ?string $prenom = null;

    #[Assert\Length(max: 15,
        maxMessage: 'Le numéro de téléphone ne peut pas dépasser 15 caractères.'
    )]
    #[Assert\NotBlank(message: 'Le numéro de téléphone ne peut pas être vide.')]
    #[ORM\Column(name: 'telephone', length: 15, nullable: true)]
    private ?string $telephone = null;

    #[Assert\Email(message: 'Cet e-mail {{ value }} n\'est pas un e-mail valide.')]
    #[Assert\NotBlank(message: 'L\'adresse e-mail ne peut pas être vide.')]
    #[ORM\Column(name: 'mail', length: 50)]
    private ?string $mail = null;

    #[ORM\Column(name: 'mot_de_passe', length: 255)]
    private ?string $motDePasse = null;

    #[ORM\Column(name: 'administrateur', type: 'boolean')]
    private ?bool $administrateur = false;

    #[ORM\Column(name: 'actif', type: 'boolean')]
    private ?bool $actif = false;


    /**
     * @var Collection<int, Sortie>
     */
    #[ORM\OneToMany(targetEntity: Sortie::class, mappedBy: 'organisateur', orphanRemoval: true)]
    private Collection $sortiesOrganiseesParMoi;

    /**
     * @var Collection<int, Sortie>
     */
    #[ORM\ManyToMany(targetEntity: Sortie::class, mappedBy: 'participants')]
    private Collection $sorties;

    #[ORM\ManyToOne(inversedBy: 'stagiaires')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Site $site = null;


    #[ORM\Column(length: 400, nullable: true)]
    private ?string $urlPhoto = null;



    public function __construct()
    {
        $this->sortiesOrganiseesParMoi = new ArrayCollection();
        $this->sorties = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->nom . ' ' . $this->prenom . ', ' ($this->pseudo) . ($this->telephone ? ', ' . $this->mail : '');
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getUrlPhoto(): ?string
    {
        return $this->urlPhoto;
    }

    public function setUrlPhoto(?string $urlPhoto): static
    {
        $this->urlPhoto = $urlPhoto;

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
        return $this->motDePasse;
    }

    public function setMotDePasse(string $motDePasse): static
    {
        $this->motDePasse = $motDePasse;

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

    /**
     * @return Collection<int, Sortie>
     */
    public function getSortiesOrganiseesParMoi(): Collection
    {
        return $this->sortiesOrganiseesParMoi;
    }

    public function addSortiesOrganiseesParMoi(Sortie $sortiesOrganiseesParMoi): static
    {
        if (!$this->sortiesOrganiseesParMoi->contains($sortiesOrganiseesParMoi)) {
            $this->sortiesOrganiseesParMoi->add($sortiesOrganiseesParMoi);
            $sortiesOrganiseesParMoi->setOrganisateur($this);
        }

        return $this;
    }

    public function removeSortiesOrganiseesParMoi(Sortie $sortiesOrganiseesParMoi): static
    {
        if ($this->sortiesOrganiseesParMoi->removeElement($sortiesOrganiseesParMoi)) {
            // set the owning side to null (unless already changed)
            if ($sortiesOrganiseesParMoi->getOrganisateur() === $this) {
                $sortiesOrganiseesParMoi->setOrganisateur(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Sortie>
     */
    public function getSorties(): Collection
    {
        return $this->sorties;
    }

    public function addSorty(Sortie $sorty): static
    {
        if (!$this->sorties->contains($sorty)) {
            $this->sorties->add($sorty);
            $sorty->addParticipant($this);
        }

        return $this;
    }

    public function removeSorty(Sortie $sorty): static
    {
        if ($this->sorties->removeElement($sorty)) {
            $sorty->removeParticipant($this);
        }

        return $this;
    }

    public function getSite(): ?Site
    {
        return $this->site;
    }

    public function setSite(?Site $site): static
    {
        $this->site = $site;

        return $this;
    }

    // Méthodes requises par UserInterface et PasswordAuthenticatedUserInterface

    public function getPassword(): ?string
    {
        return $this->motDePasse;
    }

    public function getRoles(): array
    {
        $roles = [];
        if ($this->administrateur) {
            $roles[] = 'ROLE_ADMIN';
        }
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function getSalt(): ?string
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
        return null;
    }

    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
    }

    public function getUserIdentifier(): string
    {
        return $this->pseudo;
    }
}