<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CommentRepository")
 */
class Comment
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $mark;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="comments")
     * @ORM\JoinColumn(nullable=false)
     */
    private $author;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="commentsBack")
     * @ORM\JoinColumn(nullable=false)
     */
    private $targetingUser;

    /**
     * @ORM\Column(type="string", length=30000, nullable=true)
     */
    private $comment;


    public function getId()
    {
        return $this->id;
    }



    public function getMark(): ?float
    {
        return $this->mark;
    }

    public function setMark(?float $mark): self
    {
        $this->mark = $mark;

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

    public function getTargetingUser(): ?User
    {
        return $this->targetingUser;
    }

    public function setTargetingUser(?User $targetingUser): self
    {
        $this->targetingUser = $targetingUser;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

}
