<?php class CommentController implements Controller
{
    private $comment_schema;

    public function __construct()
    {
        $this->comment_schema = new CommentSchema();
    }
    #[\Override]
    public function dispatch($method, array $path): void {}

    public function create(array $data): void {}
    public function fetch($recipe_id): void {}
}
