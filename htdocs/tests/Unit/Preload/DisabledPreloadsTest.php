<?php
/**
 * The disabled-preloads store as Icms\Preload\Handler reads it: a missing or
 * malformed store disables nothing and never fails.
 *
 * @package icms\tests
 */

declare(strict_types=1);

namespace Icms\Tests\Unit\Preload;

use Icms\Preload\Handler;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('spec:preload-activation')]
final class DisabledPreloadsTest extends TestCase
{
    private string $dir;

    protected function setUp(): void
    {
        $this->dir = sys_get_temp_dir() . '/icms-preloads-' . bin2hex(random_bytes(4));
        mkdir($this->dir);
    }

    protected function tearDown(): void
    {
        array_map('unlink', glob($this->dir . '/*') ?: []);
        rmdir($this->dir);
    }

    /**
     * @scenario Disabled preload
     */
    public function testListsTheDisabledPreloads(): void
    {
        $file = $this->store("<?php return ['disabled' => ['googleanalytics', 'debug_mode']];");

        self::assertSame(['googleanalytics', 'debug_mode'], Handler::disabledPreloads($file));
    }

    /**
     * @scenario No preload disabled
     */
    public function testMissingStoreDisablesNothing(): void
    {
        self::assertSame([], Handler::disabledPreloads($this->dir . '/preloads.php'));
        self::assertSame([], Handler::disabledPreloads(''));
    }

    /**
     * @scenario Corrupt disabled set
     */
    public function testCorruptStoreDisablesNothing(): void
    {
        self::assertSame([], Handler::disabledPreloads($this->store('<?php return [;')));
        self::assertSame([], Handler::disabledPreloads($this->store("<?php return 'debug_mode';")));
        self::assertSame([], Handler::disabledPreloads($this->store("<?php return ['disabled' => 'debug_mode'];")));
    }

    public function testIgnoresNamesThatAreNotStrings(): void
    {
        $file = $this->store("<?php return ['disabled' => ['keepalive', 3, null, ['x']]];");

        self::assertSame(['keepalive'], Handler::disabledPreloads($file));
    }

    public function testStoreLivesInTheTrustPath(): void
    {
        self::assertSame(ICMS_TRUST_PATH . '/var/preloads.php', Handler::disabledPreloadsFile());
    }

    private function store(string $code): string
    {
        $file = $this->dir . '/preloads-' . bin2hex(random_bytes(4)) . '.php';
        file_put_contents($file, $code);

        return $file;
    }
}
