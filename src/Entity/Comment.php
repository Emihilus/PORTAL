<?php

namespace App\Entity;

use App\Entity\Auction;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\CommentRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity(repositoryClass=CommentRepository::class)
 * @ORM\HasLifecycleCallbacks()
 */
class Comment
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="text")
     * 
     */
    private $content;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime")
     */
    private $modifiedAt;

     /**
     * @ORM\ManyToOne(targetEntity=Auction::class, inversedBy="comments")
     * @ORM\JoinColumn(nullable=true)
     */
    private $auction;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="userComments")
     * @ORM\JoinColumn(nullable=false)
     */
    private $byUser;

    /**
     * @ORM\ManyToMany(targetEntity=User::class, inversedBy="likedComments")
     * @ORM\JoinTable(name="likes")
     */
    private $likedBy;

    /**
     * @ORM\ManyToMany(targetEntity=User::class, inversedBy="dislikedComments")
     * @ORM\JoinTable(name="dislikes")
     */
    private $dislikedBy;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $value = -2;

    /**
     * @ORM\ManyToOne(targetEntity=Comment::class, inversedBy="replies")
     */
    private $replyTo;

    /**
     * @ORM\OneToMany(targetEntity=Comment::class, mappedBy="replyTo")
     */
    private $replies;

    /**
     * @ORM\Column(type="boolean")
     */
    private $notificationHandled;

    public function __construct()
    {
        $this->likedBy = new ArrayCollection();
        $this->dislikedBy = new ArrayCollection();
        $this->replies = new ArrayCollection();
    }
    
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getModifiedAt(): ?\DateTimeInterface
    {
        return $this->modifiedAt;
    }

    public function setModifiedAt(\DateTimeInterface $modifiedAt): self
    {
        $this->modifiedAt = $modifiedAt;

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

    /**
     * @ORM\PrePersist
     */
    public function autoSetCreatedAt()
    {
        $this->createdAt = new \DateTime();
        $this->modifiedAt = new \DateTime();
    }

    /**
     * @ORM\PreUpdate
     */
    public function autoSetModifiedAt()
    {
        $this->modifiedAt = new \DateTime();
    }

    /**
     * @return Collection|User[]
     */
    public function getLikedBy(): Collection
    {
        return $this->likedBy;
    }

    public function addLikedBy(User $likedBy): self
    {
        if (!$this->likedBy->contains($likedBy)) {
            $this->likedBy[] = $likedBy;
        }

        return $this;
    }

    public function removeLikedBy(User $likedBy): self
    {
        $this->likedBy->removeElement($likedBy);

        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getDislikedBy(): Collection
    {
        return $this->dislikedBy;
    }

    public function addDislikedBy(User $dislikedBy): self
    {
        if (!$this->dislikedBy->contains($dislikedBy)) {
            $this->dislikedBy[] = $dislikedBy;
        }

        return $this;
    }

    public function removeDislikedBy(User $dislikedBy): self
    {
        $this->dislikedBy->removeElement($dislikedBy);

        return $this;
    }

    public function getValue(): ?int
    {
        return $this->value;
    }

    public function setValue(?int $value): self
    {
        $this->value = $value;

        return $this;
    }

    public function getReplyTo(): ?self
    {
        return $this->replyTo;
    }

    public function setReplyTo(?self $replyTo): self
    {
        $this->replyTo = $replyTo;

        return $this;
    }

    /**
     * @return Collection|self[]
     */
    public function getReplies(): Collection
    {
        return $this->replies;
    }

    public function addReply(self $reply): self
    {
        if (!$this->replies->contains($reply)) {
            $this->replies[] = $reply;
            $reply->setReplyTo($this);
        }

        return $this;
    }

    public function removeReply(self $reply): self
    {
        if ($this->replies->removeElement($reply)) {
            // set the owning side to null (unless already changed)
            if ($reply->getReplyTo() === $this) {
                $reply->setReplyTo(null);
            }
        }

        return $this;
    }

    public function getNotificationHandled(): ?bool
    {
        return $this->notificationHandled;
    }

    public function setNotificationHandled(bool $notificationHandled): self
    {
        $this->notificationHandled = $notificationHandled;

        return $this;
    }


}
