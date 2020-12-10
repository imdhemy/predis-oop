<?php


namespace Imdhemy\Redis\Contracts\Support;

interface Key
{
    /**
     * @return array
     */
    public function getParts(): array;

    /**
     * @return string
     */
    public function __toString(): string;
}
