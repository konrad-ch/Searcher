<?php
namespace Test\Searcher;

use \Searcher\Searcher;
use \Searcher\Searcher\Factories\ExceptionFactory;

use \Phalcon\DI as Di;

/**
 * Class SearcherTest
 *
 * @package Test\Searcher
 * @since   PHP >=5.5.12
 * @version 1.0
 * @author  Stanislav WEB | Lugansk <stanisov@gmail.com>
 *
 */
class SearcherTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Searcher class object
     *
     * @var Searcher
     */
    private $searcher;

    /**
     * ReflectionClass
     *
     * @var \ReflectionClass
     */
    private $reflection;

    /**
     * Dependency Injection container
     *
     * @var \Phalcon\DI
     */
    private $di;

    /**
     * Initialize testing object
     *
     * @uses Searcher
     * @uses \ReflectionClass
     */
    public function setUp()
    {
        $this->searcher = new Searcher();
        $this->reflection = new \ReflectionClass('Searcher\Searcher');
    }

    /**
     * Kill testing object
     *
     * @uses Searcher
     */
    public function tearDown()
    {
        $this->searcher = null;
    }

    /**
     * Call protected/private method of a class.
     *
     * @param object &$object    Instantiated object that we will run method on.
     * @param string $methodName Method name to call
     * @param array  $parameters Array of parameters to pass into method.
     * @example <code>
     *                           $this->invokeMethod($user, 'cryptPassword', array('passwordToCrypt'));
     *                           </code>
     * @return mixed Method return.
     */
    protected function invokeMethod(&$object, $methodName, array $parameters = array())
    {
        $method = $this->reflection->getMethod($methodName);
        $method->setAccessible(true);
        return $method->invokeArgs($object, $parameters);
    }

    /**
     * Setup accessible any private (protected) property
     *
     * @param $name
     * @return \ReflectionMethod
     */
    protected function getProperty($name)
    {
        $prop = $this->reflection->getProperty($name);
        $prop->setAccessible(true);
        return $prop;
    }

    public function testProperties()
    {
        // check Searcher's properties
        foreach (['query', 'exact', 'validator'] as $prop)
            $this->assertClassHasAttribute($prop, $this->reflection->getName(),
                "[-] The `$prop` property must be in " . $this->reflection->getName()
            );

        //check default properties

        $this->assertAttributeEquals(array(), 'query', $this->searcher,
            "[-] The `query` property must have array() as default in " . $this->reflection->getName());
        $this->assertAttributeEquals(false, 'exact', $this->searcher,
            "[-] The `exact` property must have false as default in " . $this->reflection->getName());
        $this->assertAttributeEquals(new \Searcher\Validator(), 'validator', $this->searcher,
            "[-] The `_validator` property must don't have default value in " . $this->reflection->getName());
    }

    /**
     * @covers Searcher\Searcher::__construct()
     */
    public function testConstructor()
    {
        // Get mock, without the constructor being called
        $mock = $this->getMockBuilder($this->reflection->getName())
            ->disableOriginalConstructor()
            ->getMock();

        // set expectations for constructor calls
        $mock->expects($this->any())->method('validator')
            ->with(
                $this->equalTo(new \Searcher\Validator())
            );

        // now call the constructor
        $constructor = $this->reflection->getConstructor();
        $constructor->invoke($mock);
    }

    /**
     * @covers Searcher\Searcher::setExact
     */
    public function testExact()
    {
        // check method setExact
        $this->assertTrue(
            method_exists($this->searcher, 'setExact'),
            '[-] Class Searcher have method setExact()'
        );

        // calling method
        $this->searcher->setExact(true);

        // check passed param
        $this->assertContainsOnly('boolean', [$this->searcher->exact],
            "[-] The `setExact` will passed boolean param in " . $this->reflection->getName()
        );

        // check equal returning objects
        $this->assertSame($this->searcher->setExact(true), $this->searcher,
            "[-] The `setExact` will return self and passed boolean param in " . $this->reflection->getName());
    }

    /**
     * @covers Searcher\Searcher::setMin
     * @covers Searcher\Searcher::setMax
     */
    public function testLimits()
    {
        // check method setMin
        $this->assertTrue(
            method_exists($this->searcher, 'setMin'),
            '[-] Class Searcher must have method setMin()'
        );

        // check method exists setMax
        $this->assertTrue(
            method_exists($this->searcher, 'setMax'),
            '[-] Class Validator must have method setMax()'
        );

        // check return of setMin
        $this->assertSame($this->searcher, $this->invokeMethod($this->searcher, 'setMin', [3]),
            "[-] setMin method should return object Searcher"
        );

        // check return of setMax
        $this->assertSame($this->searcher, $this->invokeMethod($this->searcher, 'setMax', [128]),
            "[-] setMax method should return object Searcher"
        );
    }

    /**
     * @covers Searcher\Searcher::setQuery
     */
    public function testQuery()
    {
        // check method setMin
        $this->assertTrue(
            method_exists($this->searcher, 'setQuery'),
            '[-] Class Searcher must have method setQuery()'
        );

        $this->searcher->exact = true;
        // check return of setQuery
        $this->invokeMethod($this->searcher, 'setQuery', ['test']);

        $this->assertInternalType('array', $this->searcher->query,
           "[-] setQuery method should return array"
        );

        $this->assertCount(1, $this->searcher->query,
            "[-] setQuery method should return only 1 element of array [query]"
        );

        $this->assertArrayHasKey('query', $this->searcher->query,
            "[-] setQuery method should return array with key [query]"
        );

        $this->assertContains('test', $this->searcher->query['query'],
            "[-] setQuery method should return only assigned to the query property value"
        );

        $this->searcher->exact = false;
        // check return of setQuery
        $this->invokeMethod($this->searcher, 'setQuery', ['test']);

        $this->assertInternalType('array', $this->searcher->query,
            "[-] setQuery method should return array"
        );

        $this->assertCount(1, $this->searcher->query,
            "[-] setQuery method should return only 1 element of array [query]"
        );

        $this->assertArrayHasKey('query', $this->searcher->query,
            "[-] setQuery method should return array with key [query]"
        );
    }

    /**
     * @covers Searcher\Searcher::run
     */
    public function testRun()
    {
        // check modifier before run
        $modifiers = (new \ReflectionMethod('\Searcher\Searcher', 'run'))->getModifiers();
        $this->assertEquals(['final', 'public'], \Reflection::getModifierNames($modifiers),
            "[-] run method must be as final public"
        );

        try {
            //@TODO
            //$run = $this->searcher->run();

        }
        catch(ExceptionFactory $e) {

            // Expected exception caught! Woohoo! Ignore it

        }
    }

    /**
     * @covers Searcher\Searcher::setThreshold
     */
    public function testThreshold()
    {
        $intThreshold = $this->searcher->setThreshold(123);
        // check instance method
        $this->assertInstanceOf($this->reflection->getName(), $intThreshold,
            "[-] setThreshold method must be as instance of Searcher\Searcher"
        );

        $arrayThreshold = $this->searcher->setThreshold([0, 100]);
        // check instance method
        $this->assertInstanceOf($this->reflection->getName(), $arrayThreshold,
            "[-] setThreshold method must be as instance of Searcher\Searcher"
        );
    }

    /**
     * @covers Searcher\Searcher::getFields
     */
    public function testGetFields()
    {
        // check method getFields
        $this->assertTrue(
            method_exists($this->searcher, 'getFields'),
            '[-] Class Searcher must have method getFields()'
        );

        $fields = $this->searcher->getFields();

        // check variable type instance
        $this->assertContainsOnly('array', [$fields],
            "[-] The `getFields` will return array from Validator"
        );
        $this->assertEmpty($fields,
            "[-] The `getFields` will return an empty array while init"
        );
    }

    /**
     * @covers Searcher\Searcher::setOrder
     */
    public function testOrder()
    {
        // check method setOrder
        $this->assertTrue(
            method_exists($this->searcher, 'setOrder'),
            '[-] Class Searcher must have method setOrder()'
        );

        try {

            $setOrder = $this->searcher->setOrder(['Model' => ['id' => 'DESC']]);

            // check return of setOrder
            $this->assertSame($this->searcher, $setOrder,
                "[-] setOrder method should return object Searcher"
            );
        }
        catch(ExceptionFactory $e) {

            // Expected exception caught! Woohoo! Ignore it
            //echo $e->getMessage();
        }
    }

    /**
     * @covers Searcher\Searcher::setGroup
     */
    public function testGroup()
    {
        // check method setGroup
        $this->assertTrue(
            method_exists($this->searcher, 'setGroup'),
            '[-] Class Searcher must have method setGroup()'
        );

        try {

            $setGroup = $this->searcher->setGroup(['Model' => ['id']]);

            // check return of setGroup
            $this->assertSame($this->searcher, $setGroup,
                "[-] setGroup method should return object Searcher"
            );
        }
        catch(ExceptionFactory $e) {

            // Expected exception caught! Woohoo! Ignore it
            //echo $e->getMessage();
        }
    }

    /**
     * @covers Searcher\Searcher::setFields
     */
    public function testFields()
    {
        // check method setFields
        $this->assertTrue(
            method_exists($this->searcher, 'setFields'),
            '[-] Class Searcher must have method setFields()'
        );

        try {

            $setFields = $this->searcher->setFields(['Model' => ['id']]);

            // check return of setFields
            $this->assertSame($this->searcher, $setFields,
                "[-] setFields method should return object Searcher"
            );
        }
        catch(ExceptionFactory $e) {

            // Expected exception caught! Woohoo! Ignore it
            //echo $e->getMessage();
        }
    }
}


 