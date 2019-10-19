<?php

namespace App\Http\Response;

use App\Models\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Support\Collection;

/**
 * Class JsonResponseData
 *
 * @package App\Http\Response
 */
final class JsonResponseData implements Jsonable
{

    /**
     * @var array
     */
    private $data;

    /**
     * @var int
     */
    private $depth;

    /**
     * JsonResponseData constructor.
     *
     * @param array $data
     * @param int   $depth
     */
    public function __construct(array $data = [], int $depth = 1)
    {
        $this->data = $data;
        $this->depth = $depth;
    }

    /**
     * @return array
     */
    private function getData(): array
    {
        return $this->data;
    }

    /**
     * @return int
     */
    private function getDepth(): int
    {
        return $this->depth;
    }

    /**
     * Convert the object to its JSON representation.
     *
     * @param  int $options
     *
     * @return string
     */
    public function toJson($options = 0): string
    {
        return \json_encode($this->prepareForJson($this->getData()), $options);
    }

    /**
     * @param array $data
     *
     * @return array
     */
    private function prepareForJson(array $data): array
    {
        $responseArray = [];

        foreach ($data as $key => $value) {
            if ($value instanceof Collection) {
                $value = $value->all();
            }

            if ($value instanceof Arrayable) {
                $value = $value->toArray($this->getDepth());
            }

            if (\is_array($value)) {
                $value = $this->prepareForJson($value);
            }

            $responseArray[$key] = $value;
        }

        return $responseArray;
    }
}
