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
            $emptyData = [];
            $this->writeData($filename, $emptyData);
            return $emptyData;
        }
        $fp = fopen($filePath, "r");
        if ($fp === false) {
            error_log("Failed to open file");
            throw new ErrorException("Failed to open file");
        }

        try {
            if (flock($fp, LOCK_SH)) {
                $json = json_decode(file_get_contents($filePath), true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    error_log("Failed to decode JSON");
                    throw new ErrorException(
                        json_last_error_msg() . " in file " . $filePath
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
            error_log("readData finished");
            fclose($fp);
        }
    }
    /**
     * @param array<string,mixed> $data
     */
    public function writeData(string $filename, array $data): void
    {
        $filePath = $this->dataDirectory . DIRECTORY_SEPARATOR . $filename;

        $fp = @fopen($filePath, "w");

        if ($fp === false) {
            $error = error_get_last();
            error_log(
                "Failed to open file '$filePath' for writing. Error: " .
                    ($error["message"] ?? "Unknown error")
            );
            throw new ErrorException(
                "Failed to open file '$filePath' for writing. Error: " .
                    ($error["message"] ?? "Unknown error")
            );
        }

        try {
            if (flock($fp, LOCK_EX)) {
                $jsonData = json_encode($data, JSON_PRETTY_PRINT);
                if ($jsonData === false) {
                    error_log("JSON Encode Error: " . json_last_error_msg());
                    throw new ErrorException("Failed to encode data to JSON.");
                }

                $bytesWritten = fwrite($fp, $jsonData);

                if ($bytesWritten === false) {
                    $error = error_get_last();
                    error_log(
                        "Failed to write data to file '$filePath'. fwrite returned false. Error: " .
                            ($error["message"] ?? "Unknown error")
                    );
                    throw new ErrorException(
                        "Failed to write data to file '$filePath'. Error: " .
                            ($error["message"] ?? "Unknown error")
                    );
                }

                flock($fp, LOCK_UN);
            } else {
                throw new ErrorException(
                    "Failed to acquire lock to write data to the file '$filePath'."
                );
            }
        } catch (Exception $e) {
            error_log(
                "Error during file write operation for '$filePath': " .
                    $e->getMessage()
            );

            fclose($fp);
            throw $e;
        }
    }
}
