<?php

namespace App\Entity;

use App\Repository\AuctionImageRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=AuctionImageRepository::class)
 */
class AuctionImage
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $filename;

    /**
     * @ORM\ManyToOne(targetEntity=Auction::class, inversedBy="images")
     */
    private $auction;

    /**
     * @ORM\Column(type="integer")
     */
    private $orderIndicator;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFilename(): ?string
    {
        return $this->filename;
    }

    public function setFilename(string $filename): self
    {
        $this->filename = $filename;

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

    public function getOrderIndicator(): ?int
    {
        return $this->orderIndicator;
    }

    public function setOrderIndicator(int $orderIndicator): self
    {
        $this->orderIndicator = $orderIndicator;

        return $this;
    }
}
