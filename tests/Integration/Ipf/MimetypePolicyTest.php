<?php

declare(strict_types=1);

namespace App\Tests\Integration\Ipf;

use App\Auth\IcmsUser;
use App\Ipf\Http\LegacyFormRejected;
use App\Legacy\LegacyRuntime;
use App\Tests\Support\LegacyAppTestCase;
use App\Tests\Support\TestSite;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Gate;
use Modules\System\Generated\Mimetype\Mimetype;
use Modules\System\Generated\Mimetype\MimetypeRequest;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

/**
 * The generated policy against the legacy permission storage, and the
 * validation switch.
 */
#[RunTestsInSeparateProcesses]
#[Group('spec:ipf-policies')]
final class MimetypePolicyTest extends LegacyAppTestCase
{
    private const ALPHA = 124;

    protected function setUp(): void
    {
        parent::setUp();
        $this->app->make(LegacyRuntime::class)->bootTo('online');
    }

    /**
     * As admin.php decides: webmasters, and system administrators holding the
     * mimetype function's category.
     */
    public function testAdministrationFollowsTheSystemAdminRights(): void
    {
        self::assertTrue(Gate::forUser($this->user(TestSite::WEBMASTER_UNAME))->allows('viewAny', Mimetype::class));
        self::assertTrue(Gate::forUser($this->user(TestSite::MIMETYPEADMIN_UNAME))->allows('update', [Mimetype::class, self::ALPHA]));
        self::assertFalse(Gate::forUser($this->user(TestSite::MEDIAADMIN_UNAME))->allows('viewAny', Mimetype::class), 'another function\'s category');
        self::assertFalse(Gate::forUser($this->user(TestSite::MEMBER_UNAME))->allows('delete', [Mimetype::class, self::ALPHA]));
        self::assertFalse(Gate::forUser(null)->allows('create', Mimetype::class), 'anonymous');
    }

    /**
     * Item permissions come from group_permission: ALPHA is granted to
     * groups 1 and 2, not to anonymous visitors (3).
     */
    public function testItemPermissionsAreTheHandlersGroupPermissions(): void
    {
        self::assertTrue(Gate::forUser($this->user(TestSite::MEMBER_UNAME))->allows('permission', [Mimetype::class, 'use_extension', self::ALPHA]));
        self::assertFalse(Gate::forUser(null)->allows('permission', [Mimetype::class, 'use_extension', self::ALPHA]));
        self::assertFalse(Gate::forUser($this->user(TestSite::WEBMASTER_UNAME))->allows('permission', [Mimetype::class, 'no_such_permission', self::ALPHA]));
    }

    /**
     * ICMS_SYSTEM_MIMETYPE_VALIDATION=legacy: submitted keys pass unvalidated,
     * for the handler's cleanVars() to check.
     */
    public function testTheValidationSwitchLeavesTheChecksToTheHandler(): void
    {
        config(['icms_modules.system.resources.mimetype.validation' => 'legacy']);

        $request = MimetypeRequest::createFrom(Request::create('/', 'POST', ['extension' => '', 'dirname' => ['no-such-module'], 'op' => 'addmimetype']));
        $request->setContainer($this->app)->setRedirector($this->app->make(Redirector::class));

        try {
            $request->validateResolved();
        } catch (LegacyFormRejected $e) {
            self::fail('Validation ran: ' . implode(', ', $e->errors));
        }

        self::assertSame(['extension' => '', 'dirname' => ['no-such-module']], $request->values());
    }

    private function user(string $uname): IcmsUser
    {
        $users = \icms::handler('icms_member')->getUsers(new \Icms\Db\Criteria\Item('uname', $uname));
        self::assertCount(1, $users, $uname);

        return IcmsUser::fromLegacy($users[0]);
    }
}
