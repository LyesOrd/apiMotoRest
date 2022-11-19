<?php

namespace App\Entity;

use App\Repository\ConcessionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use Hateoas\Configuration\Annotation as Hateoas;

/**
 * @Hateoas\Relation(
 *      "self",
 *      href = @Hateoas\Route(
 *          "detailsConcessions",
 *          parameters = { "id" = "expr(object.getId())" }
 *      ),
 *      exclusion = @Hateoas\Exclusion(groups="getConcessions")
 * )
 *
 * @Hateoas\Relation(
 *      "delete",
 *      href = @Hateoas\Route(
 *          "deleteConcessions",
 *          parameters = { "id" = "expr(object.getId())" },
 *      ),
 *      exclusion = @Hateoas\Exclusion(groups="getConcessions", excludeIf = "expr(not is_granted('ROLE_ADMIN'))"),
 * )
 * 
 * * @Hateoas\Relation(
 *      "update",
 *      href = @Hateoas\Route(
 *          "updateConcessions",
 *          parameters = { "id" = "expr(object.getId())" },
 *      ),
 *      exclusion = @Hateoas\Exclusion(groups="getConcessions", excludeIf = "expr(not is_granted('ROLE_ADMIN'))"),
 * )
 */
#[ORM\Entity(repositoryClass: ConcessionRepository::class)]
class Concession
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["getMotos", "getConcessions"])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(["getMotos", "getConcessions"])]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    #[Groups(["getMotos", "getConcessions"])]
    private ?string $pays = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(["getMotos", "getConcessions"])]
    private ?string $slogan = null;

    #[ORM\OneToMany(mappedBy: 'concession', targetEntity: MotoSpec::class)]
    #[Groups(["getConcessions"])]
    private Collection $motos;

    #[ORM\Column(type: 'boolean', nullable: true)]
    #[Groups(["getConcessions"])]
    private ?bool $status = null;

    public function __construct()
    {
        $this->motos = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPays(): ?string
    {
        return $this->pays;
    }

    public function setPays(string $pays): self
    {
        $this->pays = $pays;

        return $this;
    }

    public function getSlogan(): ?string
    {
        return $this->slogan;
    }

    public function setSlogan(?string $slogan): self
    {
        $this->slogan = $slogan;

        return $this;
    }

    /**
     * @return Collection<int, MotoSpec>
     */
    public function getMotos(): Collection
    {
        return $this->motos;
    }

    public function addBook(MotoSpec $moto): self
    {
        if (!$this->motos->contains($moto)) {
            $this->motos[] = $moto;
            $moto->setConcession($this);
        }

        return $this;
    }

    public function removeBook(MotoSpec $moto): self
    {
        if ($this->motos->removeElement($moto)) {
            // set the owning side to null (unless already changed)
            if ($moto->getConcession() === $this) {
                $moto->setConcession(null);
            }
        }

        return $this;
    }

    public function isStatus(): ?bool
    {
        return $this->status;
    }

    public function setStatus(?bool $status): self
    {
        $this->status = $status;

        return $this;
    }
}
