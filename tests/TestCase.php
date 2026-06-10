<?php

declare(strict_types=1);

namespace Tests;

use App\Domain\ERP\Contracts\ERPRepositoryInterface;
use App\Domain\ERP\Fakes\FakeERPRepository;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

abstract class TestCase extends BaseTestCase
{
    protected FakeERPRepository $fakeErp;

    protected function setUp(): void
    {
        parent::setUp();

        $this->fakeErp = new FakeERPRepository();
        $this->app->instance(ERPRepositoryInterface::class, $this->fakeErp);

        app()['cache']->forget('spatie.permission.cache');
    }

    protected function crearRol(string $nombre): Role
    {
        return Role::firstOrCreate(['name' => $nombre, 'guard_name' => 'web']);
    }

    protected function crearPermiso(string $nombre): Permission
    {
        return Permission::firstOrCreate(['name' => $nombre, 'guard_name' => 'web']);
    }
}

