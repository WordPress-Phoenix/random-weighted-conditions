# Random Weighted Conditions

Pass a set of conditions and weights, and build a random condition generator.

i.e.

When serving fruit, serve `Apples` 50% of the time, serve `Oranges` 10% of the time and serve `Grapes` the rest of the time.

# Example

```php
require_once 'class-random-weighted-conditions.php';

$conditions = array(
	'apples'    => 50,
	'oranges'   => 10,
	'grapes'    => null,
);

$colors = new \WPAZ_RWC\V_1_0\Random_Weighted_Conditions( $conditions );

// will be `apples` 50%, `oranges` 10% and `grapes` 40%
echo $colors->get_random_condition();
```

# How it Works
### Step 1
Developer provides `array()` of `key => val` pairs with condition keys and weight values. 
##### Possible Values:
* 0-100 are treated as percentage weights. 0 completely excludes a condition.
* All other true-testing `empty()` values are giving _equal distribution of remaining total, following preassigned weights_

### Step 2
Class validates data and creates an array with 100 string conditions (can be modified), based on weights provided.

### Step 3
Class randomly selects one of strings.

:dancer:
