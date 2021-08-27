<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\AuctionRepository;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\HttpFoundation\File\File;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="auctions")
 * @ORM\Entity(repositoryClass=AuctionRepository::class)
 */
class Auction
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=30)
     * @Assert\NotBlank
     * @Assert\Length(min = 2, max = 30,
     * minMessage = "Nazwa of piec wendarniczy should be at least {{ limit }} characters long",
     * maxMessage = "Nazwa of piec wendarniczy cannot be longer than {{ limit }} characters")
     */
    private $title;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime")
     */
    private $endsAt;

    /**
     * @ORM\Column(type="text", nullable=true, length=2000)
     * @Assert\NotBlank
     * @Assert\Length(min = 10, max = 2000,
     * minMessage = "Description of piec wendzarniczy should be at least {{ limit }} characters long",
     * maxMessage = "Description of piec wendzarniczy cannot be longer than {{ limit }} characters")
     */
    private $description;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="Auctions")
     * @ORM\JoinColumn(nullable=false)
     */
    private $byUser;

    /**
     * @ORM\OneToMany(targetEntity=AuctionImage::class, mappedBy="auction")
     */
    private $images;

    /**
     * @ORM\OneToMany(targetEntity=Offer::class, mappedBy="auction")
     */
    private $offers;

    /**
     * @ORM\ManyToMany(targetEntity=User::class, mappedBy="likedAuctions")
     */
    private $likedByUsers;

    

    public function __construct()
    {
        $this->images = new ArrayCollection();
        $this->offers = new ArrayCollection();
        $this->likedByUsers = new ArrayCollection();
        
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    /*public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }*/
    public function setCreatedAt(?\DateTimeInterface $createdAt): self
    {
        $createdAt == null ? $this->createdAt = new \DateTime('now') : $this->createdAt = $createdAt;

        return $this;
    }

    public function getEndsAt(): ?\DateTimeInterface
    {
        return $this->endsAt;
    }

    /*public function setEndsAt(\DateTimeInterface $endsAt): self
    {
        $this->endsAt = $endsAt;

        return $this;
    }*/
    public function setEndsAtManually(?\DateTimeInterface $endsAt): self
    {
        $endsAt == null ? $this->endsAt = new \DateTime('now') : $this->endsAt = $endsAt;

        return $this;
    }

    public function setEndsAt(string $secondsOffest): self
    {
        $this->endsAt = new \DateTime();
        $this->endsAt->add(new \DateInterval('PT'.$secondsOffest.'S'));

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

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

    /**
     * @return Collection|AuctionImage[]
     */
    public function getImages(): Collection
    {
        return $this->images;
    }

    public function addImage(AuctionImage $image): self
    {
        if (!$this->images->contains($image)) {
            $this->images[] = $image;
            $image->setAuction($this);
        }

        return $this;
    }

    public function removeImage(AuctionImage $image): self
    {
        if ($this->images->removeElement($image)) {
            // set the owning side to null (unless already changed)
            if ($image->getAuction() === $this) {
                $image->setAuction(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Offer[]
     */
    public function getOffers(): Collection
    {
        return $this->offers;
    }

    public function addOffer(Offer $offer): self
    {
        if (!$this->offers->contains($offer)) {
            $this->offers[] = $offer;
            $offer->setAuction($this);
        }

        return $this;
    }

    public function removeOffer(Offer $offer): self
    {
        if ($this->offers->removeElement($offer)) {
            // set the owning side to null (unless already changed)
            if ($offer->getAuction() === $this) {
                $offer->setAuction(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getLikedByUsers(): Collection
    {
        return $this->likedByUsers;
    }

    public function addLikedByUser(User $likedByUser): self
    {
        if (!$this->likedByUsers->contains($likedByUser)) {
            $this->likedByUsers[] = $likedByUser;
            $likedByUser->addLikedAuction($this);
        }

        return $this;
    }

    public function removeLikedByUser(User $likedByUser): self
    {
        if ($this->likedByUsers->removeElement($likedByUser)) {
            $likedByUser->removeLikedAuction($this);
        }

        return $this;
    }
}
