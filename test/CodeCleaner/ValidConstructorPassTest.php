<?php

/*
 * This file is part of Psy Shell.
 *
 * (c) 2012-2020 Justin Hileman
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Psy\Test\CodeCleaner;

use Psy\CodeCleaner\ValidConstructorPass;

class ValidConstructorPassTest extends CodeCleanerTestCase
{
    /**
     * @before
     */
    public function getReady()
    {
        $this->setPass(new ValidConstructorPass());
    }

    /**
     * @dataProvider invalidStatements
     */
    public function testProcessInvalidStatement($code)
    {
        $this->expectException(\Psy\Exception\FatalErrorException::class);
        $this->parseAndTraverse($code);

        $this->fail();
    }

    /**
     * @dataProvider invalidParserStatements
     */
    public function testProcessInvalidStatementCatchedByParser($code)
    {
        $this->expectException(\Psy\Exception\ParseErrorException::class);
        $this->parseAndTraverse($code);

        $this->fail();
    }

    public function invalidStatements()
    {
        $data = [
            ['class A { public static function A() {}}'],
            ['class A { public static function a() {}}'],
            ['class A { private static function A() {}}'],
            ['class A { private static function a() {}}'],
        ];

        if (\version_compare(\PHP_VERSION, '7.0', '>=')) {
            $data[] = ['class A { public function A(): ?array {}}'];
            $data[] = ['class A { public function a(): ?array {}}'];
        }

        return $data;
    }

    public function invalidParserStatements()
    {
        return [
            ['class A { public static function __construct() {}}'],
            ['class A { private static function __construct() {}}'],
            ['class A { private static function __construct() {} public function A() {}}'],
            ['namespace B; class A { private static function __construct() {}}'],
        ];
    }

    /**
     * @dataProvider validStatements
     */
    public function testProcessValidStatement($code)
    {
        $this->parseAndTraverse($code);
        $this->assertTrue(true);
    }

    public function validStatements()
    {
        $data = [
            ['class A { public static function A() {} public function __construct() {}}'],
            ['class A { private function __construct() {} public static function A() {}}'],
            ['namespace B; class A { private static function A() {}}'],
        ];

        if (\version_compare(\PHP_VERSION, '7.0', '>=')) {
            $data[] = ['class A { public static function A() {} public function __construct() {}}'];
            $data[] = ['class A { private function __construct() {} public static function A(): ?array {}}'];
            $data[] = ['namespace B; class A { private static function A(): ?array {}}'];
        }

        return $data;
    }
}
