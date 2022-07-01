<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\MovieRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;

/**
 * @ApiResource(
 *     collectionOperations={"get","post"},
 *     itemOperations={"get"},
 *     denormalizationContext={"groups"={"movies:write"}}
 * )
 * @ORM\Entity(repositoryClass=MovieRepository::class)
 */
class Movie
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"movies:write"})
     */
    private $name;

    /**
     * @ORM\ManyToMany(targetEntity=Actor::class, cascade={"persist"})
     */
    private $casts;

    /**
     * @ORM\ManyToOne(targetEntity=Director::class, cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $director;

    /**
     * @ORM\OneToMany(targetEntity=Rating::class, mappedBy="movie", orphanRemoval=true, cascade={"persist"})
     * @Groups({"movies:write"})
     */
    private $ratings;

    public function __construct()
    {
        $this->casts = new ArrayCollection();
        $this->ratings = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    /**
     * @return Collection<int, Actor>
     */
    public function getCasts(): Collection
    {
        return $this->casts;
    }

    public function addCast(Actor $cast): self
    {
        if (!$this->casts->contains($cast)) {
            $this->casts[] = $cast;
        }

        return $this;
    }

    public function removeCast(Actor $cast): self
    {
        $this->casts->removeElement($cast);

        return $this;
    }

    private function getDirector(): ?Director
    {
        return $this->director;
    }

    /**
     * @SerializedName("director")
     * @return string|null
     */
    public function getDirectorName()
    {
        return $this->getDirector()->getName();
    }

    public function setDirector(?Director $Director): self
    {
        $this->director = $Director;

        return $this;
    }

    /**
     * @Groups({"movies:write"})
     * @SerializedName("director")
     */
    public function setDirectorByName(string $name): self
    {
        $director = (new Director())->setName($name);
        $this->setDirector($director);

        return $this;
    }

    /**
     * @return Collection<int, Rating>
     */
    private function getRatings(): Collection
    {
        return $this->ratings;
    }

    /**
     * @return \stdClass
     * @SerializedName("ratings")
     */
    public function getRatingsSerialized(): \stdClass
    {
        $ratings = (new \stdClass());
        foreach ($this->getRatings() as $rating) {
            /** @var Rating $rating */
            $ratings->{$rating->getName()} = $rating->getValue();
        }

        return $ratings;
    }

    public function addRating(Rating $rating): self
    {
        if (!$this->ratings->contains($rating)) {
            $this->ratings[] = $rating;
            $rating->setMovie($this);
        }

        return $this;
    }

    public function removeRating(Rating $rating): self
    {
        if ($this->ratings->removeElement($rating)) {
            // set the owning side to null (unless already changed)
            if ($rating->getMovie() === $this) {
                $rating->setMovie(null);
            }
        }

        return $this;
    }

    /**
     * @Groups({"movies:write"})
     * @SerializedName("ratings")
     */
    public function setRatingByObject(array $ratingObj): self
    {
        foreach ($ratingObj as $name => $value) {
            $rating = (new Rating())->setName($name)->setValue($value);
            $this->addRating($rating);
        }

        return $this;
    }
}