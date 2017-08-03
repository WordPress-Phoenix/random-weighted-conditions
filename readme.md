# Random Weighted Conditions

Simple 100 line class for randomizing over a set of string conditions given a weighted percentage.

# Example

```php
require_once 'class-random-weighted-conditions.php';

$conditions = array(
	'red'    => 20,
	'orange' => 20,
	'yellow' => 20,
	'green'  => 20,
	'blue'   => null,
	'purple' => 5,
	'silver' => 0,
);

$colors = new \WPAZ_RWC\V_1_0\Random_Weighted_Conditions( $conditions );
echo $colors->get_random_condition();
```

# How it Works
### Step 1
Developer provides `array()` of `key => val` pairs where condition keys and weight values. 
```
0 will exclude a condition, but `empty()` values are given equal distribution of remaining total.
```
### Step 2
Class validates data and creates an array with 100 string conditions, based on weights provided.

### Step 3
Class randomly selects one of strings.

:dancer:
