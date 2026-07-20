<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder\Tests\Mocks;


final class MockTenantEnvironment
{

    private ?string $tenant = null;

    private ?string $school = null;


    public function enter(
        ?string $tenant,
        ?string $school
    ): void {

        $this->tenant = $tenant;
        $this->school = $school;
    }



    public function leave(): void
    {
        $this->tenant = null;
        $this->school = null;
    }



    public function current(): array
    {
        return [
            'tenant_id'=>$this->tenant,
            'school_id'=>$this->school,
        ];
    }

    public function isActive(): bool
    {
        return $this->tenant !== null;
    }
}