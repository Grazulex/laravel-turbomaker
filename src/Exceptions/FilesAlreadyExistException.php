<?php

declare(strict_types=1);

namespace Grazulex\LaravelTurbomaker\Exceptions;

use RuntimeException;

/**
 * Thrown when generation would overwrite existing files and the force option is not set
 */
final class FilesAlreadyExistException extends RuntimeException
{
    /**
     * @param  list<string>  $paths  The files that already exist
     */
    public function __construct(private readonly array $paths)
    {
        parent::__construct(
            'The following files already exist: '.implode(', ', $paths).'. Use --force to overwrite them.'
        );
    }

    /**
     * @return list<string>
     */
    public function getPaths(): array
    {
        return $this->paths;
    }
}
