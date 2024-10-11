# API Security: Access Control

Authentication & Authorization library for Nette.

- user login
- verifying user privileges
- how to create custom authenticators and authorizators
- Access Control List

It requires PHP version 8.1 and supports PHP up to 8.4.

## Setup

DiAttibute is available on composer:

```bash
composer require qa-data/api-security
```

At first register compiler extension.

```neon
extensions:
	apiSecurity: QaData\ApiSecurity\DI\ApiSecurityExtension
```

Authentication
==============

Authentication means **user login**, ie. the process during which a user's identity is verified. The user usually
identifies himself using token. Verification is performed by the so-called [authenticator](#Authenticator). If the login
fails, it throws `QaData\ApiSecurity\AuthenticationException`.

```php
try {
	$user->login($token);
} catch (QaData\ApiSecurity\AuthenticationException $e) {
	// ... login failed
}
```

And checking if user is logged in:

```php
echo $user->isLoggedIn() ? 'yes' : 'no';
```

Authenticator
-------------

It is an object that verifies the login data, ie usually the token.

```php
$authenticator = new QaData\ApiSecurity\SimpleAuthenticator([
	# name => password
	1 => 'admin_token',
]);
```

An authenticator is an object that implements the Authenticator interface with method `authenticate()`. Its task is
either to return the so-called [identity](#Identity) or to throw an exception
`QaData\ApiSecurity\AuthenticationException`.

```php


class MyAuthenticator implements QaData\ApiSecurity\Authenticator
{
	private $database;
	private $passwords;

	public function __construct(Nette\Database\Context $database)
	{
		$this->database = $database;
		$this->passwords = $passwords;
	}

	public function authenticate(string $token): QaData\ApiSecurity\Identity
	{
		$row = $this->database->table('tokens')
			->where('token', $token)
			->fetch();

		if (!$row) {
			throw new QaData\ApiSecurity\AuthenticationException('Token not found.');
		}

		return new QaData\ApiSecurity\SimpleIdentity(
			$row->id,
			$row->token,
			$row->roles,
			[
				'name' => $row->username,
			]
		);
	}
}
```

$onLoggedIn events
--------------------------------

Object `QaData\ApiSecurity\User` has event `$onLoggedIn`, so you can add callbacks that are triggered after a successful
login or after the user logs out.

```php
$user->onLoggedIn[] = function () {
	// user has just logged in
};
```

Identity
========

An identity is a set of information about a user that is returned by the authenticator and retrieved using
`$user->getIdentity()`. So we can get the id, token and roles.:

```php
$user->getIdentity()->getId();
$user->getIdentity()->getRoles();
```

Identity is an object that implements the `QaData\ApiSecurity\Identity` interface, the default implementation is
`QaData\ApiSecurity\SimpleIdentity`.



Authorization
=============

Authorization determines whether a user has sufficient privileges, for example, to access a specific resource or to
perform an action. Authorization assumes previous successful authentication, ie that the user is logged in.

For very simple websites with administration, where user rights are not distinguished, it is possible to use the already
known method as an authorization criterion `isLoggedIn()`. In other words: once a user is logged in, he has permissions
to all actions and vice versa.

```php
if ($user->isLoggedIn()) { // is user logged in?
	deleteItem(); // if so, he may delete an item
}
```

Roles
-----

The purpose of roles is to offer a more precise permission management and remain independent on the token.

```php
if ($user->isInRole('admin')) { // is the admin role assigned to the user?
	deleteItem(); // if so, he may delete an item
}
```


Authorizator
------------

In addition to roles, we will introduce the terms resource and operation:

- **role** is a user attribute - for example moderator, editor, visitor, registered user, administrator, ...
- **resource** is a logical unit of the application - article, page, user, menu item, poll, presenter, ...
- **operation** is a specific activity, which user may or may not do with *resource* - view, edit, delete, vote, ...

An authorizer is an object that decides whether a given *role* has permission to perform a certain *operation* with
specific *resource*. It is an object implementing the QaData\ApiSecurity\Authorizator interface with only one
method `isAllowed()`:

```php
class MyAuthorizator implements QaData\ApiSecurity\Authorizator
{
	public function isAllowed($role, $resource, $operation): bool
	{
		if ($role === 'admin') {
			return true;
		}
		if ($role === 'user' && $resource === 'article') {
			return true;
		}

		...

		return false;
	}
}
```

And the following is an example of use. Note that this time we call the method `QaData\ApiSecurity\User::isAllowed()`, not
the authorizator's one, so there is not first parameter `$role`. This method calls `MyAuthorizator::isAllowed()`
sequentially for all user roles and returns true if at least one of them has permission.

```php
if ($user->isAllowed('file')) { // is user allowed to do everything with resource 'file'?
	useFile();
}

if ($user->isAllowed('file', 'delete')) { // is user allowed to delete a resource 'file'?
	deleteFile();
}
```

Both arguments are optional and their default value means *everything*.



Permission ACL
--------------

Nette comes with a built-in implementation of the authorizer, the `QaData\ApiSecurity\Acl` class, which offers a
lightweight and flexible ACL (Access Control List) layer for permission and access control. When we work with this
class, we define roles, resources, and individual permissions. And roles and resources may form hierarchies. To explain,
we will show an example of a web application:

- `guest`: visitor that is not logged in, allowed to read and browse public part of the web, ie. read articles, comment
  and vote in polls
- `registered`: logged-in user, which may on top of that post comments
- `administrator`: can manage articles, comments and polls

So we have defined certain roles (`guest`, `registered` and `administrator`) and mentioned resources (`article`,
`comments`, `poll`), which the users may access or take actions on (`view`, `vote`, `add`, `edit`).

We create an instance of the Permission class and define **roles**. It is possible to use the inheritance of roles,
which ensures that, for example, a user with a role `administrator` can do what an ordinary website visitor can do (and
of course more).

```php
$acl = new QaData\ApiSecurity\Permission;

$acl->addRole('guest');
$acl->addRole('registered', 'guest'); // registered inherits from guest
$acl->addRole('administrator', 'registered'); // and administrator inherits from registered
```

We will now define a list of **resources** that users can access:

```php
$acl->addResource('article');
$acl->addResource('comment');
$acl->addResource('poll');
```

Resources can also use inheritance, for example, we can add `$acl->addResource('perex', 'article')`.

And now the most important thing. We will define between them **rules** determining who can do what:

```php
// everything is denied now

// let the guest view polls
$acl->allow('guest', 'poll', 'view');

// and also vote in polls
$acl->allow('guest', 'poll', 'vote');

// the registered inherits the permissions from guesta, we will also let him to comment
$acl->allow('registered', 'comment', 'add');

// the administrator can view and edit anything
$acl->allow('administrator', 'article');
$acl->allow('administrator', 'comment');
$acl->allow('administrator', 'pool');
```

What if we want to **prevent** someone from accessing a resource?

```php
// administrator cannot edit polls, that would be undemocractic.
$acl->deny('administrator', 'poll', 'edit');
```

Now when we have created the set of rules, we may simply ask the authorization queries:

```php
// can guest view articles?
$acl->isAllowed('guest', 'article', 'view'); // true

// can guest edit an article?
$acl->isAllowed('guest', 'article', 'edit'); // false

// can guest vote in polls?
$acl->isAllowed('guest', 'poll', 'vote'); // true

// may guest add comments?
$acl->isAllowed('guest', 'comment', 'add'); // false
```

The same applies to a registered user, but he can also comment:

```php
$acl->isAllowed('registered', 'article', 'view'); // true
$acl->isAllowed('registered', 'comment', 'add'); // true
$acl->isAllowed('registered', 'comment', 'edit'); // false
```

The administrator can edit everything except polls:

```php
$acl->isAllowed('administrator', 'poll', 'vote'); // true
$acl->isAllowed('administrator', 'poll', 'edit'); // false
$acl->isAllowed('administrator', 'comment', 'edit'); // true
```
