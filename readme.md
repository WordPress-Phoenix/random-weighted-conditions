# Random Weighted Conditions

Simple class for randomizing over a set of string conditions given a weighted percentage.

# Example

```php
require_once 'class-weighted-condition-switchboard.php';

$conditions = array(
	'red'    => 20,
	'orange' => 20,
	'yellow' => 20,
	'green'  => 20,
	'blue'   => null,
	'purple' => 10,
);

$colors = new \WPAZ_RWC\V_1_0\Random_Weighted_Conditions( $conditions );
echo $colors->get_random_condition();
```
