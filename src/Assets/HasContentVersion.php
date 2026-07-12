<?php

declare(strict_types = 1);

namespace Wallacemartinss\FilamentOnboarding\Assets;

/**
 * Versions an asset by what is inside it, not by the release it shipped with.
 *
 * Filament stamps `?v=` with the package's Composer version. That is fine for a
 * tagged release and useless everywhere else: during development the version
 * never changes, so an edited stylesheet keeps the old URL and the browser keeps
 * the old file — and a package installed from a path repository (`@dev`) has the
 * same problem in production, where a deploy would ship CSS nobody downloads.
 *
 * Hashing the file settles it: the URL changes exactly when the file does.
 */
trait HasContentVersion
{
    public function getVersion(): string
    {
        $path = $this->getPath();

        if (blank($path) || $this->isRemote() || !is_file($path)) {
            return parent::getVersion();
        }

        return substr((string) md5_file($path), 0, 12);
    }
}
