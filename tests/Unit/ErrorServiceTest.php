<?php

namespace Tests\Unit;

use App\Services\ErrorService;
use PHPUnit\Framework\TestCase;

class ErrorServiceTest extends TestCase
{
    private function subject(): object
    {
        return new class
        {
            use ErrorService;
        };
    }

    public function test_set_error_appends_scalar_messages()
    {
        $subject = $this->subject();
        $subject->setError('a');
        $subject->setError('b');

        $this->assertSame(['a', 'b'], $subject->getErrors());
    }

    public function test_set_error_merges_an_array_without_nesting()
    {
        $subject = $this->subject();
        $subject->setError(['x', 'y']);

        $this->assertSame(['x', 'y'], $subject->getErrors());
    }

    public function test_set_error_mixes_scalars_and_arrays()
    {
        $subject = $this->subject();
        $subject->setError('a');
        $subject->setError(['b', 'c']);

        $this->assertSame(['a', 'b', 'c'], $subject->getErrors());
    }
}
