<?php

declare(strict_types=1);

namespace Codelicia\Xulieta\ValueObject;

final class SampleCode
{
    private string $file;
    private string $language;
    private int $position;
    private string $code;

    public function __construct(string $file, string $language, int $position, string $code)
    {
        $this->file     = $file;
        $this->language = $language;
        $this->position = $position;
        $this->code     = $code;
    }

    public function file(): string
    {
        return $this->file;
    }

    public function position(): int
    {
        return $this->position;
    }

    public function code(): string
    {
        return $this->code;
    }

    public function language(): string
    {
        return $this->language;
    }
}
