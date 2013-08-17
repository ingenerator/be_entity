Be_Entity
=========

Improve clarity of your Behat suites by easily specifying Doctrine2 entities within your feature files instead of in
separate fixtures.

[![Build Status](https://secure.travis-ci.org/ingenerator/be_entity.png?branch=master)](http://travis-ci.org/ingenerator/be_entity)

Usage Example
-------------

```gherkin
Given a User entity "ingenerator@users.noreply.github.com" with password "12345678"
And I go to "/login"
And I fill in "email" with "ingenerator@users.noreply.github.com"
And I fill in "password" with "12345678"
When I press "Login"
Then I should be on "/"
And I should be logged in

Given no User entity "nobody.here@no.domain"
And I go to "/login"
And I fill in "email" with "ingenerator@users.noreply.github.com"
And I fill in "password" with "12345678"
When I press "Login"
Then I should be on "/login"
And I should see "Invalid user or password"
```

```php
namespace Ingenerator\BeEntity\Factory;

class User extends Ingenerator\BeEntity\Factory {

	protected function _locate($identifier)
	{
		return $this->entity_manager->getRepository('Project\User')->findOneBy(array('email' => $identifier));
	}

	protected function _create($identifier)
	{
		$user = new Project\User;
		$user->set_email($identifier);
		$user->set_password('foo');
		return $user;
	}

}
```

Installation
------------

Add to your composer.json:

```json
{
	"require": {
		"ingenerator/be_entity" : "dev-master"
	}
}
```

Create the BeEntity context as a subcontext of your FeatureContext:

```php

class FeatureContext extends Behat\Behat\Context\BehatContext {

	public function __construct()
	{
		$em = new Doctrine\ORM\EntityManager;  // Whatever code you need to get an EntityManager instance in your app

		$this->useContext('be_entity', new Ingenerator\BeEntity\Context\BeEntityContext($em));
	}
}
```

Create factory classes for each type of entity you want to use in your features - see the example User factory above.
Your factory defines how to use the step argument to search for an entity of that type and declares sensible defaults
for a new entity record.

Note that you can have multiple factory classes for each entity type in your application - for example, you might create
an Administrator factory that creates Project\User entities with a particular field set or role granted.

Roadmap
-------

* Generators for sequences of entities - eg user1, user2, user3
* Entity references - eg `Given the Blog entity "My first blog" has author "User: ingenerator@users.noreply.github.com"`
* Wipe db/wipe table hooks for specific scenarios that require an empty database or integration builds
* Review Entity Manager injection and consider a service container layer

Contributors and Credits
------------------------

* Andrew Coulton [acoulton](http://github.com/acoulton) [lead developer]

Heavily inspired by [thoughtbot's factory_girl ruby gem](https://github.com/thoughtbot/factory_girl).

Licence
-------
[BSD Licence - see LICENSE](LICENSE)