<?php

namespace Rubicon\ObjectReveal;

class ObjectRevealTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \Rubicon\ObjectReveal\InvalidArgumentException
     * @dataProvider constructorTestDataProvider
     */
    public function testConstructorOnlyAcceptObject($param)
    {
        new ObjectReveal($param);
    }

    public function constructorTestDataProvider()
    {
        return [
            ['string'],
            [['array']],
            [10.0],
            [10],
            [tmpfile()],
            [true],
            [null],
            [function(){}],
        ];
    }

    /**
     * @requires PHP 5.5
     */
    public function testConstructorWithGenerator()
    {
        $reveal = new ObjectReveal(call_user_func(function() {
            $data = yield;
            if ($data) {
                yield $data;
            }
        }));
        $this->assertSame('data', $reveal->send('data'));
    }

    public function testReadProperty()
    {
        $revealed = new ObjectReveal(new ObjectRevealStub());
        $this->assertSame('private', $revealed->propPrivate);
        $this->assertSame('protected', $revealed->propProtected);
        $this->assertSame('public', $revealed->propPublic);

        $this->setExpectedException('Rubicon\ObjectReveal\UndefinedPropertyException');
        $revealed->propUnknown;
    }
    public function testReadPropertyWithMagicGet()
    {
        $revealed = new ObjectReveal(new ObjectRevealMagicStub());
        $this->assertSame('propUnknown', $revealed->propUnknown);
    }

    public function testWriteProperty()
    {
        $object   = new ObjectRevealStub();
        $revealed = new ObjectReveal($object);

        $revealed->propPrivate = 'write.private';
        $this->assertAttributeEquals('write.private', 'propPrivate', $object);

        $revealed->propProtected = 'write.protected';
        $this->assertAttributeEquals('write.protected', 'propProtected', $object);

        $revealed->propPublic = 'write.public';
        $this->assertAttributeEquals('write.public', 'propPublic', $object);
    }

    public function testCallMethod()
    {
        $revealed = new ObjectReveal(new ObjectRevealStub());
        $this->assertSame('private', $revealed->getPrivate());
        $this->assertSame('protected', $revealed->getProtected());
        $this->assertSame('public', $revealed->getPublic());

        $this->setExpectedException('Rubicon\ObjectReveal\UndefinedMethodException');
        $revealed->getUnknown();
    }

    public function testCloneCopyUnderlyingObject()
    {
        $object   = new ObjectRevealStub();
        $revealed = new ObjectReveal($object);
        $clone    = clone $revealed;
        $clone->newProp = 'new';
        $this->assertObjectNotHasAttribute('newProp', $object);
    }
}

class ObjectRevealStub
{
    private $propPrivate = 'private';
    protected $propProtected = 'protected';
    public $propPublic = 'public';

    private function getPrivate()
    {
        return $this->propPrivate;
    }
    protected function getProtected()
    {
        return $this->propProtected;
    }
    public function getPublic()
    {
        return $this->propPublic;
    }
}
class ObjectRevealMagicStub
{
    public function __get($prop)
    {
        return $prop;
    }
}