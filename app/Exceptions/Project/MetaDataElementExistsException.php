<?php

namespace App\Exceptions\Project;

/**
 * Class MetaDataElementExistsException
 *
 * @package App\Exceptions\Project
 */
final class MetaDataElementExistsException extends \Exception
{
    /**
     * @var int
     */
    private $index;

    /**
     * MetaDataElementExistsException constructor.
     *
     * @param int    $index
     * @param string $message
     */
    public function __construct(int $index = 0, string $message = '')
    {
        $this->index = $index;

        parent::__construct($message);
    }

    /**
     * @return int
     */
    public function getIndex(): int
    {
        return $this->index;
    }
}
