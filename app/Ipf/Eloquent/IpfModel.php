<?php

declare(strict_types=1);

namespace App\Ipf\Eloquent;

use Illuminate\Database\Eloquent\Model;
use LogicException;

/**
 * Base of the generated Eloquent models of IPF resources: the legacy table on
 * the shared `icms` connection (which adds the table prefix), the legacy
 * column names, no Laravel timestamps.
 *
 * Read-only during coexistence. Writes go through the IPF handler
 * (App\Ipf\Repository\LegacyHandlerRepository), which runs the entity's
 * cleanVars(), the handler's events and the permission storage; a model
 * write would skip them, so saving or deleting a model is refused until
 * observers that replace them exist and are tested.
 */
abstract class IpfModel extends Model
{
    public const RESOURCE = '';

    public $timestamps = false;

    protected $connection = 'icms';

    /** @var list<string> */
    protected $guarded = ['*'];

    protected static function booted(): void
    {
        $refuse = static function (self $model): never {
            throw new LogicException($model::class . ' is read-only: write ' . $model::RESOURCE . ' through its IPF handler (App\Ipf\Repository\LegacyHandlerRepository).');
        };

        static::saving($refuse);
        static::deleting($refuse);
    }
}
