<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\EleveRepository")
 */
class Eleve
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;
    
    /**
     * @ORM\Column(type="string", length=100)
     * * @Assert\NotBlank(
     *     message = "Le nom est obligatoire"
     * )
     * @Assert\Length(
     *      min = 1,
     *      max = 100,
     *      minMessage = "Le nom doit contenir au moins {{ limit }} lettre",
     *      maxMessage = "Le nom doit contenir au maximum {{ limit }} lettres",
     *      allowEmptyString = false
     * )
     */
    private $nom;
    
    /**
     * @ORM\Column(type="string", length=30)
     * @Assert\NotBlank(
     *     message = "Le prénom est obligatoire"
     * )
     * @Assert\Length(
     *      min = 1,
     *      max = 30,
     *      minMessage = "Le prenom doit contenir au moins {{ limit }} lettre",
     *      maxMessage = "Le prenom doit contenir au maximum {{ limit }} lettres",
     *      allowEmptyString = false
     * )
     */
    private $prenom;
    
    /**
     * @ORM\Column(type="date")
     * @Assert\NotNull(
     *     message = "La date de naissance est obligatoire"
     * )
     * @Assert\Type(
     *     type="dateTime",
     *     message="Le format de la date de naissance n'est pas valide."
     * )
     * @Assert\Range(
     *      min = 0,
     *      max = 20,
     *      minMessage = "La note doit être supérieure ou égale à 0",
     *      maxMessage = "La note doit être inférieure ou égale à 20"
     * )
     */
    private $date_naissance;
    
    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Note", mappedBy="eleve", orphanRemoval=true)
     */
    private $notes;
    
    public function __construct()
    {
        $this->notes = new ArrayCollection();
    }
    
    public function getId(): ?int
    {
        return $this->id;
    }
    
    public function getNom(): ?string
    {
        return $this->nom;
    }
    
    public function setNom(?string $nom): self
    {
        $this->nom = $nom == null ? null : ucfirst($nom);
        
        return $this;
    }
    
    public function getPrenom(): ?string
    {
        return $this->prenom;
    }
    
    public function setPrenom(?string $prenom): self
    {
        $this->prenom = $prenom == null ? null : ucfirst($prenom);
        
        return $this;
    }
    
    public function getDateNaissance(): ?\DateTimeInterface
    {
        return $this->date_naissance;
    }
    
    public function setDateNaissance(?string $date_naissance): self
    {
        if ($date_naissance == null) {
            return $this;
        }
        try {
            $this->date_naissance = new \DateTime($date_naissance);
        } catch (\Exception $e) {
            //oui je sais, je triche, c'est juste pour trigger l'@Assert\Type si la date de naissance est invalide
            $this->date_naissance = $date_naissance;
        }
        
        return $this;
    }
    
    /**
     * @return Collection|Note[]
     */
    public function getNotes(): Collection
    {
        return $this->notes;
    }
    
    public function addNote(Note $note): self
    {
        if (!$this->notes->contains($note)) {
            $this->notes[] = $note;
            $note->setEleve($this);
        }
        
        return $this;
    }
    
    public function removeNote(Note $note): self
    {
        if ($this->notes->contains($note)) {
            $this->notes->removeElement($note);
            // set the owning side to null (unless already changed)
            if ($note->getEleve() === $this) {
                $note->setEleve(null);
            }
        }
        
        return $this;
    }
}
