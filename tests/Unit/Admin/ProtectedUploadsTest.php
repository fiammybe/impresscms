<?php

declare(strict_types=1);

namespace App\Tests\Unit\Admin;

use App\Controller\System\AssetsController;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

/**
 * The files of the upload folder that the asset manager never deletes.
 */
#[Group('spec:system-assets-admin')]
final class ProtectedUploadsTest extends TestCase
{
    /**
     * @return array<string, array{string, bool}>
     */
    public static function names(): array
    {
        return [
            'directory guard' => ['index.html', true],
            'directory guard, other case' => ['INDEX.HTML', true],
            'fallback avatar' => ['blank.gif', true],
            'access file' => ['.htaccess', true],
            'hidden file' => ['.gitignore', true],
            'other index' => ['index.htm', false],
            'avatar' => ['avatar-user.gif', false],
            'dot inside the name' => ['photo.index.html', false],
        ];
    }

    /**
     * @scenario Protected file in the tree
     */
    #[DataProvider('names')]
    public function testProtectedNames(string $name, bool $protected): void
    {
        self::assertSame($protected, AssetsController::isProtected($name));
    }
}
