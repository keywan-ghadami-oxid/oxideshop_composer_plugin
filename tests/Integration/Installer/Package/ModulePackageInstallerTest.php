<?php
/**
 * This file is part of OXID eShop Composer plugin.
 *
 * OXID eShop Composer plugin is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * OXID eShop Composer plugin is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OXID eShop Composer plugin.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @link      http://www.oxid-esales.com
 * @copyright (C) OXID eSales AG 2003-2016
 * @version   OXID eShop Composer plugin
 */

namespace OxidEsales\ComposerPlugin\Tests\Integration\Installer\Package;

use Composer\IO\IOInterface;
use Composer\IO\NullIO;
use Composer\Package\Package;
use Composer\Package\PackageInterface;
use OxidEsales\ComposerPlugin\Installer\Package\AbstractPackageInstaller;
use OxidEsales\ComposerPlugin\Installer\Package\ModulePackageInstaller;
use OxidEsales\ComposerPlugin\Utilities\VfsFileStructureOperator;
use org\bovigo\vfs\vfsStream;
use Webmozart\PathUtil\Path;

class ModulePackageInstallerTest extends AbstractPackageInstallerTest
{
    protected function getPackageInstaller($packageName, $version = '1.0.0', $extra = [])
    {
        $package = new Package($packageName, $version, $version);
        $package->setExtra($extra);

        return new ModulePackageInstaller(
            new NullIO(),
            $this->getVirtualShopSourcePath(),
            $package
        );
    }
    
    public function testModuleNotInstalledByDefault()
    {
        $installer = $this->getPackageInstaller('test-vendor/test-package');

        $this->assertFalse($installer->isInstalled());
    }

    public function testModuleIsInstalledIfAlreadyExistsInShop()
    {
        $this->setupVirtualProjectRoot('source/modules/test-vendor/test-package', [
            'metadata.php' => '<?php'
        ]);

        $installer = $this->getPackageInstaller('test-vendor/test-package');

        $this->assertTrue($installer->isInstalled());
    }

    public function testModuleIsInstalledAfterInstallProcess()
    {
        $this->setupVirtualProjectRoot('vendor/test-vendor/test-package', [
            'metadata.php' => '<?php'
        ]);

        $installer = $this->getPackageInstaller('test-vendor/test-package');
        $installer->install($this->getVirtualFileSystemRootPath('vendor/test-vendor/test-package'));

        $this->assertTrue($installer->isInstalled());
    }

    public function testModuleFilesAreCopiedAfterInstallProcess()
    {
        $this->setupVirtualProjectRoot('vendor/test-vendor/test-package', [
            'metadata.php' => '<?php'
        ]);

        $installer = $this->getPackageInstaller('test-vendor/test-package');
        $installer->install($this->getVirtualFileSystemRootPath('vendor/test-vendor/test-package'));

        $this->assertVirtualFileEquals(
            'vendor/test-vendor/test-package/metadata.php',
            'source/modules/test-vendor/test-package/metadata.php'
        );
    }

    public function testModuleFilesAreCopiedAfterInstallProcessWithSameSourceDirectory()
    {
        $this->setupVirtualProjectRoot('vendor/test-vendor/test-package', [
            'metadata.php' => '<?php'
        ]);

        $installer = $this->getPackageInstaller('test-vendor/test-package', '1.0.0', [
            'oxideshop' => [
                'source-directory' => ''
            ]
        ]);
        $installer->install($this->getVirtualFileSystemRootPath('vendor/test-vendor/test-package'));

        $this->assertVirtualFileEquals(
            'vendor/test-vendor/test-package/metadata.php',
            'source/modules/test-vendor/test-package/metadata.php'
        );
    }

    public function testModuleFilesAreCopiedAfterInstallProcessWithSameTargetDirectory()
    {
        $this->setupVirtualProjectRoot('vendor/test-vendor/test-package', [
            'metadata.php' => '<?php'
        ]);

        $installer = $this->getPackageInstaller('test-vendor/test-package', '1.0.0', [
            'oxideshop' => [
                'target-directory' => 'test-vendor/test-package'
            ]
        ]);
        $installer->install($this->getVirtualFileSystemRootPath('vendor/test-vendor/test-package'));

        $this->assertVirtualFileEquals(
            'vendor/test-vendor/test-package/metadata.php',
            'source/modules/test-vendor/test-package/metadata.php'
        );
    }

    public function testModuleFilesAreCopiedAfterInstallProcessWithSameSourceDirectoryAndSameTargetDirectory()
    {
        $this->setupVirtualProjectRoot('vendor/test-vendor/test-package', [
            'metadata.php' => '<?php'
        ]);

        $installer = $this->getPackageInstaller('test-vendor/test-package', '1.0.0', [
            'oxideshop' => [
                'source-directory' => '',
                'target-directory' => 'test-vendor/test-package'
            ]
        ]);
        $installer->install($this->getVirtualFileSystemRootPath('vendor/test-vendor/test-package'));

        $this->assertVirtualFileEquals(
            'vendor/test-vendor/test-package/metadata.php',
            'source/modules/test-vendor/test-package/metadata.php'
        );
    }

    public function testModuleFilesAreCopiedAfterInstallProcessWithCustomSourceDirectory()
    {
        $this->setupVirtualProjectRoot('vendor/test-vendor/test-package/custom-root', [
            'metadata.php' => '<?php'
        ]);

        $installer = $this->getPackageInstaller('test-vendor/test-package', '1.0.0', [
            'oxideshop' => [
                'source-directory' => 'custom-root',
            ]
        ]);
        $installer->install($this->getVirtualFileSystemRootPath('vendor/test-vendor/test-package'));

        $this->assertVirtualFileEquals(
            'vendor/test-vendor/test-package/custom-root/metadata.php',
            'source/modules/test-vendor/test-package/metadata.php'
        );
    }

    public function testModuleFilesAreCopiedAfterInstallProcessWithCustomTargetDirectory()
    {
        $this->setupVirtualProjectRoot('vendor/test-vendor/test-package', [
            'metadata.php' => '<?php'
        ]);

        $installer = $this->getPackageInstaller('test-vendor/test-package', '1.0.0', [
            'oxideshop' => [
                'target-directory' => 'custom-vendor/custom-package',
            ]
        ]);
        $installer->install($this->getVirtualFileSystemRootPath('vendor/test-vendor/test-package'));

        $this->assertVirtualFileEquals(
            'vendor/test-vendor/test-package/metadata.php',
            'source/modules/custom-vendor/custom-package/metadata.php'
        );
    }

    public function testModuleFilesAreCopiedAfterInstallProcessWithCustomSourceDirectoryAndCustomTargetDirectory()
    {
        $this->setupVirtualProjectRoot('vendor/test-vendor/test-package/custom-root', [
            'metadata.php' => '<?php'
        ]);

        $installer = $this->getPackageInstaller('test-vendor/test-package', '1.0.0', [
            'oxideshop' => [
                'source-directory' => 'custom-root',
                'target-directory' => 'custom-vendor/custom-package',
            ]
        ]);
        $installer->install($this->getVirtualFileSystemRootPath('vendor/test-vendor/test-package'));

        $this->assertVirtualFileEquals(
            'vendor/test-vendor/test-package/custom-root/metadata.php',
            'source/modules/custom-vendor/custom-package/metadata.php'
        );
    }

    public function testBlacklistedFilesArePresentWhenNoBlacklistFilterIsDefined()
    {
        $this->setupVirtualProjectRoot('vendor/test-vendor/test-package', [
            'metadata.php' => '<?php',
            'module.php' => '<?php',
            'readme.txt' => 'readme',
        ]);

        $installer = $this->getPackageInstaller('test-vendor/test-package', '1.0.0');
        $installer->install($this->getVirtualFileSystemRootPath('vendor/test-vendor/test-package'));

        $this->assertVirtualFileExists('source/modules/test-vendor/test-package/metadata.php');
        $this->assertVirtualFileExists('source/modules/test-vendor/test-package/module.php');
        $this->assertVirtualFileExists('source/modules/test-vendor/test-package/readme.txt');
    }

    public function testBlacklistedFilesArePresentWhenEmptyBlacklistFilterIsDefined()
    {
        $this->setupVirtualProjectRoot('vendor/test-vendor/test-package', [
            'metadata.php' => '<?php',
            'module.php' => '<?php',
            'readme.txt' => 'readme',
        ]);

        $installer = $this->getPackageInstaller('test-vendor/test-package', '1.0.0', [
            'oxideshop' => [
                'blacklist-filter' => []
            ]
        ]);
        $installer->install($this->getVirtualFileSystemRootPath('vendor/test-vendor/test-package'));

        $this->assertVirtualFileExists('source/modules/test-vendor/test-package/metadata.php');
        $this->assertVirtualFileExists('source/modules/test-vendor/test-package/module.php');
        $this->assertVirtualFileExists('source/modules/test-vendor/test-package/readme.txt');
    }

    public function testBlacklistedFilesArePresentWhenDifferentBlacklistFilterIsDefined()
    {
        $this->setupVirtualProjectRoot('vendor/test-vendor/test-package', [
            'metadata.php' => '<?php',
            'module.php' => '<?php',
            'readme.txt' => 'readme',
        ]);

        $installer = $this->getPackageInstaller('test-vendor/test-package', '1.0.0', [
            'oxideshop' => [
                'blacklist-filter' => [
                    '**/*.pdf'
                ]
            ]
        ]);
        $installer->install($this->getVirtualFileSystemRootPath('vendor/test-vendor/test-package'));

        $this->assertVirtualFileExists('source/modules/test-vendor/test-package/metadata.php');
        $this->assertVirtualFileExists('source/modules/test-vendor/test-package/module.php');
        $this->assertVirtualFileExists('source/modules/test-vendor/test-package/readme.txt');
    }

    public function testBlacklistedFilesAreSkippedWhenABlacklistFilterIsDefined()
    {
        $this->setupVirtualProjectRoot('vendor/test-vendor/test-package', [
            'metadata.php' => '<?php',
            'module.php' => '<?php',
            'readme.txt' => 'readme',
        ]);

        $installer = $this->getPackageInstaller('test-vendor/test-package', '1.0.0', [
            'oxideshop' => [
                'blacklist-filter' => [
                    '**/*.txt'
                ]
            ]
        ]);
        $installer->install($this->getVirtualFileSystemRootPath('vendor/test-vendor/test-package'));

        $this->assertVirtualFileExists('source/modules/test-vendor/test-package/metadata.php');
        $this->assertVirtualFileExists('source/modules/test-vendor/test-package/module.php');
        $this->assertVirtualFileNotExists('source/modules/test-vendor/test-package/readme.txt');
    }

    public function testComplexCase()
    {
        $this->setupVirtualProjectRoot('vendor/test-vendor/test-package/custom-root', [
            'metadata.php' => '<?php',
            'module.php' => '<?php',
            'readme.txt' => 'readme',
            'readme.pdf' => 'PDF',
            'documentation/readme.txt' => 'readme',
            'documentation/example.php' => '<?php',
            'model/model.php' => '<?php',
        ]);

        $installer = $this->getPackageInstaller('test-vendor/test-package', '1.0.0', [
            'oxideshop' => [
                'source-directory' => 'custom-root',
                'target-directory' => 'custom-out',
                'blacklist-filter' => [
                    '**/*.txt',
                    '**/*.pdf',
                    'documentation/**/*.*',
                ]
            ]
        ]);
        $installer->install($this->getVirtualFileSystemRootPath('vendor/test-vendor/test-package'));

        $this->assertTrue($installer->isInstalled());
        $this->assertVirtualFileEquals(
            'vendor/test-vendor/test-package/custom-root/metadata.php',
            'source/modules/custom-out/metadata.php'
        );
        $this->assertVirtualFileEquals(
            'vendor/test-vendor/test-package/custom-root/module.php',
            'source/modules/custom-out/module.php'
        );
        $this->assertVirtualFileEquals(
            'vendor/test-vendor/test-package/custom-root/model/model.php',
            'source/modules/custom-out/model/model.php'
        );
        $this->assertVirtualFileNotExists('source/modules/custom-out/readme.txt');
        $this->assertVirtualFileNotExists('source/modules/custom-out/readme.pdf');
        $this->assertVirtualFileNotExists('source/modules/custom-out/documentation');
        $this->assertVirtualFileNotExists('source/modules/custom-out/documentation/readme.txt');
        $this->assertVirtualFileNotExists('source/modules/custom-out/documentation/example.php');
    }
}
