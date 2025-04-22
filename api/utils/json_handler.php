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
    public function readData(string $filename): array
    {
        $filePath = $this->dataDirectory . DIRECTORY_SEPARATOR . $filename;
        if (!file_exists($filePath)) {
            throw new ErrorException("File not found");
        }
        $fp = fopen($filePath, "r");
        if ($fp === false) {
            throw new ErrorException("Failed to open file");
        }
        try {
            if (flock($fp, LOCK_SH)) {
                $json = json_decode(file_get_contents($filePath), true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new ErrorException(
                        "JSON decode error: " . json_last_error_msg()
                    );
                }
                flock($fp, LOCK_UN);
                return $json;
            } else {
                throw new ErrorException(
                    "Failed to acquire lock to read the file"
                );
            }
        } catch (Exception $e) {
            error_log("Error reading file");
            throw $e;
        } finally {
            fclose($fp);
        }
    }
    /**
     * @param array<string,mixed> $data
     */
    public function writeData(string $filename, array $data): void
    {
        $filePath = $this->dataDirectory . DIRECTORY_SEPARATOR . $filename;
        if (!file_exists($filePath)) {
            //if the file doesn't exist, we create an empty one
            file_put_contents($filePath, json_encode([]));
        }
        $fp = fopen($filePath, "w");
        if ($fp === false) {
            throw new ErrorException("Failed to open file");
        }
        try {
            if (flock($fp, LOCK_EX)) {
                //atomic writing system: we write to a temporary file, and only we rename it to the intended file name when we're sure the writing was successfully completed
                $tempFile = $filePath . ".tmp";
                file_put_contents(
                    $tempFile,
                    json_encode($data, JSON_PRETTY_PRINT)
                );
                rename($tempFile, $filePath);
                flock($fp, LOCK_UN);
            } else {
                throw new ErrorException(
                    "Failed to acquire lock to write data to the file"
                );
            }
        } catch (Exception $e) {
            error_log("Error writing file");
            throw $e;
        } finally {
            fclose($fp);
        }
    }
}
