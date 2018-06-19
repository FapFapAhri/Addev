<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 */
class User
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=320)
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=60)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=60)
     */
    private $firstname;

    /**
     * @ORM\Column(type="string", length=60)
     */
    private $password;

    /**
     * @ORM\Column(type="boolean")
     */
    private $active;

    /**
     * @ORM\Column(type="integer")
     */
    private $adminLevel;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $lastConnection;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $registerIp;

    /**
     * @ORM\Column(type="string", length=32)
     */
    private $activationToken;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\JobRequest", mappedBy="author", orphanRemoval=true)
     */
    private $jobRequests;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Advert", mappedBy="author", orphanRemoval=true)
     */
    private $adverts;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Comment", mappedBy="author")
     */
    private $comments;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Comment", mappedBy="targetingUser", orphanRemoval=true)
     */
    private $commentsBack;

    public function __construct()
    {
        $this->jobRequests = new ArrayCollection();
        $this->adverts = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->commentsBack = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active): self
    {
        $this->active = $active;

        return $this;
    }

    public function getAdminLevel(): ?int
    {
        return $this->adminLevel;
    }

    public function setAdminLevel(int $adminLevel): self
    {
        $this->adminLevel = $adminLevel;

        return $this;
    }

    public function getLastConnection(): ?\DateTimeInterface
    {
        return $this->lastConnection;
    }

    public function setLastConnection(?\DateTimeInterface $lastConnection): self
    {
        $this->lastConnection = $lastConnection;

        return $this;
    }

    public function getRegisterIp(): ?string
    {
        return $this->registerIp;
    }

    public function setRegisterIp(string $registerIp): self
    {
        $this->registerIp = $registerIp;

        return $this;
    }

    public function getActivationToken(): ?string
    {
        return $this->activationToken;
    }

    public function setActivationToken(string $activationToken): self
    {
        $this->activationToken = $activationToken;

        return $this;
    }

    /**
     * @return Collection|JobRequest[]
     */
    public function getJobRequests(): Collection
    {
        return $this->jobRequests;
    }

    public function addJobRequest(JobRequest $jobRequest): self
    {
        if (!$this->jobRequests->contains($jobRequest)) {
            $this->jobRequests[] = $jobRequest;
            $jobRequest->setAuthor($this);
        }

        return $this;
    }

    public function removeJobRequest(JobRequest $jobRequest): self
    {
        if ($this->jobRequests->contains($jobRequest)) {
            $this->jobRequests->removeElement($jobRequest);
            // set the owning side to null (unless already changed)
            if ($jobRequest->getAuthor() === $this) {
                $jobRequest->setAuthor(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Advert[]
     */
    public function getAdverts(): Collection
    {
        return $this->adverts;
    }

    public function addAdvert(Advert $advert): self
    {
        if (!$this->adverts->contains($advert)) {
            $this->adverts[] = $advert;
            $advert->setAuthor($this);
        }

        return $this;
    }

    public function removeAdvert(Advert $advert): self
    {
        if ($this->adverts->contains($advert)) {
            $this->adverts->removeElement($advert);
            // set the owning side to null (unless already changed)
            if ($advert->getAuthor() === $this) {
                $advert->setAuthor(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Comment[]
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments[] = $comment;
            $comment->setAuthor($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->contains($comment)) {
            $this->comments->removeElement($comment);
            // set the owning side to null (unless already changed)
            if ($comment->getAuthor() === $this) {
                $comment->setAuthor(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Comment[]
     */
    public function getCommentsBack(): Collection
    {
        return $this->commentsBack;
    }

    public function addCommentsBack(Comment $commentsBack): self
    {
        if (!$this->commentsBack->contains($commentsBack)) {
            $this->commentsBack[] = $commentsBack;
            $commentsBack->setTargetingUser($this);
        }

        return $this;
    }

    public function removeCommentsBack(Comment $commentsBack): self
    {
        if ($this->commentsBack->contains($commentsBack)) {
            $this->commentsBack->removeElement($commentsBack);
            // set the owning side to null (unless already changed)
            if ($commentsBack->getTargetingUser() === $this) {
                $commentsBack->setTargetingUser(null);
            }
        }

        return $this;
    }
}
