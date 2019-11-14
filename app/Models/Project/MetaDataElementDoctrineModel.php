<?php

namespace App\Models\Project;

use App\Models\AbstractDoctrineModel;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class MetaDataElementDoctrineModel
 *
 * @ORM\Table(name="meta_data_elements")
 * @ORM\Entity(repositoryClass="App\Repositories\Project\MetaDataElementDoctrineRepository")
 *
 * @package App\Models\Project
 */
final class MetaDataElementDoctrineModel extends AbstractDoctrineModel implements MetaDataElementModel
{
    /**
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     *
     * @var string
     */
    private $name;

    /**
     * @ORM\Column(name="label", type="string", length=255, nullable=false)
     *
     * @var string
     */
    private $label;

    /**
     * @ORM\ManyToOne(targetEntity="App\Models\Project\ProjectDoctrineModel", inversedBy="metaDataElements", cascade={"persist"})
     *
     * @var ProjectModel
     */
    private $project;

    /**
     * @ORM\Column(name="required", type="boolean", nullable=false)
     *
     * @var bool
     */
    private $required;

    /**
     * @ORM\Column(name="in_list", type="boolean", nullable=false)
     *
     * @var bool
     */
    private $inList;

    /**
     * @ORM\Column(name="position", type="integer", nullable=false)
     *
     * @var int
     */
    private $position;

    /**
     * MetaDataElementDoctrineModel constructor.
     *
     * @param string       $name
     * @param string       $label
     * @param ProjectModel $project
     * @param bool         $required
     * @param bool         $inList
     * @param int          $position
     */
    public function __construct(
        string $name,
        string $label,
        ProjectModel $project,
        bool $required,
        bool $inList,
        int $position
    ) {
        $this->name = $name;
        $this->label = $label;
        $this->project = $project;
        $this->required = $required;
        $this->inList = $inList;
        $this->position = $position;
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setName(string $name): MetaDataElementModel
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $label
     *
     * @return MetaDataElementModel
     */
    public function setLabel(string $label): MetaDataElementModel
    {
        $this->label = $label;

        return $this;
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * @param ProjectModel $project
     *
     * @return $this
     */
    public function setProject(ProjectModel $project): MetaDataElementModel
    {
        $this->project = $project;

        return $this;
    }

    /**
     * @return ProjectModel
     */
    public function getProject(): ProjectModel
    {
        return $this->project;
    }

    /**
     * @param bool $required
     *
     * @return $this
     */
    public function setRequired(bool $required): MetaDataElementModel
    {
        $this->required = $required;

        return $this;
    }

    /**
     * @return bool
     */
    public function isRequired(): bool
    {
        return $this->required;
    }

    /**
     * @param bool $inList
     *
     * @return $this
     */
    public function setInList(bool $inList): MetaDataElementModel
    {
        $this->inList = $inList;

        return $this;
    }

    /**
     * @return bool
     */
    public function isInList(): bool
    {
        return $this->inList;
    }

    /**
     * @param int $position
     *
     * @return $this
     */
    public function setPosition(int $position): MetaDataElementModel
    {
        $this->position = $position;

        return $this;
    }

    /**
     * @return int
     */
    public function getPosition(): int
    {
        return $this->position;
    }

    /**
     * @param int $depth
     *
     * @return array
     */
    public function toArray(int $depth = 1): array
    {
        $array = [
            self::PROPERTY_NAME     => $this->getName(),
            self::PROPERTY_LABEL    => $this->getLabel(),
            self::PROPERTY_REQUIRED => $this->isRequired(),
            self::PROPERTY_IN_LIST  => $this->isInList(),
            self::PROPERTY_POSITION => $this->getPosition(),
        ];

        if ($depth > 0) {
            --$depth;

            $array[self::PROPERTY_PROJECT] = $this->getProject()->toArray($depth);
        }

        return $array;
    }
}
