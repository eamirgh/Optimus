<?php

namespace Eamirgh\Optimus\Tests\Unit;

use Eamirgh\Optimus\Security\ConcurrencyLock;
use Eamirgh\Optimus\Tests\TestCase;
use Illuminate\Support\Facades\Cache;

class ConcurrencyLockTest extends TestCase
{
    public function test_it_executes_callback_safely(): void
    {
        $lock = new ConcurrencyLock(Cache::store('array'), 5);

        $executed = false;
        $result = $lock->execute('image-hash-123', function () use (&$executed) {
            $executed = true;
            return 'processed-image-binary';
        });

        $this->assertTrue($executed);
        $this->assertEquals('processed-image-binary', $result);
    }
}
