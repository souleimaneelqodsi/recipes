<?php

class JSONHandler
{
    private $dataDirectory;

    public function __construct(string $directory)
    {
        $this->dataDirectory = $directory;
    }

    public function readData(string $filename): array {}
    public function writeData(string $filename, array $data): void {}
}
