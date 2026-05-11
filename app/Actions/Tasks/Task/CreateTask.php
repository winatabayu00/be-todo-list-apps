<?php

namespace App\Actions\Tasks\Task;

use Winata\PackageBased\Abstracts\BaseAction;
use Winata\PackageBased\Concerns\ValidationInput;

class CreateTask extends BaseAction
{
    use ValidationInput;

    public function __construct(

        bool $usingDBTransaction = false
    )
    {
        parent::__construct($usingDBTransaction);
    }

    public function rules(): BaseAction
    {
        return $this;
    }

    public function handle(): mixed
    {
        // TODO: Implement handle() method.
    }
}
