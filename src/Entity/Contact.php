<?php

namespace App\Entity;

use App\Repository\ContactRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ContactRepository::class)]
class Contact
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?Client $client_id = null;

    #[ORM\Column(length: 50)]
    private ?string $contact_type = null;

    #[ORM\Column(length: 255)]
    private ?string $contact_detail = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updated_at = null;

    #[ORM\ManyToOne(inversedBy: 'contacts')]
    private ?User $userr = null;

    #[ORM\ManyToOne(inversedBy: 'contactos')]
    private ?User $useros = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getClientId(): ?Client
    {
        return $this->client_id;
    }

    public function setClientId(?Client $client_id): static
    {
        $this->client_id = $client_id;

        return $this;
    }

    public function getContactType(): ?string
    {
        return $this->contact_type;
    }

    public function setContactType(string $contact_type): static
    {
        $this->contact_type = $contact_type;

        return $this;
    }

    public function getContactDetail(): ?string
    {
        return $this->contact_detail;
    }

    public function setContactDetail(string $contact_detail): static
    {
        $this->contact_detail = $contact_detail;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(?\DateTimeImmutable $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updated_at): static
    {
        $this->updated_at = $updated_at;

        return $this;
    }

    public function getUserr(): ?User
    {
        return $this->userr;
    }

    public function setUserr(?User $userr): static
    {
        $this->userr = $userr;

        return $this;
    }

    public function getUseros(): ?User
    {
        return $this->useros;
    }

    public function setUseros(?User $useros): static
    {
        $this->useros = $useros;

        return $this;
    }
}
