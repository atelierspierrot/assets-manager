<?php
/**
 * AssetsManager - Composer plugin
 * Copyleft (ↄ) 2013-2015 Pierre Cassat and contributors
 * <www.ateliers-pierrot.fr> - <contact@ateliers-pierrot.fr>
 * License GPL-3.0 <http://www.opensource.org/licenses/gpl-3.0.html>
 * Sources <https://github.com/atelierspierrot/assets-manager>
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

namespace AssetsManager\Composer\Util;

use \Composer\Util\Filesystem as OriginalFilesystem;
use \RecursiveDirectoryIterator;
use \RecursiveIteratorIterator;

/**
 * This class just completes the default `Composer\Util\Filesystem` with a `copy` method
 */
class Filesystem
    extends OriginalFilesystem
{

    /**
     * Exact same code as `copyThenRemove()` method but without removing
     *
     * @see \Composer\Util\Filesystem::copyThenRemove()
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
