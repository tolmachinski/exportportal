# <a name="introduction"></a> Introduction

DBAL (Database Abstraction Layer) is very important part of almost all of the application. It can have a strong influence over the project by enforcing many restrictions and demanding specific approach in the data handling.

But the problem is, that the DBAL shipped with the framework used in this project (TinyMVC) not only is poorly designed, but has many major flaws:

- can connect only to MySQL database server;
- can handle only one connection;
- suffers from the "God Object" antipattern - one instance is used to: handle connection, query execution and query building;
- can build only one query at time - ther is now way to prepare or reuse query later;
- query building tools are very limited;
- doesn't have transactions support;
- doesn't have migrations;
- doesn't have schema builder;
- doesn't have support of the new MySQL multibyte collations;
- uses PHP PDO but in a very limited way (for example, there is no way to indicate type of parameter or to use named parameters);
- has a very poor error handling;
- uses outdated pseudo-active-record-model based approach.

Needless to say that right now it can be considered a limited tool for interaction with the database, even if in parcourse of years it went through many changes. More than that, it directly contributes to the fact, that models (they are in fact repositories) became filled with queries designed to ***get around*** DABL limitations. More than that, the most common pattern in the models? especially the older ones, was to leverage code to the raw queries rather that using the PDO, like this:

```php
if(isset($keywords)){
    $order_by =  $order_by . ", REL DESC";
    $where[] = " MATCH (it.title, it.description, it.search_info) AGAINST (?)";
    $params[] = $keywords;
    $rel = " , MATCH (it.title, it.description, it.search_info) AGAINST ('" . $keywords . "') as REL";
}

$sql = "SELECT COUNT(itf.id_item) as count
        FROM item_featured itf
        LEFT JOIN items it ON itf.id_item = it.id
        LEFT JOIN item_category cat ON it.id_cat = cat.category_id
        LEFT JOIN port_country pco ON it.p_country = pco.id
        LEFT JOIN users u ON it.id_seller = u.idu ";

if(count($where))
    $sql .= " WHERE " . implode(" AND", $where);
$rez = $this->db->query_one($sql, $params);
return $rez['count'];
```

Such is code was directly contributed by fact, that for some reasons method `where()` requires `=` in every clause. Yes, this is ridiculous and can be used as a proof of unfinished nature of the DBAL intergrated in project's framework.

Another common pattern used in those models - a lot of repeated code used to indicate query conditions. Sometimes more than 50% of the model's content is consisted of repeated `if`'s.

As a way to resolve this problem without global changes in the project (for example, usage of another DBAL), is to add a low-level abstraction that can be used to create simplified, easier to understand models.

An example of such attempt can be considered classes `App_Model` and `Standalone_Model`;

# <a name="app-model"></a> App Model

This model was created as an attempt to make DB requests easier and to evade usage of repeated code.

It incorporates several feautures that can greatly enhance work with models inherited from that one.

## <a name="model-scopes"></a> Model scopes

As a way to reduce amount of code in the model, `App_Model` uses the `scopes`. The idea of scopes was adopted from **Laravel Eloquent** where they are creted to reuse query byuilder conditions. Of course, our adaptation is just a shallow copy, but still allows to enhance code reusability.

What is scope? Simply put, the scope is a method that begins with prefix `"scope_"` when the rest of the name depends on the scope type. Right now, there are two types of scopes: conditional scope and bind scope; Their breakdown is presented below.

---

***NOTE***

*Scopes **MUST** return `void`.*

*Scopes **CAN** be called directly without `"scope_"` prefix in the code, but such calls are not recommended inside models. **UPDATE:** Such calls are now considered deprecated.*

```php
$model->for_primary_key($alias, $primaryKeyName, $primaryKey);
```

---

### <a name="conditional-scopes"></a> Conditional scopes

This type of scope is used to group one or more query conditions.

The scope method name begins with prefix `"scope_"` and the second word is a common name for the whole group of conditional scopes. Such peculiarity is caused by the fact that the most of models in the project works with multiple tables and we need a way to differentiate between scopes prepared for each table.

---

***NOTE***

*The name of the scope group can be anything but the word `"bind"`- this word is reserved for binding scopes.*

---

What remains of the scope method name is the name of the scope.

For example:

```php
/**
 * Scope a query to filter by expiration datetime.
 *
 * @param \DateTimeInterface|int|string $expired_at
 */
protected function scope_document_expired_at($expired_at)
{
    if (null === ($date = $this->morph_to_datetime($expired_at))) {
        return;
    }

    $this->db->where("`{$this->documents_table_alias}`.`date_latest_version_expires` = ?", $date);
}
```

Here we can see that this method is scope (begins with `"scope_"`) for the group `"documents"` and its name is `"expired_at"`.

Each conditional scope can have from zero to infinite amount of parameters and can be called (or not) during the query lifecicle.

### <a name="binding-scopes"></a> Binding scopes

This type of scope is used to bind to the current query one or more tables, most of the times using `JOIN`.

The rules for the scope's name are mostly the same as for conditional scopes but with one exception: the second word in the name must be `"bind"`, like this:

```php
/**
 * Scope a query to bind users to the query.
 */
protected function scope_bind_document_type()
{
    $this->db->join(
        "`{$this->document_types_table}` AS `{$this->document_types_table_alias}`",
        "`{$this->documents_table_alias}`.`id_type` = `{$this->document_types_table_alias}`.`id_document`",
        'LEFT'
    );
}
```

---

***NOTE***

*The scope method of this type cannot accept parameters.*

---

## <a name="static-relationships"></a> Static relationships

Relationships in Active Records are a way to map the connections between the tables. Sadly, we don't have real Active Records in our DBAL. That is why relationships provided by `App_Model` are in a sense ***static***.
In simple terms, they are just methods which run a separated query that tries to fetch resources from the table that is connected to original resource's table. That connection can be real (`FK` - Foreign Key) or virtual (just a value, mostly for tables with `MyISAM` engine).
Right now static relationships have serveral limitations, such as:

- doesn't have support for pivot tables;
- can accept only simple key;
- cannot be transformed into subquery.

The last limitation is caused by framework DBAL class that doesn't allow to prepare and store queries or get them as string.

Right now the following relationships are supported:

- `has_one` - allows to fetch one record;
- `has_many` - allows to fetch list of records;
- `belongs_to` - the opposite of `has_one`; allows to fetch one record; in `App_Model` is just an alias of `has_one` method.

All of them have the same set of parameters:

```php
protected function has_one(array &$dataset, $as, $table, $foreign_key, $local_key = null, $binding = null, default = null);

protected function has_many(array &$dataset, $as, $table, $foreign_key, $local_key = null, $binding = null, $default = null);

protected function belongs_to(array &$dataset, $as, $table, $foreign_key, $local_key = null, $binding = null, $default = null);
```

These parameters are:

- `$dataset` is an original list of records that may have or may not the connection attribute;

- `$as` is the name of the new attribute that will be added to the record's attributes;

- `$table` - the name of the connected table;

- `$foreign_key` - the primary/connection key in table;

- `$local_key` - name of the key in dataset that contains the value of primary/connection key;

- `$binding` - grants access to the relationship query prior to database request; the value must be a closure with declaration:

    ```php

    function (\TinyMVC_PDO $db, \App_Model $model, ?string $table, ?string $primary_key): void;

    ```


- `$default` - the default value that will be set if no value found for the record

In the end, static relationship method looks like this:

```php
/**
 * Resolves static relationships with buyer.
 */
protected function buyer(array &$records, ?Closure $builder = null, ?string $key = null): void
{
    /** @var User_Model $users */
    $users = model(User_Model::class);

    $this->belongs_to(
        $records,
        $key ?? __FUNCTION__,
        $users->get_users_table(),
        $users->get_users_table_primary_key(),
        'id_buyer',
        $builder
    );
}
```
The parameter `$records` is the list of records from where the connection key will be taken. The found relationship data will be added to this array for each corresponding entry.

The parameter `$builder` has the same purpose and declaration as the `$binding` parameter. Well, most of the time they are the same, if, of course, `$builder` is not wrapped into another `Closure` like this:

```php
$this->belongs_to(
    $records,
    $key ?? __FUNCTION__,
    $billing->get_billing_table(),
    'id_item',
    $this->get_primary_key(),
    function (TinyMVC_PDO $pdo) use ($builder) {
        $pdo->where('id_type_bill = ?', static::ORDER_BILL_TYPE);
        if (null !== $builder) {
            $builder->call($this, ...func_get_args());
        }
    }
);
```

Most of the time the relationship has the same name as the function. It can be changed by transmitting the the third parameter `$key` which can be used as alias for relationship.


After the static relationship is executed, each found entry will be attached to the entry with same connection key value in the `$records` list under the relationship name/alias.

So, for example, the record with `id_buyer` equals to `8944` can be added to many entries in `$records` like this:

```php
array(
    array(
        'id'       => 2234,
        'id_buyer' => 8944,
        'buyer' => array(
            'id' => 8944
            // ...
        )
    ),
    array(
        'id'       => 33246,
        'id_buyer' => 8944,
        'buyer' => array(
            'id' => 8944
            // ...
        )
    )
);
```

---

***NOTE***

*Never give to the relationship method the same name as the connection attribute.*

*Never call another query in the `$binding` closure, or else it will break the current one.*

---

## <a name="out-of-box-select"></a> Out-of-box `SELECT` queries

The majority of the DB queries in project are of type `SELECT`. That is why a lot of attention is put on how to make them easier. `App_Model` allows to greatly reduce the amount of time and code needed to make request if specified set of parameters is provided.
In short, `App_Model` provides the following methods to do `SELECT` queries:

- `find_record` that returns ony **ONE** record from the database;
- `find_records` that returns ony **MANY** records from the database.

The example of usage can be found in the `User_Personal_Documents_Model`:

```php
public function get_document($document_id, array $params = array())
{
    return $this->find_record(
        'document',
        $this->documents_table,
        $this->documents_table_alias,
        'id_document',
        $document_id,
        $params
    );
}

public function find_document(array $params = array())
{
    return $this->find_record(
        'document',
        $this->documents_table,
        $this->documents_table_alias,
        null,
        null,
        $params
    );
}

public function get_documents(array $params = array())
{
    return $this->find_records(
        'document',
        $this->documents_table,
        $this->documents_table_alias,
        $params
    );
}
```

Each one of them receives some common parameters:

- `$section` - given that **MOST** of the models in the project works with multiple tables, this values can be considered an attempt to indicate what scopes from the model to use; basically, most of the times this is **type** of the record you want to fetch;
- `$table` - is the name of the table;
- `$alias` - is the alias of the table; optional value;
- `$params` - an array of the parameters used to make query; the breakdown of the indices can be found [below](#query-parameters);

Method `find_record` have two additional optional parameters: `$primaryKeyName` and `$primaryKey`. They represent the table primary key name and value respectively. When they are provided, the special scope will add a primary key condition to the query.

### <a name="query-parameters"></a> Query parameters

The `$params` **CAN** contain the predefined indices:

- `columns`
- `joins`
- `skip`
- `limit`
- `order`
- `group`
- `with`
- `with_count`
- `conditions`

#### <a name="query-parameters-columns"></a> Parameter `columns`

This parameter must contain the list of the columns in the `SELECT` query. It can be either string or array. If empty then it is considered equals to `"*"`.

```php
'columns' => array('firstname', 'latname', 'COUNT(*) as `all`'),
```

```php
'columns' => 'firstname, latname, COUNT(*) as `all`',
```

#### <a name="query-parameters-joins"></a> Parameter `joins`

This parameter must contain the list of the names of [binding sopes](#binding-scopes) defined in model. Each value must be a non-empty string. Empty array is ignored and not found scopes sre skipped.

```php
'joins' => array('type'),
```

#### <a name="query-parameters-with"></a> Parameter `with`

This parameters allows you to attach static relationships to the `SELECT` query result. The accepted values are of type `Array<String>|Array<?String,Closure>` where the `String` is the name of the relationship.

The most simple way is to put the name directly into this array:

```php
'with' => array('buyer'),
```

But if you want to customize the relationship request, you must use name as key and `Closure` as the value:

```php
'with' => array(
    'buyer',
    'invoice' => function (/*...*/) {
        // ...
    }
),
```
The format of the anonymous function is described in [static relationships](#static-relationships) section.

#### <a name="query-parameters-with-count"></a> Parameter `with_count`

The parameter `with_count` is similar in many ways to `with` parameter. Not only it is defined in the same way, it also uses the same static relationships.
The only difference is that the `with_count` returns the amount of related records. Plus, the value will be added to record attributes with `count_` prefix.

So, when using the same relationship with this parameter:

```php
'with_count' => array('inoices'),
```

we will receive the following:

```php
array(
    // ...
    'count_inoices' => 12
    // ...
)
```

#### <a name="query-parameters-with-count"></a> Parameter `conditions`

This parameter allows to indicate the conditions for the query. It accepts the standard list of key-value pairs. Each key is the name of the conditional scope that exists in the model. The value must be of the type `mixed|Closure<mixed[]>`. `NULL` values are skipped (for now).

When the value is not a function but an object or scalar, it will become the first argument of the scope method.

```php
'conditions' => array(
    'expired_at' => '2019-11-01'
)

// ...

/**
 * Scope a query to filter by expiration datetime.
 *
 * @param \DateTimeInterface|int|string $expired_at
 */
protected function scope_document_expired_at($expired_at): void
{
    // $expired_at = 2019-11-01
}
```

If you want to transmit more parameters you must provide an anonymous function that returns and array of values.

```php
'conditions' => array(
    'expired_at' => function (): array {
        return array('2019-11-01', 'Y-m-d')
    }
)

// ...

/**
 * Scope a query to filter by expiration datetime.
 *
 * @param \DateTimeInterface|int|string $expired_at
 */
protected function scope_document_expired_at($expired_at, string $format): void
{
    // $expired_at = 2019-11-01
    // $format = Y-m-d
}

```

---

***NOTE***

*The only way to have the first scope parameter of type `Closure` is to warp it around another `Closure`*

```php
'conditions' => array(
    'progress' => function () use ($entries): array {
        return array(
            function (string $prefix) use ($entries): array {
                return array_column($entries, 'i');
            },
            12
        )
    }
)

// ...

protected function scope_document_progress(Closure $parser, int $limit): void
{
    // $parser = Closure
    // $limit = 12
}
```

---

#### <a name="query-parameters-group"></a> Parameter `group`

This parameter is used to indicate how the query will be groped. It must contains the list of string values where each on of them is column name.

```php
'group' => array('id', 'views')
```

#### <a name="query-parameters-order"></a> Parameter `order`

This parameter is used to indicate how the query values will be sorted. It must contains the list of key-value pairs where each key is column name and the value is a direction of sorting - `ASC` or `DESC`.

It is also allowed to use statements as instead of column name.

```php
'order' => array(
    'id'                                => 'DESC',
    'CONCAT(`first_name`, `last_name`)' => 'ASC',
)
```

#### <a name="query-parameters-skip"></a> Parameter `skip`

This parameter indicates the amount of the records that will be skipped by the query. It is used in `"LIMIT"` statement as offset and is ignored if its value equals to `NULL`.

```php
'skip' => 12,
```

```php
'skip' => null,
```

#### <a name="query-parameters-limit"></a> Parameter `limit`

This parameter indicates the amount of the records that will be taken by the query. It is used in `"LIMIT"` statement as limitand is ignored if its value equals to `NULL`.

```php
'limit' => 12,
```

```php
'limit' => null,
```


## <a name="out-of-box-delete"></a> Out-of-box `DELETE` queries

Another feature of the `App_Model` are out-of-box `DELETE` queries. You just need to use protected `remove_records`in the public model methods to perform delete.

The declaration of the function is:

```php
protected function remove_records($section, $table, $alias = null, array $params = array());
```
This function accepts the same parameters as the `find_records` function. Plus, they serve the same purpose as well. The only difference is that `$params` accepts only two keys: `joins` and `conditions` Other parameters will be ignored.

The example of usege of the out-of-box feature:

```php
public function delete_records(array $params = array())
{
    return $this->remove_records(
        null,
        $this->table,
        $this->alias,
        $params
    );
}

//...

public function delete_record($record_id)
{
    return $this->remove_records(
        null,
        $this->table,
        $this->alias,
        array(
            'conditions' => array('id' => $record_id),
        )
    );
}
```

## <a name="attributes-mutations"></a> Attributes mutations

`App_Model` allows to mutate entity attributes in different ways before writing them into the databse. This operation is not automatic and can be done by using a special method. Plus, you need to make some preparations before use it.

First of all, you need to prepara a set of metadata information that allows to perform such operations. The format of the metadata is following: `Array<int,Array<string,mixed>>` - the list of arrays with key-values pairs of specific options:

```php
 array(
    array('name' => 'id_document', 'type' => 'int', 'fillable' => false),
    array('name' => 'id_type',     'type' => 'int', 'nullable' => true),
)
```

The `name` field is requred and it represents the name of the column in the database. Other fields are optional and used for different operations, for example, attribute casting requires the `type` field. Some of them may have default values depending on the type of mutation.

To transform attributes you must use the protected function `prepare` which has the following definition:

```php
/**
 * @param array|\ArrayObject|\IteratorAggregate|\stdClass $data
 */
protected function prepare($data, $definitions, bool $force = false): array
```

The `$data` parameter is the set of database entity attributes. Most of the time this method is called on data insert or update. The `$definitions` parameter is the metadata array described above. Flag `$force` is used to control guarded attributes filtering behaviour.

This function can be used in insert and update methods:

```php
public function create_document(array $document, $force = false)
{
    return $this->db->insert(
        $this->documents_table,
        $this->prepare(
            $document,
            $this->documents_columns_metadata,
            $force
        )
    );
}
```

Depending on the metadata the final result can greatly differ from the original, beginning from deleted columns and ending with changed attribute type. All of the possible transformations are described below.

### <a name="attributes-casting"></a> Attributes casting

One of the attribute mutation possible in `App_model` is the attributes casting. It allows to transform entity attributes values into database-accepted types. For example the `\DateTime` object can be transformed into string with valid format.

---

***NOTE***

*Right now only PHP-to-DB cast is implemented*

---

To use attributes casting, the option `type` must be added to entity field metadata. The `type` option can have the following values:

- `int`, `integer` - casted to `SAMLLINT`, `INTEGER` and `BIGINT` values
- `float` - casted to `FLOAT` values
- `double` - casted to `DOUBLE` values
- `decimal` - casted to `DECIMAL` values
- `bool`, `boolean` - casted to `TINYINT` values
- `set` - casted to `SET` values
- `enum` - casted to `ENUM` values
- `string` - casted to `CHAR` AND `VARCHAR` values
- `json`, `array`, `object` - casted to `JSON` values
- `date` - casted to `DATE` values
- `time` - casted to `TIME` values
- `datetime` - casted to `DATETIME` OR `TIMESTAMP` values
- `money` - casted to `DECIMAL` values

---

***NOTE***

*New types can be added in the future*

---

After the processing the resulted set of attributes will correspond (hopefully) the DB types.

### <a name="guarded-attributes"></a> Guarded attributes

It is also possible to guard attributes. This feature can be enabled by adding the `fillable` option to the entity metadata array. Basically, it will jsut delete those attributes from the final result.

This feature can be used for prevent rewriting of some sensitive columns, like `id` on the mass-assignement. But it can also be disabled by providing the `TRUE` as the thord parameter to the `prepare` method.

---

***NOTE***

*The deafult attribute is `false`*

---

### <a name="guarded-attributes"></a> Nullable attributes

By default, `prepare` will drop the attributes with the `NULL` values to prevent their writing into the database. But this behaviour can be explicitly disabled for the columns by adding the `nullable` option equal to `TRUE` into column metadata array.

---

***NOTE***

*The `nullable` option is recommeded only for nullable columns or else it can cause some problems on writing.*

---

# <a name="standalone-model"></a> Standalone Model

The `Standalone_Model` is build on top of the `App_Model` as an attempt to cover weaknesses of this model and offer better usability. Not only it is easier to configure but it also requires to write less code to bring the whole model functionality out.

Even if `App_Model` offers a lot of features in comparison it to base model, it has its own problems. The biggest of them is that there is no reliable way to prevent developers to stick only to one model. Well, true be told, from the very beginning this was a tradeof of the backward compatibility, but with time it grew in a huge problem.
That is why the scope methods have prefixes in the names - to satisfy needs to work with many tables in one model. In a way, this is crude attempt to create namespaces in the one model to separate conditions and such.

With the time, the models that extended `App_Model` class became bloated with dozens of methods loosely grooped togher with one prefix. Right now, it is hard to resolve this problem without huge code rewrite, that is why the decision to create new specialized model was made.

As by itself, the `Standalone_Model` offers the following features:

- configurable table name
- configurable table alias
- configurable primary key
- configurable timestamps
- scopes without prefixes
- base methods such as `has`, `find`, `find_one`, `find_all`, `insert_one` etc.
- methods to get table name, alias and some other information
- methods that allows to paginate over the records
- properly documented methods

Given all of this the `Standalone_Model` allows to begin work with the database without adding anything to the new model:

```php
/**
 * Another_Test model
 */
class Another_Test_Model extends Standalone_Model
{
    /**
     * {@inheritdoc}
     */
    protected $table = "test_table";

    /**
     * {@inheritdoc}
     */
    protected $primary_key = "id";

    /**
     * {@inheritdoc}
     */
    protected $metadata = array();
}
```

You need just to fill up the `$metadata` array and you can begin work with the model instantly. Plus, the `Standalone_Model` has a room of improvement. For example, in the future the `$metadata` array will be replaced with easier configuration that does not require to describe all fields there.

## <a name="standalone-model-configuration"></a> Configuration

Of course, before using this model it requires at least basic configuration. The next sessions will describe how to do this without shoting in the leg.

### <a name="standalone-model-configuration-table-name"></a> Table name

Maybe, the most basic configuration of the model - jsut and the name of the table and that is all!

```php
/**
 * {@inheritdoc}
 */
protected $table = "test_table";
```

You can get this name later using the method `Standalone_Model::get_table()`

### <a name="standalone-model-configuration-table-alias"></a> Table alias

The optional configuration that can make life easier. This parameter is configured in the same way as table name - just put a string there.

```php
/**
 * {@inheritdoc}
 */
protected $alias = "TEST";
```

It can be read by using the method `Standalone_Model::get_alias()`.

### <a name="standalone-model-configuration-primary-key"></a> Primary key

Right now only simple primary keys are supported, so there is not difficulties with their configuration - the old boring strings are in the deal again:

```php
/**
 * {@inheritdoc}
 */
protected $primary_key = "id";
```

You can get this value using the method `Standalone_Model::get_primary_key()`

### <a name="standalone-model-configuration-metadata"></a> Metadata

No changes from how it is used in `App_Model`. Right now.

```php
/**
 * {@inheritdoc}
 */
protected $metadata =  array(
    array('name' => 'id',   'type' => 'int',    'fillable' => false),
    array('name' => 'name', 'type' => 'string', 'nullable' => true),
)
```

If you want to obtain this information then use the method `Standalone_Model::get_metadata()`. But seriously, never do that.

### <a name="standalone-model-configuration-per-page"></a> Per page value

This value is relevant when you use methods `Standalone_Model::paginate()` or `Standalone_Model::get_paginator()` and only in the case when no `$perPage` value is provided.
It is jsut integer, nothing serious.

```php
/**
 * {@inheritdoc}
 */
protected $per_page = 20;
```

And yet again, you can read it by running `Standalone_Model::get_per_page()`.

### <a name="standalone-model-configuration-timestaps"></a> Timestaps

The `Standalone_Model` can automatically add timestamps on insert and update operations but only if this feature is enabled explicitly. And now things become little trickier.

First of all you need to enable it manually:

```php
/**
 * {@inheritdoc}
 */
protected $timestamps = true;
```

And nothing works. Why? Because if model doesn't know what fields to update if won't give a damn about timestamps at all. That is why you need to indicate those fields manually:

```php
/**
 * {@inheritdoc}
 */
protected const CREATED_AT = `created_at`;

/**
 * {@inheritdoc}
 */
protected const UPDATED_AT = `updated_at`;
```

And thats all. Now those fields will be attached to the final data array under the respective names.

If you want to know if you enabled them jsut run `Standalone_Model::uses_timestamps()`, it should help.

## <a name="standalone-model-methods"></a> Methods

A lot.
And all of them are used constantly.

The full list is here:

- `has` - checks if record exists by its ID;
- `find` - reads the record by its ID;
- `find_all` - reads all records from table. I am serious. All of them;
- `find_one_by` - finds record by specified condition(s);
- `find_all_by` - find all records that satisfy the provided conditions;
- `paginate` - returns the pagination for specified page, limit and conditions with attached data;
- `get_paginator` - returns the pagination for specified page, limit and conditions without attached data. I don't know, maybe someone needs that;
- `count_all` - counts all records. Like, all of them;
- `count_by` - counts records by provided conditions;
- `insert_one` - creates one record;
- `insert_many` - create many records. I sincerely recommend to use something else;
- `update_one` - updates one record. No fancy tricks here;
- `delete_one` - deletes one record;

And that's at least for now. Later some other methods can be added.

# <a name="creating-models"></a> Creating models

The most direct way is to do it by hands. Just create class, extend `App_Model` or `Standalone_Model`, configure it and done!

The smart way is to use `make:model` command. It provides a good interactive way of creating the standard model of desired type. Plus, it can offer some fine tuning. Just run `make:model --help` and find everything you need to spawn the whole army of models.
