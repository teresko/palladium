<?php

namespace Palladium\Component;

use PHPUnit\Framework\TestCase;

use Mock\Accopunt;

/**
 * @covers Palladium\Component\DataMapper
 */
final class DataMapperTest extends TestCase
{
    public function test_Population_of_Entity()
    {

        $entity = $this
                    ->getMockBuilder(Account::class)
                    ->setMethods(['setId', 'setAlpha', 'setBetaGamma'])
                    ->disableOriginalConstructor()
                    ->getMock();
        $entity->expects($this->once())->method('setAlpha')->with($this->equalTo(12));
        $entity->expects($this->once())->method('setBetaGamma')->with($this->equalTo('test'));

        $instance = $this->getMockForAbstractClass(DataMapper::class);

        $instance->applyValues($entity, [
            'alpha' => 12,
            'beta_gamma' => 'test',
        ]);
    }
}
