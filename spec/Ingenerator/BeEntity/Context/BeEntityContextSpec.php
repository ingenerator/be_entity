<?php
/**
 * Specifications for the entity context which defines relevant steps
 *
 * @author     Andrew Coulton <andrew@ingenerator.com>
 * @copyright  2013 inGenerator Ltd
 * @licence    BSD
 */

namespace spec\Ingenerator\BeEntity\Context;

use Behat\Gherkin\Node\TableNode;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * Specifications for the BeEntityContext, which defines relevant steps
 *
 * @package spec\Ingenerator\BeEntity\Context
 * @see \Ingenerator\BeEntity\Context\BeEntityContext
 */
class BeEntityContextSpec extends ObjectBehavior
{

	/**
	 * Users should create an Entity Manager within their main feature context in whatever way suits their application,
	 * passing it in to the BeEntity context on creation.
	 *
	 * @param \Doctrine\ORM\EntityManager $entity_manager the entity manager
	 *
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
        $this->shouldHaveType('Ingenerator\BeEntity\Context\BeEntityContext');
    }

	/**
	 * If not given a FactoryManager to use it should create one
	 *
	 * @return void
	 */
	public function it_creates_a_factory_manager_if_required()
	{
		$this->get_factory_manager()->shouldReturnAnInstanceOf('\Ingenerator\BeEntity\FactoryManager');
	}

	/**
	 * Users should be able to inject a FactoryManager for customisation or testing
	 *
	 * @param \Ingenerator\BeEntity\FactoryManager $factory_manager the factory manager stub to inject
	 *
	 * @return void
	 */
	public function it_can_accept_an_injected_factory_manager($factory_manager)
	{
		$this->set_factory_manager($factory_manager);
		$this->get_factory_manager()->shouldReturn($factory_manager);
	}

	/**
	 * The given_a_simple_entity step should provide a quick way to ensure a named entity exists with any values
	 *
	 * @param \Doctrine\ORM\EntityManager          $entity_manager the entity manager
	 * @param \Ingenerator\BeEntity\Factory        $factory        the entity factory mock
	 * @param \Ingenerator\BeEntity\FactoryManager $manager        the factory manager mock
	 *
	 * @return void
	 * @see \Ingenerator\BeEntity\Context\BeEntityContext::given_a_simple_entity
	 */
	public function it_can_provide_and_flush_a_simple_entity_with_default_fields($entity_manager, $factory, $manager)
	{
		$manager->create_factory('Dummy')->willReturn($factory);
		$this->set_factory_manager($manager);

		// The factory provide method takes care of lookup and creating if required
		$e = new \stdClass();
		$factory->provide('my-dummy', array())->willReturn($e)->shouldBeCalled();
		$entity_manager->flush()->shouldBeCalled();

		$this->given_a_simple_entity('Dummy', 'my-dummy')->shouldReturn($e);
	}

	/**
	 * The given_a_simple_entity_with step should provide a quick way to update an entity where only one field is relevant
	 *
	 * @param \Doctrine\ORM\EntityManager          $entity_manager the entity manager
	 * @param \Ingenerator\BeEntity\Factory        $factory        the entity factory mock
	 * @param \Ingenerator\BeEntity\FactoryManager $manager        the factory manager mock
	 *
	 * @return void
	 * @see \Ingenerator\BeEntity\Context\BeEntityContext::given_a_simple_entity_with
	 */
	public function it_can_provide_and_flush_a_simple_entity_with_one_relevant_field($entity_manager, $factory, $manager)
	{
		$manager->create_factory('Dummy')->willReturn($factory);
		$this->set_factory_manager($manager);

		// The factory provide method takes care of lookup and creating if required
		$e = new \stdClass();
		$factory->provide('my-dummy', array('set-field' => 'new-value'))->willReturn($e)->shouldBeCalled();
		$entity_manager->flush()->shouldBeCalled();

		$this->given_a_simple_entity_with('Dummy', 'my-dummy', 'set-field', 'new-value')->shouldReturn($e);
	}

	/**
	 * The given_entities step should provide a way to set multiple fields on multiple entities by providing a table
	 * of values where the first column is the identifier used by the entity factory.
	 *
	 * @param \Doctrine\ORM\EntityManager          $entity_manager the entity manager
	 * @param \Ingenerator\BeEntity\Factory        $factory        the entity factory mock
	 * @param \Ingenerator\BeEntity\FactoryManager $manager        the factory manager mock
	 *
	 * @return void
	 * @see \Ingenerator\BeEntity\Context\BeEntityContext::given_entities
	 */
	public function it_can_populate_a_set_of_entities_from_a_table($entity_manager, $factory, $manager)
	{
		$manager->create_factory('Dummy')->willReturn($factory);
		$this->set_factory_manager($manager);

		// Stub some entity data that would be specified in the table
		$data_1 = array('title' => 'This is title 1', 'active' => TRUE, 'custom' => 'other1');
		$data_2 = array('title' => 'This is title 2', 'active' => TRUE, 'custom' => 'other2');
		$data_3 = array('title' => 'This is title 3', 'active' => FALSE, 'custom' => 'other3');
		$table = new TableNode();
		$table->addRow(\array_keys($data_1));
		$table->addRow($data_1);
		$table->addRow($data_2);
		$table->addRow($data_3);

		// The factory should be asked to provide entities for each row in the table, with the first column as identifier
		$factory->provide('This is title 1', $data_1)->shouldBeCalled();
		$factory->provide('This is title 2', $data_2)->shouldBeCalled();
		$factory->provide('This is title 3', $data_3)->shouldBeCalled();

		// If not requested explicitly, the purge method should not be called
		$factory->purge()->shouldNotBeCalled();

		// After all done, the entity manager should be flushed
		$entity_manager->flush()->shouldBeCalled();

		$this->given_entities('', 'Dummy', $table);
	}

	/**
	 * The given_entities step should provide a way to wipe existing entity records to ensure the database is in an
	 * entirely clean state.
	 *
	 * @param \Doctrine\ORM\EntityManager          $entity_manager the entity manager
	 * @param \Ingenerator\BeEntity\Factory        $factory        the entity factory mock
	 * @param \Ingenerator\BeEntity\FactoryManager $manager        the factory manager mock
	 * @param \Behat\Gherkin\Node\TableNode        $table          the data table mock
	 *
	 * @return void
	 * @see \Ingenerator\BeEntity\Context\BeEntityContext::given_entities
	 */
	public function it_can_purge_entities_before_populating_them_from_a_table_for_clean_state($entity_manager, $factory, $manager, $table)
	{
		$manager->create_factory('Dummy')->willReturn($factory);
		$this->set_factory_manager($manager);

		$table->getHash()->willReturn(array());

		$factory->purge()->shouldBeCalled();
		$entity_manager->flush()->shouldBeCalled();

		$this->given_entities('only ', 'Dummy', $table);
	}

	/**
	 * The given_no_entity step should verify that there is no entity with the given identifier.
	 *
	 * @param \Ingenerator\BeEntity\Factory        $factory the entity factory mock
	 * @param \Ingenerator\BeEntity\FactoryManager $manager the factory manager mock
	 * @param \Doctrine\ORM\EntityManager $entity_manager the entity manager
	 *
	 * @return void
	 * @see \Ingenerator\BeEntity\Context\BeEntityContext::given_no_entity
	 */
	public function it_can_verify_there_is_no_entity_with_a_given_title($factory, $manager, $entity_manager)
	{
		$manager->create_factory('Dummy')->willReturn($factory);
		$this->set_factory_manager($manager);

		// The entity manager should be cleared to ensure values are reloaded from DB
		$entity_manager->clear()->shouldBeCalled();

		// When an entity cannot be located then the method should do nothing
		$factory->locate('not-existing-dummy', FALSE)->willReturn(NULL);
		$this->given_no_entity('Dummy', 'not-existing-dummy');

		// If the entity exists then the step has failed and should throw exception
		$factory->locate('this dummy should not exist', FALSE)->willReturn(new \StdClass);
		$this->shouldThrow('\Ingenerator\BeEntity\Exception\UnexpectedEntityException')
			->during('given_no_entity', array('Dummy', 'this dummy should not exist'));
	}

	/**
	 * The assert_entities step should verify that entities exist
	 *
	 * @param \Ingenerator\BeEntity\Factory        $factory the entity factory mock
	 * @param \Ingenerator\BeEntity\FactoryManager $manager the factory manager mock
 	 * @param \Doctrine\ORM\EntityManager $entity_manager the entity manager
	 *
	 * @return void
	 * @see \Ingenerator\BeEntity\Context\BeEntityContext::assert_entities
	 */
	public function it_can_verify_that_expected_entities_exist($factory, $manager, $entity_manager)
	{
		$manager->create_factory('Dummy')->willReturn($factory);
		$this->set_factory_manager($manager);

		// The entity does not exist
		$factory->matches('not existing dummy', array('active' => 'y', 'foo' => 'bar'))->willReturn(NULL);

		// Stub some entity data that would be specified in the table
		$data_1 = array('title' => 'not existing dummy', 'active' => 'y', 'foo' => 'bar');
		$table = new TableNode();
		$table->addRow(\array_keys($data_1));
		$table->addRow($data_1);

		$entity_manager->clear()->shouldBeCalled();

		$this->shouldThrow('\Ingenerator\BeEntity\Exception\ExpectationException')
			->during('assert_entities', array('Dummy', $table));
	}

	/**
	 * The assert_entities step should verify that entities have expected values
	 *
	 * @param \Ingenerator\BeEntity\Factory        $factory the entity factory mock
	 * @param \Ingenerator\BeEntity\FactoryManager $manager the factory manager mock
  	 * @param \Doctrine\ORM\EntityManager $entity_manager the entity manager
	 *
	 * @return void
	 * @see \Ingenerator\BeEntity\Context\BeEntityContext::assert_entities
	 */
	public function it_can_verify_that_expected_entities_have_expected_values($factory, $manager, $entity_manager)
	{
		$manager->create_factory('Dummy')->willReturn($factory);
		$this->set_factory_manager($manager);

		// The entity exists but has invalid values
		$factory->matches('not existing dummy', array('active' => 'y', 'foo' => 'bar'))
			->willReturn(array('foo' => array('exp' => 'bar', 'got' => 'foo')));

		// Stub some entity data that would be specified in the table
		$data_1 = array('title' => 'not existing dummy', 'active' => 'y', 'foo' => 'bar');
		$table = new TableNode();
		$table->addRow(\array_keys($data_1));
		$table->addRow($data_1);

		// The entity manager should be cleared to ensure values are reloaded from DB
		$entity_manager->clear()->shouldBeCalled();

		$this->shouldThrow('\Ingenerator\BeEntity\Exception\ExpectationException')
			->during('assert_entities', array('Dummy', $table));
	}

}
