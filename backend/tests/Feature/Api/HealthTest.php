<?php

namespace Tests\Feature\Api;

use Tests\TestCase;

class HealthTest extends TestCase
{
    public function test_health_endpoint_returns_service_status(): void
    {
        $response = $this->getJson('/api/v1/health');

        $response
            ->assertOk()
            ->assertJson([
                'status' => 'ok',
                'service' => 'CampusEvent API',
                'version' => 'v1',
            ])
            ->assertJsonStructure(['status', 'service', 'version', 'time']);
    }
}
