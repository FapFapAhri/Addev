<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\JobRequestRepository")
 */
class JobRequest
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="jobRequests")
     * @ORM\JoinColumn(nullable=false)
     */
    private $author;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Advert", inversedBy="jobRequests")
     * @ORM\JoinColumn(nullable=false)
     */
    private $advert;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateApply;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\StatusJob", inversedBy="jobRequests")
     */
    private $status;


    public function getId()
    {
        return $this->id;
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

    public function getAdvert(): ?Advert
    {
        return $this->advert;
    }

    public function setAdvert(?Advert $advert): self
    {
        $this->advert = $advert;

        return $this;
    }

    public function getDateApply(): ?\DateTimeInterface
    {
        return $this->dateApply;
    }

    public function setDateApply(?\DateTimeInterface $dateApply): self
    {
        $this->dateApply = $dateApply;

        return $this;
    }

    public function getStatus(): ?StatusJob
    {
        return $this->status;
    }

    public function setStatus(?StatusJob $status): self
    {
        $this->status = $status;

        return $this;
    }

}
