<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\OfferRepository;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * @ORM\Table(name="offers")
 * @ORM\Entity(repositoryClass=OfferRepository::class)
 */
class Offer
{
    /**
     * @Assert\Callback()
     */
    public function validateValue(ExecutionContextInterface $context)
    {
        $constraint = new ZipCodeConstraint(['country' => $this->country]);
        $violations = $context->getValidator()->validate($this->zipcode, $constraint);

        foreach ($violations as $violation) {
            $context->getViolations()->add($violation);
        }
    }


    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     * @Assert\LessThan(999999999)
     * @Assert\GreaterThan(0)
     */
    private $Value;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\ManyToOne(targetEntity=Auction::class, inversedBy="offers")
     * @ORM\JoinColumn(nullable=false)
     */
    private $auction;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="offers")
     * @ORM\JoinColumn(nullable=false)
     */
    private $byUser;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getValue(): ?int
    {
        return $this->Value;
    }

    public function setValue(int $Value): self
    {
        $this->Value = $Value;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeInterface $createdAt): self
    {
        $createdAt == null ? $this->createdAt = new \DateTime('now') : $this->createdAt = $createdAt;

        return $this;
    }

    public function getAuction(): ?Auction
    {
        return $this->auction;
    }

    public function setAuction(?Auction $auction): self
    {
        $this->auction = $auction;

        return $this;
    }

    public function getByUser(): ?User
    {
        return $this->byUser;
    }

    public function setByUser(?User $byUser): self
    {
        $this->byUser = $byUser;

        return $this;
    }
}
