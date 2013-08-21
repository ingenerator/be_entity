<?php
/**
 * Specifications for the Factory manager, which creates instances of the required Factory type
 *
 * @author     Andrew Coulton <andrew@ingenerator.com>
 * @copyright  2013 inGenerator Ltd
 * @licence    BSD
 */

namespace spec\Ingenerator\BeEntity;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

// Use the spec fixture implementation of the entity factory and entity class
require_once(__DIR__.'/../../support/Ingenerator/BeEntity/Factory/Example.php');

/**
 * Specifications for the Factory manager, which handles creating Factory instances for a given type string - this is
 * handled as a separate class to allow injection of stubs and mocks into classes that use entity factories.
 *
 * @package spec\Ingenerator\BeEntityFactory
 * @see \Ingenerator\BeEntity\FactoryManager
 * @see \Ingenerator\BeEntity\Factory\Example
 */
class FactoryManagerSpec extends ObjectBehavior
{
	/**
	 * An entity factory needs a reference to the entity manager, so it is passed in through the factory
	 *
	 * @param \Doctrine\ORM\EntityManager $entity_manager
	 * @return void
	 */
	public function let($entity_manager)
	{
		$this->beConstructedWith($entity_manager);
	}

	/**
	 * It should be possible to create an instance
	 *
	 * @return void
	 */
	public function it_is_initializable()
    {
        $this->shouldHaveType('Ingenerator\BeEntity\FactoryManager');
    }

	/**
	 * The factory manager maps the 'type' to a factory class and creates an instance if the class exists
	 *
	 * @return void
	 */
	public function it_creates_factory_for_known_types()
	{
		$this->create_factory('Example')->shouldReturnAnInstanceOf('\Ingenerator\BeEntity\Factory\Example');
	}

	/**
	 * In rare cases where a consistent factory class is required by multiple steps a feature context should expose this
	 * in its own implementation - the factory manager should always send back a new factory.
	 *
	 * @return void
	 */
	public function it_creates_a_new_factory_for_each_call()
	{
		$first = $this->create_factory('Example');
		$second = $this->create_factory('Example');

		$second->shouldBeAnInstanceOf('\Ingenerator\BeEntity\Factory\Example');
		$second->shouldNotBe($first);
	}

	/**
	 * If no factory for this type has been defined then a catchable exception should be thrown during create_factory
	 *
	 * @return void
	 */
	public function it_throws_missing_factory_exception_for_unknown_types()
	{
		$this->shouldThrow('\Ingenerator\BeEntity\Exception\MissingFactoryException')
			->during('create_factory', array('UnknownFoo'));
	}

	/**
	 * An entity factory needs a reference to the entity manager, so it is passed in through the factory
	 *
	 * @param \Doctrine\ORM\EntityManager $entity_manager
	 *
	 * @return void
	 */
	public function it_injects_the_provided_entity_manager($entity_manager)
	{
		// Uses a spec-specific accessor in the Example factory
		$factory = $this->create_factory('Example');
		$factory->get_entity_manager()->shouldReturn($entity_manager);
	}

}
