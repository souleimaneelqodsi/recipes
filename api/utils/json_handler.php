<?php

class JSONHandler
{
    private $dataDirectory;

    public function __construct(string $directory)
    {
        $this->dataDirectory = $directory;
    }
    /**
     * @return array<string,mixed>
     */
    public function readData(string $filename): array {}
    /**
     * @param array<string,mixed> $data
     */
    public function writeData(string $filename, array $data): void {}
}
