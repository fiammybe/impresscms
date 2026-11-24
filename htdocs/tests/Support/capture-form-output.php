<?php
/**
 * Prints the output of every FormOutputScenarios scenario as JSON.
 *
 * Run from the root of the checkout whose output is captured (its vendor/
 * and htdocs/ are used); the scenario classes may come from another
 * checkout:
 *
 *     php /path/to/htdocs/tests/Support/capture-form-output.php [root]
 *
 * @package icms\tests
 */

declare(strict_types=1);

$root = $argv[1] ?? getcwd();
require $root . '/vendor/autoload.php';
require_once $root . '/htdocs/libraries/Autoloader.php';
// the environment of the checkout under test
require_once $root . '/htdocs/tests/environment.php';
require_once __DIR__ . '/TemplateEnvironment.php';
require_once __DIR__ . '/FakeIpfEntity.php';
require_once __DIR__ . '/FormOutputScenarios.php';

$output = [];
foreach (Icms\Tests\Support\FormOutputScenarios::all() as $name => $scenario) {
    try {
        $output[$name] = $scenario();
    } catch (Throwable $e) {
        $output[$name] = 'ERROR: ' . get_class($e) . ': ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine();
    }
}
echo json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), "\n";
