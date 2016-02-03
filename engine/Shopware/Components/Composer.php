<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

namespace Shopware\Components;

use Composer\Script\Event;

class Composer
{
    const PACKAGE_NAME = 'shopware/shopware';
    const VERSION_FILE = 'engine/Shopware/Application.php';

    /**
     * @param Event $event
     */
    public static function replaceVersionString(Event $event)
    {
        $composer = $event->getComposer();

        $packages = $composer->getRepositoryManager()->getLocalRepository()->getCanonicalPackages();
        /** @var \Composer\Package\CompletePackage $package */
        foreach ($packages as $package) {
            if ($package->getName() === self::PACKAGE_NAME) {
                $version = $package->getPrettyVersion();
                break;
            }
        }

        if (!isset($version)) {
            throw new \RuntimeException();
        }


        $vendorDir = $composer->getConfig()->get('vendor-dir');

        $file = $vendorDir.DIRECTORY_SEPARATOR.self::PACKAGE_NAME.DIRECTORY_SEPARATOR.self::VERSION_FILE;

        if (!is_file($file)) {
            throw new \RuntimeException(sprintf("Unable to replace Version. Could not find file: %s", $file));
        }

        $search = array(
            "___VERSION___",
            "___VERSION_TEXT___",
            "___REVISION___"
        );

        $replace = array(
            $version,
            $version,
            'GIT',
        );

        $fileContents = file_get_contents($file);
        $fileContents = str_replace($search, $replace, $fileContents);
        file_put_contents($file, $fileContents);
    }
}
