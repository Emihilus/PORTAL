<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Table(name="users")
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @UniqueEntity(fields={"username"}, message="There is already an account with this username")
 * @UniqueEntity(fields={"Email"}, message="There is already an account with this email")
 */
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private $username;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     */
    private $Email;

    /**
     * @ORM\OneToMany(targetEntity=Auction::class, mappedBy="byUser")
     */
    private $Auctions;

    /**
     * @ORM\OneToMany(targetEntity=Offer::class, mappedBy="byUser")
     */
    private $offers;

    /**
     * @ORM\ManyToMany(targetEntity=Auction::class, inversedBy="likedByUsers")
     */
    private $likedAuctions;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isVerified = false;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isBanned = false;

    /**
     * @ORM\OneToMany(targetEntity=Comment::class, mappedBy="byUser")
     */
    private $userComments;

    /**
     * @ORM\ManyToMany(targetEntity=Comment::class, mappedBy="likedBy")
     * @ORM\JoinTable(name="likes")
     */
    private $likedComments;

    /**
     * @ORM\ManyToMany(targetEntity=Comment::class, mappedBy="dislikedBy")
     * @ORM\JoinTable(name="dislikes")
     */
    private $dislikedComments;

    /**
     * @ORM\OneToMany(targetEntity=Notification::class, mappedBy="recipientUser")
     */
    private $notifications;

    public function __construct()
    {
        $this->Auctions = new ArrayCollection();
        $this->offers = new ArrayCollection();
        $this->likedAuctions = new ArrayCollection();
        $this->userComments = new ArrayCollection();
        $this->likedComments = new ArrayCollection();
        $this->dislikedComments = new ArrayCollection();
        $this->notifications = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @deprecated since Symfony 5.3, use getUserIdentifier instead
     */
    public function getUsername(): string
    {
        return (string) $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->username;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Returning a salt is only needed, if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     *
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getEmail(): ?string
    {
        return $this->Email;
    }

    public function setEmail(string $Email): self
    {
        $this->Email = $Email;

        return $this;
    }

    /**
     * @return Collection|Auction[]
     */
    public function getAuctions(): Collection
    {
        return $this->Auctions;
    }

    public function addAuction(Auction $auction): self
    {
        if (!$this->Auctions->contains($auction)) {
            $this->Auctions[] = $auction;
            $auction->setByUser($this);
        }

        return $this;
    }

    public function removeAuction(Auction $auction): self
    {
        if ($this->Auctions->removeElement($auction)) {
            // set the owning side to null (unless already changed)
            if ($auction->getByUser() === $this) {
                $auction->setByUser(null);
            }
        }

        return $this;
    }

    public function __toString()
    {
        return $this->username;
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
            $offer->setByUser($this);
        }

        return $this;
    }

    public function removeOffer(Offer $offer): self
    {
        if ($this->offers->removeElement($offer)) {
            // set the owning side to null (unless already changed)
            if ($offer->getByUser() === $this) {
                $offer->setByUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Auction[]
     */
    public function getLikedAuctions(): Collection
    {
        return $this->likedAuctions;
    }

    public function addLikedAuction(Auction $likedAuction): self
    {
        if (!$this->likedAuctions->contains($likedAuction)) {
            $this->likedAuctions[] = $likedAuction;
        }

        return $this;
    }

    public function removeLikedAuction(Auction $likedAuction): self
    {
        $this->likedAuctions->removeElement($likedAuction);

        return $this;
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): self
    {
        $this->isVerified = $isVerified;

        return $this;
    }

    public function isBanned(): bool
    {
        return $this->isBanned;
    }

    public function setIsBanned(bool $isBanned): self
    {
        $this->isBanned = $isBanned;

        return $this;
    }

    /**
     * @return Collection|Comment[]
     */
    public function getUserComments(): Collection
    {
        return $this->userComments;
    }

    public function addUserComment(Comment $userComment): self
    {
        if (!$this->userComments->contains($userComment)) {
            $this->userComments[] = $userComment;
            $userComment->setByUser($this);
        }

        return $this;
    }

    public function removeUserComment(Comment $userComment): self
    {
        if ($this->userComments->removeElement($userComment)) {
            // set the owning side to null (unless already changed)
            if ($userComment->getByUser() === $this) {
                $userComment->setByUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Comment[]
     */
    public function getLikedComments(): Collection
    {
        return $this->likedComments;
    }

    public function addLikedComment(Comment $likedComment): self
    {
        if (!$this->likedComments->contains($likedComment)) {
            $this->likedComments[] = $likedComment;
            $likedComment->addLikedBy($this);
        }

        return $this;
    }

    public function removeLikedComment(Comment $likedComment): self
    {
        if ($this->likedComments->removeElement($likedComment)) {
            $likedComment->removeLikedBy($this);
        }

        return $this;
    }

    /**
     * @return Collection|Comment[]
     */
    public function getDislikedComments(): Collection
    {
        return $this->dislikedComments;
    }

    public function addDislikedComment(Comment $dislikedComment): self
    {
        if (!$this->dislikedComments->contains($dislikedComment)) {
            $this->dislikedComments[] = $dislikedComment;
            $dislikedComment->addDislikedBy($this);
        }

        return $this;
    }

    public function removeDislikedComment(Comment $dislikedComment): self
    {
        if ($this->dislikedComments->removeElement($dislikedComment)) {
            $dislikedComment->removeDislikedBy($this);
        }

        return $this;
    }

    /**
     * @return Collection|Notification[]
     */
    public function getNotifications(): Collection
    {
        return $this->notifications;
    }

    public function addNotification(Notification $notification): self
    {
        if (!$this->notifications->contains($notification)) {
            $this->notifications[] = $notification;
            $notification->setRecipientUser($this);
        }

        return $this;
    }

    public function removeNotification(Notification $notification): self
    {
        if ($this->notifications->removeElement($notification)) {
            // set the owning side to null (unless already changed)
            if ($notification->getRecipientUser() === $this) {
                $notification->setRecipientUser(null);
            }
        }

        return $this;
    }
}
