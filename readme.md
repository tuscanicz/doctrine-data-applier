# Doctrine data applier

Symfony bundle for Doctrine Migrations of data using doctrine entities.
Use this tool to describe your database data by doctrine entities.
It requires to modify your entities a bit but for that price,
you'll get a mighty tool that will auto-merge entities in your application
with those in your database.

This is very useful when you need to keep your data up-to-date
 in many environments and are necessary for your application to run.
 
Typical use-case is a user table with your administrator, content manager
 and other "obligatory" users or enumerations that are used across your app.

DataApplier will **never affect any data that was not created by DataApplier**
so your user data can live next to data applier data together in one table.

## How to use

Add composer dependency: ``composer require tuscanicz/doctrine-data-applier:dev-develop``

### Modify your Entity to be useful with Data Applier

You need to add some columns to doctrine entity to make them managable by data applier.

In order to do so you'll have to implement ``DataApplier\Entity\DataApplicableEntityInterface``
 that will force you to use ``DataApplier\Entity\DataApplicableEntityTrait``
 and present ``id`` column with setter and getter.
 
This id must be your primary key in database, see a typical annotation:

```php
/**
 * @var int
 * @ORM\Column(type="integer")
 * @ORM\Id
 * @ORM\GeneratedValue()
 */
private $id;

public function getId()
{
    return $this->id;
}

public function setId(int $id)
{
    $this->id = $id;
}
```

I expect that most of the doctrine entities have such column already defined.
If you define your id with different name or consists of complex keys,
 you'll have to fork this repository and fix this limitation :)

Next, you'll have to decide what are the DataApplier identifier columns.

This tool will decide whether to delete the row, update the data or insert new
 by matching your database contents with entities in your application.

Annotate them with ``DataApplier\Annotation\DataApplierIdentifier``:

```php
/**
 * @var string
 * @ORM\Column(type="string")
 * @DataApplierIdentifier()
 */
private $key;
```

Then create a factory method /or factory class
that will set all the necessary attributes to your entity.
Don't include the primary key in database (``id``),
 these will differ on your environments.

Example:
```php
public static function createNew($value, $key)
{
    $self = new self;
    $self->setKey($key);
    $self->setValue($value);

    return $self;
}
```

### Update the database

Use ``doctrine:migrations`` to change the entities.
Generating a diff will add a few columns that will help DataApplier to identify a source of data.

### Create a DataApplier for your entity

Data applier consists of multiple DataAppliers
 that will implement ``DataApplier\Data\DataApplierInterface``.
 
The only method ``applyData()`` will return
 an array of entities that you need to keep in your database:
 
```php
class TestDataApplier1 implements DataApplierInterface
{
    public function applyData()
    {
        return [
            TestEntity::createNew('value1', 'key1'),
            TestEntity::createNew('value2', 'key2'),
            TestEntity::createNew('value3', 'key3'),
        ];
    }
}

```

You must register ``TestDataApplier1`` as Symfony service
 and add ``doctrine.data_applier`` tag:
 
```xml
<service id="your_app.data.test_data_applier1" class="YourApp\Data\TestDataApplier1">
    <tag name="doctrine.data_applier"/>
</service>
```

### Register DataApplier bundle

```php
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = [
            ...,
            new \DataApplier\DataApplierBundle()
        ];
    }
}
```

### Run DataApplier

If you managed to go thru all the previous steps - congratulations. :)

You can now run your data applier via Symfony console:
``php bin/console data:apply``

This will update your data in your database.