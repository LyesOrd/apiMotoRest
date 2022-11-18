<?php

namespace App\Entity;

use App\Repository\MotoSpecRepository;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use Hateoas\Configuration\Annotation as Hateoas;


/**
 * @Hateoas\Relation(
 *      "self",
 *      href = @Hateoas\Route(
 *          "detailsMotos",
 *          parameters = { "id" = "expr(object.getId())" }
 *      ),
 *      exclusion = @Hateoas\Exclusion(groups="getMotos")
 * )
 *
 * @Hateoas\Relation(
 *      "delete",
 *      href = @Hateoas\Route(
 *          "deleteMotos",
 *          parameters = { "id" = "expr(object.getId())" },
 *      ),
 *      exclusion = @Hateoas\Exclusion(groups="getMotos", excludeIf = "expr(not is_granted('ROLE_ADMIN'))"),
 * )
 * 
 * * @Hateoas\Relation(
 *      "update",
 *      href = @Hateoas\Route(
 *          "updateMotos",
 *          parameters = { "id" = "expr(object.getId())" },
 *      ),
 *      exclusion = @Hateoas\Exclusion(groups="getMotos", excludeIf = "expr(not is_granted('ROLE_ADMIN'))"),
 * )
*/
#[ORM\Entity(repositoryClass: MotoSpecRepository::class)]
class MotoSpec
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["getMotos", "getConcessions"])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(["getMotos", "getConcessions"])]
    private ?string $type = null;

    #[ORM\Column(length: 255)]
    #[Groups(["getMotos", "getConcessions"])]
    private ?string $refroidissement = null;

    #[ORM\Column]
    #[Groups(["getMotos", "getConcessions"])]
    private ?int $cylindree = null;

    #[ORM\Column]
    #[Groups(["getMotos", "getConcessions"])]
    private ?int $puissance = null;

    #[ORM\Column(nullable: true)]
    #[Groups(["getMotos", "getConcessions"])]
    private ?int $puissance_au_litre = null;

    #[ORM\Column(nullable: true)]
    #[Groups(["getMotos", "getConcessions"])]
    private ?int $reservoir = null;

    #[ORM\Column]
    #[Groups(["getMotos", "getConcessions"])]
    private ?int $poids = null;

    #[ORM\Column(length: 255)]
    #[Groups(["getMotos", "getConcessions"])]
    private ?string $transmission = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(["getMotos", "getConcessions"])]
    private ?string $couleur = null;

    #[ORM\Column]
    #[Groups(["getMotos", "getConcessions"])]
    private ?float $prix = null;

    #[ORM\Column(nullable: true)]
    #[Groups(["getMotos", "getConcessions"])]
    private ?bool $status = null;

    #[ORM\ManyToOne(targetEntity: Concession::class, inversedBy: 'motos')]
    #[ORM\JoinColumn(onDelete:"CASCADE")]
    #[Groups(["getMotos"])]
    private ?Concession $concession = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getRefroidissement(): ?string
    {
        return $this->refroidissement;
    }

    public function setRefroidissement(string $refroidissement): self
    {
        $this->refroidissement = $refroidissement;

        return $this;
    }

    public function getCylindree(): ?int
    {
        return $this->cylindree;
    }

    public function setCylindree(int $cylindree): self
    {
        $this->cylindree = $cylindree;

        return $this;
    }

    public function getPuissance(): ?int
    {
        return $this->puissance;
    }

    public function setPuissance(int $puissance): self
    {
        $this->puissance = $puissance;

        return $this;
    }

    public function getPuissanceAuLitre(): ?int
    {
        return $this->puissance_au_litre;
    }

    public function setPuissanceAuLitre(?int $puissance_au_litre): self
    {
        $this->puissance_au_litre = $puissance_au_litre;

        return $this;
    }

    public function getReservoir(): ?int
    {
        return $this->reservoir;
    }

    public function setReservoir(?int $reservoir): self
    {
        $this->reservoir = $reservoir;

        return $this;
    }

    public function getPoids(): ?int
    {
        return $this->poids;
    }

    public function setPoids(int $poids): self
    {
        $this->poids = $poids;

        return $this;
    }

    public function getTransmission(): ?string
    {
        return $this->transmission;
    }

    public function setTransmission(string $transmission): self
    {
        $this->transmission = $transmission;

        return $this;
    }

    public function getCouleur(): ?string
    {
        return $this->couleur;
    }

    public function setCouleur(?string $couleur): self
    {
        $this->couleur = $couleur;

        return $this;
    }

    public function getPrix(): ?float
    {
        return $this->prix;
    }

    public function setPrix(float $prix): self
    {
        $this->prix = $prix;

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

    public function getConcession(): ?Concession
    {
        return $this->concession;
    }

    public function setConcession(?Concession $concession): self
    {
        $this->concession = $concession;

        return $this;
    }
}
