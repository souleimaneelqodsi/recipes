<?php class PhotoController implements Controller
{
    private $photo_schema;

    public function __construct($photo_schema)
    {
        $this->photo_schema = $photo_schema;
    }
    #[\Override]
    public function dispatch($method, array $path): void {}

    public function upload(array $data) {}
}
