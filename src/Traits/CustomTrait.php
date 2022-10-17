<?php

namespace Realodix\Dessert\Traits;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Constraint\IsEqual;
use PHPUnit\Framework\Constraint\IsEqualIgnoringCase;
use PHPUnit\Framework\Constraint\LogicalNot;
use Realodix\Dessert\Exceptions\InvalidActualValue;
use Realodix\Dessert\Support\Validator;

trait CustomTrait
{
    public function stringEquals(string $expected, string $message = ''): self
    {
        if (! is_string($this->actual)) {
            throw new InvalidActualValue;
        }

        if (Validator::isJson($this->actual)) {
            $this->jsonStringEqualsJsonString($expected, $message);

            return $this;
        }

        if (Validator::isXml($this->actual)) {
            $this->xmlStringEqualsXmlString($expected, $message);

            return $this;
        }

        $this->equals($expected, $message);

        return $this;
    }

    public function stringNotEquals(string $expected, string $message = ''): self
    {
        if (! is_string($this->actual)) {
            throw new InvalidActualValue;
        }

        if (Validator::isJson($this->actual)) {
            $this->jsonStringNotEqualsJsonString($expected, $message);

            return $this;
        }

        if (Validator::isXml($this->actual)) {
            $this->xmlStringNotEqualsXmlString($expected, $message);

            return $this;
        }

        $this->notEquals($expected, $message);

        return $this;
    }

    /**
     * Asserts that the contents of one file is equal to the string.
     *
     * Reference:
     * - https://github.com/sebastianbergmann/phpunit/pull/4649
     */
    public function fileEqualsString(string $expectedString, string $message = ''): self
    {
        if (! is_string($this->actual)) {
            throw new InvalidActualValue;
        }

        $constraint = new IsEqual($expectedString);

        Assert::assertFileExists($this->actual, $message);
        Assert::assertThat(file_get_contents($this->actual), $constraint, $message);

        return $this;
    }

    public function fileNotEqualsString(string $expectedString, string $message = ''): self
    {
        if (! is_string($this->actual)) {
            throw new InvalidActualValue;
        }
        $constraint = new LogicalNot(new IsEqual($expectedString));

        Assert::assertFileExists($this->actual, $message);
        Assert::assertThat(file_get_contents($this->actual), $constraint, $message);

        return $this;
    }

    /**
     * Asserts that the contents of one file is equal to the string (ignoring case).
     */
    public function fileEqualsStringIgnoringCase(string $expectedString, string $message = ''): self
    {
        if (! is_string($this->actual)) {
            throw new InvalidActualValue;
        }

        Assert::assertFileExists($this->actual, $message);

        $constraint = new IsEqualIgnoringCase($expectedString);
        Assert::assertThat(file_get_contents($this->actual), $constraint, $message);

        return $this;
    }

    public function fileNotEqualsStringIgnoringCase(string $expectedString, string $message = ''): self
    {
        if (! is_string($this->actual)) {
            throw new InvalidActualValue;
        }
        Assert::assertFileExists($this->actual, $message);

        $constraint = new LogicalNot(new IsEqualIgnoringCase($expectedString));
        Assert::assertThat(file_get_contents($this->actual), $constraint, $message);

        return $this;
    }

    /**
     * Asserts that $number matches value's Length.
     */
    public function hasLength(int $number): self
    {
        if (\is_string($this->actual)) {
            Assert::assertEquals($number, mb_strlen($this->actual));

            return $this;
        }

        if (is_iterable($this->actual)) {
            $this->count($number);

            return $this;
        }

        if (\is_object($this->actual)) {
            if (method_exists($this->actual, 'toArray')) {
                $array = $this->actual->toArray();
            } else {
                $array = (array) $this->actual;
            }

            Assert::assertCount($number, $array);

            return $this;
        }

        throw new \BadMethodCallException('Expectation value length is not countable.');
    }

    /**
     * Asserts that $number not matches value's Length.
     */
    public function notHasLength(int $number): self
    {
        if (\is_string($this->actual)) {
            Assert::assertNotEquals($number, mb_strlen($this->actual));

            return $this;
        }

        if (is_iterable($this->actual)) {
            $this->notCount($number);

            return $this;
        }

        if (\is_object($this->actual)) {
            if (method_exists($this->actual, 'toArray')) {
                $array = $this->actual->toArray();
            } else {
                $array = (array) $this->actual;
            }

            Assert::assertNotCount($number, $array);

            return $this;
        }

        throw new \BadMethodCallException('Expectation value length is not countable.');
    }
}
