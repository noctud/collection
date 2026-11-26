# Sorting

Every collection and map in the library is sequentially ordered, so sorting is supported across all types.

## Naming convention

The sorting API follows two consistent patterns:

| Pattern | Behavior |
|---------|----------|
| `sorted*` | Returns a **new** sorted collection (available on all types) |
| `sort*` | Sorts **in-place** (mutable types only) |

Within each pattern:

| Suffix | Input |
|--------|-------|
| *(none)* | Natural order of elements |
| `*Desc` | Descending natural order |
| `*By` | Selector function: extracts a comparable value |
| `*ByDesc` | Selector, descending |
| `*With` | Comparator function: full control over comparison |

Maps have additional variants for key-specific and value-specific sorting:

| Method | Target |
|--------|--------|
| `sortedByKey` / `sortByKey` | Sort by key |
| `sortedByValue` / `sortByValue` | Sort by value |
| `sortedWithKey` / `sortWithKey` | Comparator on keys |
| `sortedWithValue` / `sortWithValue` | Comparator on values |
| `sortedWith` / `sortWith` | Comparator on `MapEntry` objects |

## Collection / List / Set sorting

### Natural order

```php
$list = listOf([3, 1, 4, 1, 5]);

$list->sorted(); // [1, 1, 3, 4, 5]
$list->sortedDesc(); // [5, 4, 3, 1, 1]
```

### Selector (sortedBy)

Extract a comparable value from each element:

```php
$users = listOf($users);

$users->sortedBy(fn($u) => $u->name); // alphabetical
$users->sortedByDesc(fn($u) => $u->score); // highest score first
```

### Comparator (sortedWith)

Full control over the comparison:

```php
$list->sortedWith(fn($a, $b) => strlen($a) <=> strlen($b));
```

### In-place (mutable only)

```php
$list = mutableListOf([3, 1, 2]);

$list->sort(); // [1, 2, 3]
$list->sortDesc(); // [3, 2, 1]
$list->sortBy(fn($n) => -$n); // [3, 2, 1]
$list->sortWith(fn($a, $b) => $a <=> $b); // [1, 2, 3]
```

### Other order operations

```php
$list->reversed(); // new list in reverse order
$list->shuffled(); // new list in random order

// In-place (mutable only)
$list->reverse();
$list->shuffle();
```

## Map sorting

Maps don't have `sorted()` or `sortedDesc()` (natural order is ambiguous for key-value pairs), but support selector and comparator patterns plus key/value-specific variants.

### By key

```php
$map = mapOf(['banana' => 2, 'apple' => 5, 'cherry' => 1]);

$map->sortedByKey(); // apple => 5, banana => 2, cherry => 1
$map->sortedByKeyDesc(); // cherry => 1, banana => 2, apple => 5
$map->sortedByKey(fn($k) => strlen($k)); // apple, banana, cherry (by length)
```

### By value

```php
$map->sortedByValue(); // cherry => 1, banana => 2, apple => 5
$map->sortedByValueDesc(); // apple => 5, banana => 2, cherry => 1
$map->sortedByValue(fn($v) => -$v); // apple => 5, banana => 2, cherry => 1
```

### By entry (selector on both key and value)

```php
$map->sortedBy(fn($v, $k) => $v); // sort by value using generic selector
$map->sortedByDesc(fn($v, $k) => strlen($k)); // sort by key length, descending
```

### Comparators

```php
// Compare keys
$map->sortedWithKey(fn($a, $b) => strcmp($a, $b));

// Compare values
$map->sortedWithValue(fn($a, $b) => $a <=> $b);

// Compare MapEntry objects
$map->sortedWith(fn(MapEntry $a, MapEntry $b) => $a->value <=> $b->value);
```

::: tip sortWith() / sortedWith() on maps
Map comparators receive two `MapEntry` objects, not four separate `$v, $k, $v, $k` arguments. This keeps the callback clean, since you access `$a->key`, `$a->value`, `$b->key`, `$b->value` as needed. Use `sortedWithKey()` or `sortedWithValue()` when you only need to compare one of the two.
:::

### In-place map sorting (mutable only)

```php
$map = mutableMapOf(['b' => 2, 'a' => 1]);

$map->sortByKey(); // a => 1, b => 2
$map->sortByValue(); // a => 1, b => 2
$map->sortByKeyDesc(); // b => 2, a => 1
$map->sortBy(fn($v, $k) => $v);
$map->sortWithKey(fn($a, $b) => strcmp($a, $b));
$map->sortWithValue(fn($a, $b) => $a <=> $b);
$map->sortWith(fn(MapEntry $a, MapEntry $b) => $a->key <=> $b->key);
```

### Other order operations

```php
$map->reversed(); // new map in reverse order
$map->shuffled(); // new map in random order

// In-place (mutable only)
$map->reverse();
$map->shuffle();
```

## Quick reference

### Collection / List / Set

| Method | Returns | Description |
|--------|---------|-------------|
| `sorted()` | new | Natural ascending order |
| `sortedDesc()` | new | Natural descending order |
| `sortedBy($selector)` | new | By selector, ascending |
| `sortedByDesc($selector)` | new | By selector, descending |
| `sortedWith($comparator)` | new | By comparator |
| `reversed()` | new | Reversed order |
| `shuffled()` | new | Random order |
| `sort()` | $this | In-place ascending |
| `sortDesc()` | $this | In-place descending |
| `sortBy($selector)` | $this | In-place by selector |
| `sortByDesc($selector)` | $this | In-place by selector, desc |
| `sortWith($comparator)` | $this | In-place by comparator |
| `reverse()` | $this | In-place reverse |
| `shuffle()` | $this | In-place shuffle |

### Map

Maps share `sortedBy`, `sortedByDesc`, `sortedWith`, `reversed`, `shuffled` (and their in-place variants) with Collection, plus key/value-specific methods:

| Method | Returns | Description |
|--------|---------|-------------|
| `sortedByKey($selector?)` | new | By key, optional selector |
| `sortedByKeyDesc($selector?)` | new | By key desc, optional selector |
| `sortedByValue($selector?)` | new | By value, optional selector |
| `sortedByValueDesc($selector?)` | new | By value desc, optional selector |
| `sortedWithKey($comparator)` | new | Key comparator |
| `sortedWithValue($comparator)` | new | Value comparator |
| `sortedWith($comparator)` | new | MapEntry comparator |
| `sortByKey($selector?)` | $this | In-place by key |
| `sortByKeyDesc($selector?)` | $this | In-place by key desc |
| `sortByValue($selector?)` | $this | In-place by value |
| `sortByValueDesc($selector?)` | $this | In-place by value desc |
| `sortWithKey($comparator)` | $this | In-place key comparator |
| `sortWithValue($comparator)` | $this | In-place value comparator |
| `sortWith($comparator)` | $this | In-place MapEntry comparator |
