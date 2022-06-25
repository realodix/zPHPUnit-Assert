<?php

namespace Realodix\NextProject\Support;

use Closure;

/**
 * @internal
 */
final class NullClosure
{
    /**
     * Creates a nullable closure.
     */
    public static function create(): Closure
    {
        return Closure::fromCallable(
            function (): void
            {
            }
        );
    }
}
