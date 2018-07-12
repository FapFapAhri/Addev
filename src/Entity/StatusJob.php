<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\StatusJobRepository")
 */
class StatusJob
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $label;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\JobRequest", mappedBy="status")
     */
    private $jobRequests;

    public function __construct()
    {
        $this->jobRequests = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(?string $label): self
    {
        $this->label = $label;

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
            $jobRequest->setStatus($this);
        }

        return $this;
    }

    public function removeJobRequest(JobRequest $jobRequest): self
    {
        if ($this->jobRequests->contains($jobRequest)) {
            $this->jobRequests->removeElement($jobRequest);
            // set the owning side to null (unless already changed)
            if ($jobRequest->getStatus() === $this) {
                $jobRequest->setStatus(null);
            }
        }

        return $this;
    }
}
