<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\AuctionRepository;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\HttpFoundation\File\File;
use Doctrine\Common\Collections\ArrayCollection;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\Validator\Constraints\LessThan;
use Symfony\Component\Validator\Mapping\ClassMetadata;

/**
 * @ORM\Table(name="auctions")
 * @ORM\Entity(repositoryClass=AuctionRepository::class)
 * @Vich\Uploadable
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
     * NOTE: This is not a mapped field of entity metadata, just a simple property.
     * 
     * @Vich\UploadableField(mapping="images", fileNameProperty="imageName")
     * 
     * @var File|null
     */
    private $imageFile;


    /**
     * @ORM\Column(type="string", nullable=true)
     *
     * @var string|null
     */
    private $imageName;

    /**
     * @ORM\Column(type="string", length=255)
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
     * @ORM\Column(type="text", nullable=true)
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

    

    public function __construct()
    {
        $this->images = new ArrayCollection();
        $this->offers = new ArrayCollection();
        
    }

    /** 
    * If manually uploading a file (i.e. not using Symfony Form) ensure an instance
    * of 'UploadedFile' is injected into this setter to trigger the update. If this
    * bundle's configuration parameter 'inject_on_load' is set to 'true' this setter
    * must be able to accept an instance of 'File' as the bundle will inject one here
    * during Doctrine hydration.
    *
    * @param File|\Symfony\Component\HttpFoundation\File\UploadedFile|null $imageFile
    */
   public function setImageFile(?File $imageFile = null): void
   {
       $this->imageFile = $imageFile;

       if (null !== $imageFile) {
           // It is required that at least one field changes if you are using doctrine
           // otherwise the event listeners won't be called and the file is lost
           $this->updatedAt = new \DateTimeImmutable();
       }
   }

   public function getImageFile(): ?File
   {
       return $this->imageFile;
   }

   public function setImageName(?string $imageName): void
   {
       $this->imageName = $imageName;
   }

   public function getImageName(): ?string
   {
       return $this->imageName;
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
}
