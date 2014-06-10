<?php
/**
 * AssetsManager - Composer plugin
 * Copyleft (c) 2013-2014 Pierre Cassat and contributors
 * <www.ateliers-pierrot.fr> - <contact@ateliers-pierrot.fr>
 * License GPL-3.0 <http://www.opensource.org/licenses/gpl-3.0.html>
 * Sources <https://github.com/atelierspierrot/assets-manager>
 */

namespace AssetsManager\Composer\Util;

use \Composer\Util\Filesystem as OriginalFilesystem;

use \RecursiveDirectoryIterator,
    \RecursiveIteratorIterator;

/**
 * This class just completes the default `Composer\Util\Filesystem` with a `copy` method
 */
class Filesystem extends OriginalFilesystem
{

    /**
     * Exact same code as `copyThenRemove()` method but without removing
     *
     * @see Composer\Util\Filesystem::copyThenRemove()
     */
    public function copy($source, $target)
    {
        $it = new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS);
        $ri = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::SELF_FIRST);

        if (!file_exists($target)) {
            mkdir($target, 0777, true);
        }

        foreach ($ri as $file) {
            $targetPath = $target . DIRECTORY_SEPARATOR . $ri->getSubPathName();
            if ($file->isDir()) {
                if (!file_exists($targetPath)) {
                    mkdir($targetPath);
                }
            } else {
                copy($file->getPathname(), $targetPath);
            }
        }
    }

}

// Endfile
