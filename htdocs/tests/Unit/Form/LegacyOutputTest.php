<?php
/**
 * Characterization: the form controls and layouts render what they rendered
 * before they used templates.
 *
 * __snapshots__/legacy-form-output.json is the output of FormOutputScenarios
 * captured from the pre-3 branch before the form templates (hardcoded HTML):
 *
 *     cd <checkout of that revision> && php <this checkout>/htdocs/tests/Support/capture-form-output.php
 *
 * Markup is compared semantically (HtmlCanonicalizer). For assign() the
 * legacy keys and values must be unchanged; new keys may be added.
 *
 * @package icms\tests
 */

declare(strict_types=1);

namespace Icms\Tests\Unit\Form;

use Icms\Tests\Support\FormOutputScenarios;
use Icms\Tests\Support\HtmlCanonicalizer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class LegacyOutputTest extends TestCase
{
    /**
     * @return iterable<string, array{string}>
     */
    public static function scenarios(): iterable
    {
        foreach (array_keys(self::snapshot()) as $name) {
            yield $name => [$name];
        }
    }

    #[DataProvider('scenarios')]
    public function testOutputIsSemanticallyUnchanged(string $name): void
    {
        $scenarios = FormOutputScenarios::all();
        self::assertArrayHasKey($name, $scenarios);
        $actual = $scenarios[$name]();
        $expected = self::snapshot()[$name];

        if (str_ends_with($name, '-assign')) {
            self::assertLegacyKeys(json_decode($expected, true, 512, JSON_THROW_ON_ERROR), json_decode($actual, true, 512, JSON_THROW_ON_ERROR), $name);

            return;
        }
        self::assertSame(HtmlCanonicalizer::canonical($expected), HtmlCanonicalizer::canonical($actual), $name);
    }

    public function testEveryScenarioHasASnapshot(): void
    {
        self::assertSame(array_keys(FormOutputScenarios::all()), array_keys(self::snapshot()));
    }

    /**
     * @param array<mixed> $expected
     * @param array<mixed> $actual
     */
    private static function assertLegacyKeys(array $expected, array $actual, string $path): void
    {
        foreach ($expected as $key => $value) {
            self::assertArrayHasKey($key, $actual, "$path.$key");
            if (is_array($value)) {
                self::assertIsArray($actual[$key], "$path.$key");
                self::assertLegacyKeys($value, $actual[$key], "$path.$key");
            } elseif (is_string($value) && str_contains($value, '<')) {
                self::assertSame(HtmlCanonicalizer::canonical($value), HtmlCanonicalizer::canonical((string) $actual[$key]), "$path.$key");
            } else {
                self::assertSame($value, $actual[$key], "$path.$key");
            }
        }
    }

    /**
     * @return array<string, string>
     */
    private static function snapshot(): array
    {
        return json_decode((string) file_get_contents(__DIR__ . '/__snapshots__/legacy-form-output.json'), true, 512, JSON_THROW_ON_ERROR);
    }
}
