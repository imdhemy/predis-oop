<?php


namespace Imdhemy\Redis;

class Key
{
    /**
     * @var string
     */
    protected $value;

    /**
     * @var string
     */
    private $separator;

    /**
     * Key constructor.
     * @param string $value
     * @param string $separator
     */
    public function __construct(string $value, string $separator = ':')
    {
        $this->value = $value;
        $this->separator = $separator;
    }

    /**
     * @return array
     */
    public function getParts(): array
    {
        return explode($this->separator, $this->value);
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->value;
    }
}
