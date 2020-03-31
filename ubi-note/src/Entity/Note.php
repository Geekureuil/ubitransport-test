<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\NoteRepository")
 */
class Note
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;
    
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Eleve", inversedBy="notes")
     * @ORM\JoinColumn(nullable=false)
     * @Assert\NotNull(
     *     message = "L'élève est obligatoire"
     * )
     */
    private $eleve;
    
    /**
     * @ORM\Column(type="string", length=100)
     * @Assert\NotNull(
     *     message = "La matière est obligatoire"
     * )
     * @Assert\Length(
     *      min = 1,
     *      max = 100,
     *      minMessage = "La matière doit contenir au moins {{ limit }} lettre",
     *      maxMessage = "La matière doit contenir au maximum {{ limit }} lettres",
     *      allowEmptyString = false
     * )
     */
    private $matiere;
    
    /**
     * @ORM\Column(type="float",)
     * @Assert\NotNull(
     *     message = "La note est obligatoire"
     * )
     * @Assert\Range(
     *      min = 0,
     *      max = 20,
     *     invalidMessage = "La note doit être un nombre",
     *      minMessage = "La note doit être suppérieure ou égale à 0",
     *      maxMessage = "La note doit être inférieure ou égale à 0",
     *     notInRangeMessage = "La note doit être comprise entre 0 et 20"
     * )
     */
    private $note;
    
    public function getId(): ?int
    {
        return $this->id;
    }
    
    public function getEleve(): ?Eleve
    {
        return $this->eleve;
    }
    
    public function setEleve(?Eleve $eleve): self
    {
        $this->eleve = $eleve;
        
        return $this;
    }
    
    public function getMatiere(): ?string
    {
        return $this->matiere;
    }
    
    public function setMatiere(?string $matiere): self
    {
        $this->matiere = is_string($matiere) ? ucfirst($matiere) : $matiere;
        
        return $this;
    }
    
    public function getNote(): ?float
    {
        return $this->note;
    }
    
    /**
     * @param mixed $note
     *
     * @return Note
     */
    public function setNote($note): self
    {
        $this->note = is_numeric($note) ? (float)$note : $note;
        
        return $this;
    }
}
