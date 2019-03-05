<?php

namespace WSD\Spark\PhpUnitHelpers\Constraints;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Constraint\Constraint;
use Mockery;
Use Exception;

class ExpectationsMet
{

    public function verifyMockeryExpectations()
    {
        try {
            Mockery::getContainer()->mockery_verify();
        } catch (Exception $e) {
            return false;
        }

        return true;
    }

    public function constraint(): Constraint
    {
        return Assert::callback([$this, 'verifyMockeryExpectations']);
    }

}
