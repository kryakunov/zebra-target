<?php

namespace App\Services\Works;

class WorkCreationResult
{
    /** @var bool */
    public $ok;

    /** @var int|null */
    public $workId;

    /** @var string|null */
    public $error;

    /**
     * @param int $workId
     * @return self
     */
    public static function success($workId)
    {
        $result = new self();
        $result->ok = true;
        $result->workId = $workId;
        $result->error = null;

        return $result;
    }

    /**
     * @param string $error
     * @return self
     */
    public static function fail($error)
    {
        $result = new self();
        $result->ok = false;
        $result->workId = null;
        $result->error = $error;

        return $result;
    }
}
