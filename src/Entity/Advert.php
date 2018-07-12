<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AdvertRepository")
 */
class Advert
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=150)
     */
    private $title;

    /**
     * @ORM\Column(type="string", length=30000)
     */
    private $content;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $postDate;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\JobRequest", mappedBy="advert", orphanRemoval=true)
     */
    private $jobRequests;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="adverts")
     * @ORM\JoinColumn(nullable=false)
     */
    private $author;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $lastModified;

    /**
     * @ORM\Column(type="string", length=30)
     */
    private $contractType;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Comment", mappedBy="advert")
     */
    private $comments;




    public function __construct()
    {
        $this->jobRequests = new ArrayCollection();
        $this->comments = new ArrayCollection();
    }


    public function getId()
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

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getPostDate(): ?\DateTimeInterface
    {
        return $this->postDate;
    }

    public function setPostDate(?\DateTimeInterface $postDate): self
    {
        $this->postDate = $postDate;

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
            $jobRequest->setAdvert($this);
        }

        return $this;
    }

    public function removeJobRequest(JobRequest $jobRequest): self
    {
        if ($this->jobRequests->contains($jobRequest)) {
            $this->jobRequests->removeElement($jobRequest);
            // set the owning side to null (unless already changed)
            if ($jobRequest->getAdvert() === $this) {
                $jobRequest->setAdvert(null);
            }
        }

        return $this;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): self
    {
        $this->author = $author;

        return $this;
    }

    public function getLastModified(): ?\DateTimeInterface
    {
        return $this->lastModified;
    }

    public function setLastModified(?\DateTimeInterface $lastModified): self
    {
        $this->lastModified = $lastModified;

        return $this;
    }

    public function getContractType(): ?string
    {
        return $this->contractType;
    }

    public function setContractType(string $contractType): self
    {
        $this->contractType = $contractType;

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
            $comment->setAdvert($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->contains($comment)) {
            $this->comments->removeElement($comment);
            // set the owning side to null (unless already changed)
            if ($comment->getAdvert() === $this) {
                $comment->setAdvert(null);
            }
        }

        return $this;
    }

}
