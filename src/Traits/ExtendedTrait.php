<?php

namespace Realodix\NextProject\Traits;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Constraint\IsEqual;
use PHPUnit\Framework\Constraint\IsEqualIgnoringCase;
use PHPUnit\Framework\Constraint\LogicalNot;
use PHPUnit\Framework\Constraint\StringContains;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Runner\Version;
use Realodix\NextProject\Support\Dom;
use Realodix\NextProject\Support\NullClosure;
use Realodix\NextProject\Support\Str;
use Realodix\NextProject\Support\Validator;

trait ExtendedTrait
{
    /**
     * Asserts that the value array has the provided $keys.
     *
     * @param array<int, int|string> $keys
     * @param string                 $message
     */
    public function arrayHasKeys(array $keys, string $message = ''): self
    {
        foreach ($keys as $key) {
            $this->arrayHasKey($key, null, $message);
        }

        return $this;
    }

    /**
     * Asserts that the value array not has the provided $keys.
     *
     * @param array<int, int|string> $keys
     * @param string                 $message
     */
    public function arrayNotHasKeys(array $keys, string $message = ''): self
    {
        foreach ($keys as $key) {
            $this->arrayNotHasKey($key, null, $message);
        }

        return $this;
    }

    /**
     * Asserts that the value contains the property $name.
     *
     * @param string $name
     * @param mixed  $value
     */
    public function hasProperty(string $name, $value = null): self
    {
        $this->isObject();

        Assert::assertTrue(property_exists($this->actual, $name));

        if (\func_num_args() > 1) {
            Assert::assertEquals($value, $this->actual->{$name});
        }

        return $this;
    }

    /**
     * Asserts that the value contains the provided properties $names.
     *
     * @param iterable<array-key, string> $names
     */
    public function hasProperties(iterable $names): self
    {
        foreach ($names as $name) {
            $this->hasProperty($name);
        }

        return $this;
    }

    /**
     * Asserts string contains string (ignoring line endings).
     *
     * Reference:
     * - https://github.com/sebastianbergmann/phpunit/pull/4670
     *
     * @param string $needle
     * @param string $message
     */
    public function stringContainsStringIgnoringLineEndings(string $needle, string $message = ''): self
    {
        $actual = Validator::actualValue($this->actual, 'string');
        $needle = Str::normalizeLineEndings($needle);
        $haystack = Str::normalizeLineEndings($actual);

        Assert::assertThat($haystack, new StringContains($needle, false), $message);

        return $this;
    }

    public function stringEquals(string $expected, string $message = ''): self
    {
        $actual = $this->actual(Validator::actualValue($this->actual, 'string'));

        if (Validator::isJson($this->actual)) {
            $actual->jsonStringEqualsJsonString($expected, $message);

            return $this;
        }

        if (Validator::isXml($this->actual)) {
            $actual->xmlStringEqualsXmlString($expected, $message);

            return $this;
        }

        $actual->equals($expected, $message);

        return $this;
    }

    public function stringNotEquals(string $expected, string $message = ''): self
    {
        $actual = $this->actual(Validator::actualValue($this->actual, 'string'));

        if (Validator::isJson($this->actual)) {
            $actual->jsonStringNotEqualsJsonString($expected, $message);

            return $this;
        }

        if (Validator::isXml($this->actual)) {
            $actual->xmlStringNotEqualsXmlString($expected, $message);

            return $this;
        }

        $actual->notEquals($expected, $message);

        return $this;
    }

    /**
     * Asserts that two strings equality (ignoring line endings).
     *
     * Reference:
     * - https://github.com/sebastianbergmann/phpunit/pull/4670
     *
     * @param string $expected
     * @param string $message
     */
    public function stringEqualIgnoringLineEndings(string $expected, string $message = ''): self
    {
        $actual = Str::normalizeLineEndings(
            Validator::actualValue($this->actual, 'string')
        );
        $expected = Str::normalizeLineEndings($expected);

        Assert::assertThat($actual, new IsEqual($expected), $message);

        return $this;
    }

    /**
     * Asserts that the contents of one file is equal to the string.
     *
     * Reference:
     * - https://github.com/sebastianbergmann/phpunit/pull/4649
     *
     * @param string $expectedString
     * @param string $message
     */
    public function fileEqualsString(string $expectedString, string $message = ''): self
    {
        $actual = Validator::actualValue($this->actual, 'string');
        $constraint = new IsEqual($expectedString);

        Assert::assertFileExists($actual, $message);
        Assert::assertThat(file_get_contents($actual), $constraint, $message);

        return $this;
    }

    public function fileNotEqualsString(string $expectedString, string $message = ''): self
    {
        $actual = Validator::actualValue($this->actual, 'string');
        $constraint = new LogicalNot(new IsEqual($expectedString));

        Assert::assertFileExists($actual, $message);
        Assert::assertThat(file_get_contents($actual), $constraint, $message);

        return $this;
    }

    /**
     * Asserts that the contents of one file is equal to the string (ignoring case).
     *
     * @param string $expectedString
     * @param string $message
     */
    public function fileEqualsStringIgnoringCase(string $expectedString, string $message = ''): self
    {
        $actual = Validator::actualValue($this->actual, 'string');
        Assert::assertFileExists($actual, $message);

        // @codeCoverageIgnoreStart
        if (version_compare(Version::series(), '9.0', '<')) {
            Assert::assertThat(
                file_get_contents($actual),
                new IsEqual($expectedString, 0.0, 10, false, true),
                $message
            );

            return $this;
        }
        // @codeCoverageIgnoreEnd

        $constraint = new IsEqualIgnoringCase($expectedString);
        Assert::assertThat(file_get_contents($actual), $constraint, $message);

        return $this;
    }

    public function fileNotEqualsStringIgnoringCase(string $expectedString, string $message = ''): self
    {
        $actual = Validator::actualValue($this->actual, 'string');
        Assert::assertFileExists($actual, $message);

        // @codeCoverageIgnoreStart
        if (version_compare(Version::series(), '9.0', '<')) {
            $constraint = new LogicalNot(new IsEqual($expectedString, 0.0, 10, false, true));

            Assert::assertThat(file_get_contents($actual), $constraint, $message);

            return $this;
        }
        // @codeCoverageIgnoreEnd

        $constraint = new LogicalNot(new IsEqualIgnoringCase($expectedString));
        Assert::assertThat(file_get_contents($actual), $constraint, $message);

        return $this;
    }

    /**
     * Asserts that $number matches value's Length.
     *
     * @param int $number
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
     *
     * @param int $number
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

    /**
     * Assert that the given string contains an element matching the given selector.
     *
     * @param string $selector A query $selector for the element to find.
     * @param string $message  A message to display if the assertion fails.
     */
    public function markupContainsSelector(string $selector, string $message = ''): self
    {
        $actual = Validator::actualValue($this->actual, 'string');
        $results = Dom::executeQuery($actual, $selector)->count();

        $this->actual($results)->greaterThan(0, $message);

        return $this;
    }

    /**
     * Assert that the given string does not contain an element matching the given selector.
     *
     * @param string $selector A query $selector for the element to find.
     * @param string $message  A message to display if the assertion fails.
     */
    public function markupNotContainsSelector(string $selector, string $message = ''): self
    {
        $actual = Validator::actualValue($this->actual, 'string');
        $results = Dom::executeQuery($actual, $selector)->count();

        $this->actual($results)->equals(0, $message);

        return $this;
    }

    /**
     * Assert an element's contents contain the given string.
     *
     * @param string $contents The string to look for within the DOM node's contents.
     * @param string $selector A query selector for the element to find.
     * @param string $message  A message to display if the assertion fails.
     */
    public function markupElementContains(string $contents, string $selector = '', string $message = ''): self
    {
        $actual = Validator::actualValue($this->actual, 'string');
        $matchedElements = Dom::getInnerHtmlOfMatchedElements($actual, $selector);

        $this->actual($matchedElements)->stringContainsString($contents, $message);

        return $this;
    }

    /**
     * Assert an element's contents do not contain the given string.
     *
     * @param string $contents The string to look for within the DOM node's contents.
     * @param string $selector A query $selector for the element to find.
     * @param string $message  A message to display if the assertion fails.
     */
    public function markupElementNotContains(string $contents, string $selector = '', string $message = ''): self
    {
        $actual = Validator::actualValue($this->actual, 'string');
        $matchedElements = Dom::getInnerHtmlOfMatchedElements($actual, $selector);

        $this->actual($matchedElements)->stringNotContainsString($contents, $message);

        return $this;
    }

    /**
     * Assert an element's contents contain the given regular expression pattern.
     *
     * @param string $regexp   The regular expression pattern to look for within the DOM node.
     * @param string $selector A query $selector for the element to find.
     * @param string $message  A message to display if the assertion fails.
     */
    public function markupElementRegExp(string $regexp, string $selector = '', string $message = ''): self
    {
        $actual = Validator::actualValue($this->actual, 'string');
        $matchedElements = Dom::getInnerHtmlOfMatchedElements($actual, $selector);

        $this->actual($matchedElements)->matchesRegularExpression($regexp, $message);

        return $this;
    }

    /**
     * Assert an element's contents do not contain the given regular expression pattern.
     *
     * @param string $regexp   The regular expression pattern to look for within the DOM node.
     * @param string $selector A query $selector for the element to find.
     * @param string $message  A message to display if the assertion fails.
     */
    public function markupElementNotRegExp(string $regexp, string $selector = '', string $message = ''): self
    {
        $actual = Validator::actualValue($this->actual, 'string');
        $matchedElements = Dom::getInnerHtmlOfMatchedElements($actual, $selector);

        $this->actual($matchedElements)->doesNotMatchRegularExpression($regexp, $message);

        return $this;
    }

    /**
     * Assert that an element with the given attributes exists in the given markup.
     *
     * @param array  $attributes An array of HTML attributes that should be found on the element.
     * @param string $message    A message to display if the assertion fails.
     */
    public function markupHasElementWithAttributes(array $attributes = [], string $message = ''): self
    {
        $actual = Validator::actualValue($this->actual, 'string');
        $attributes = '*'.Dom::flattenAttributeArray($attributes);

        $this->actual($actual)->markupContainsSelector($attributes, $message);

        return $this;
    }

    /**
     * Assert that an element with the given attributes does not exist in the given markup.
     *
     * @param array  $attributes An array of HTML attributes that should be found on the element.
     * @param string $message    A message to display if the assertion fails.
     */
    public function markupNotHasElementWithAttributes(array $attributes = [], string $message = ''): self
    {
        $actual = Validator::actualValue($this->actual, 'string');
        $attributes = '*'.Dom::flattenAttributeArray($attributes);

        $this->actual($actual)->markupNotContainsSelector($attributes, $message);

        return $this;
    }

    /**
     * Assert the number of times an element matching the given selector is found.
     *
     * @param int    $count    The number of matching elements expected.
     * @param string $selector A query selector for the element to find.
     * @param string $message  A message to display if the assertion fails.
     */
    public function markupSelectorCount(int $count, string $selector, string $message = ''): self
    {
        $actual = Validator::actualValue($this->actual, 'string');
        $results = Dom::executeQuery($actual, $selector);

        $this->actual($results)->count($count, $message);

        return $this;
    }

    /**
     * Asserts that executing value throws an exception.
     *
     * @param string|Closure $exception        string: the exception class
     *                                         Closure: first parameter = exception class
     * @param null|string    $exceptionMessage
     */
    public function throw($exception, string $exceptionMessage = null): self
    {
        $callback = NullClosure::create();

        if ($exception instanceof \Closure) {
            $callback = $exception;
            $parameters = (new \ReflectionFunction($exception))->getParameters();

            if (1 !== \count($parameters)) {
                throw new \LogicException('The "throw" closure must have a single parameter type-hinted as the class string');
            }

            if (! ($type = $parameters[0]->getType()) instanceof \ReflectionNamedType) {
                throw new \LogicException('The "throw" closure\'s parameter must be type-hinted as the class string');
            }

            $exception = $type->getName();
        }

        try {
            ($this->actual)();
        } catch (\Throwable $e) {
            if (! class_exists($exception)) {
                Assert::assertStringContainsString($exception, $e->getMessage());

                return $this;
            }

            if ($exceptionMessage) {
                Assert::assertStringContainsString($exceptionMessage, $e->getMessage());
            }

            Assert::assertInstanceOf($exception, $e);
            $callback($e);

            return $this;
        }

        if (! class_exists($exception)) {
            throw new ExpectationFailedException("Exception with message \"{$exception}\" not thrown.");
        }

        throw new ExpectationFailedException("Exception \"{$exception}\" not thrown.");
    }
}
