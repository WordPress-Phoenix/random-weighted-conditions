<?php

namespace WPAZ_RWC\V_1_2;

/**
 * Class Random_Weighted_Conditions
 *
 * @package FanSided\Core\Util
 */
class Random_Weighted_Conditions {

	/**
	 * @var int
	 */
	public $count;

	/**
	 * @var int
	 */
	public $total = 100;

	/**
	 * @var bool
	 */
	public $valid = false;

	/**
	 * @var null
	 */
	public $debug = null;

	/**
	 * @var array
	 */
	public $created_conditions;

	/**
	 * @var array
	 */
	public $weighted_conditions = array();

	/**
	 * Random_Weighted_Conditions constructor.
	 *
	 * @param array $conditions
	 */
	function __construct( $conditions = array() ) {
		$this->created_conditions = $conditions;
		$this->count              = count( $this->created_conditions );
		$this->validate();
		if ( ! $this->valid ) {
			return $this->debug;
		}
		$this->set_condition_weights();
	}

	/**
	 * Main method to use for retrieving a random string
	 * @return mixed|string
	 */
	function __toString() {
		return $this->get_random_condition();
	}

	/**
	 * Decide Random Item In Probability Array
	 * @return mixed|string
	 */
	public function get_random_condition() {
		$rand_key = mt_rand( 0, count( array_keys( $this->weighted_conditions ) ) - 1 );

		return ! empty( $this->weighted_conditions ) ? $this->weighted_conditions[ $rand_key ] : '';
	}

	/**
	 * Main Preparation Function
	 */
	protected function set_condition_weights() {
		$weighted       = array();
		$auto_weighting = array();
		$auto_weighted  = array();
		foreach ( $this->created_conditions as $condition => $weight ) {
			if ( '0' == $weight ) {
				continue; // zero is the only value that can be used to exclude, other empty() === true values are used for auto-weighting
			} elseif ( empty( $weight ) ) {
				$auto_weighting[] = $condition;
			} elseif ( is_int( $weight ) ) {
				$current_condition = array_fill( 0, $weight, $condition );
				$weighted          = array_merge( $weighted, $current_condition );
			} else {
				$this->debug['failed-conditions'][ $condition ] = $weight;
			}
		}
		if ( ! empty( $auto_weighting ) ) {
			$remaining_outcomes = $this->total - count( array_keys( $weighted ) );
			$equi_fill          = $remaining_outcomes / count( $auto_weighting );
			foreach ( $auto_weighting as $condition ) {
				$current_condition = array_fill( 0, $equi_fill, $condition );
				$auto_weighted     = array_merge( $auto_weighted, $current_condition );
			}
			if ( count( $weighted ) < $this->total ) { // we're recounting purposefully
				$remaining_outcomes = $this->total - count( $weighted );
				$ext_weights        = array_map( function ( $el ) { return $el * 10; }, $auto_weighting );
				$round_off          = array();
				for ( $i = 0; $i <= $remaining_outcomes; $i ++ ) {
					if ( ! empty( $ext_weights[ $i ] ) ) {
						$round_off[] = $ext_weights[ $i ];
					}
				}
				$auto_weighted = $auto_weighted + $round_off;
			}
		}
		$this->weighted_conditions = array_merge( $weighted, $auto_weighted );
	}

	/**
	 * Confirms unique keys and that weights to excede defined total
	 */
	protected function validate() {
		if ( $this->has_unique_conditions()
		     && $this->weights_dont_excede_total()
		) {
			$this->valid = true;
		} else {
			$this->valid = false;
			$this->debug = array(
				'has_unique_conditions'           => $this->has_unique_conditions(),
				'weights_dont_excede_total' => $this->weights_dont_excede_total(),
			);
		}
	}

	/**
	 * Sum of Conditions
	 *
	 * @return float|int
	 */
	public function get_calculated_total() {
		return array_sum( array_values( array_count_values( $this->weighted_conditions ) ) );
	}

	/**
	 * Count frequency of condition in probability array
	 *
	 * @return array
	 */
	public function get_simple_distribution() {
		return array_count_values( $this->weighted_conditions ) + array( 'Calculated Total' => $this->get_calculated_total() );
	}

	/**
	 * Run formatter on simple conditions array for human output
	 *
	 * @return array
	 */
	public function get_distribution() {
		return array_map( array( $this, 'create_display' ), $this->get_simple_distribution() );
	}

	/**
	 * Simple WP_List_Table builder
	 *
	 * @param $array
	 *
	 * @return string
	 */
	public function build_table( $array ) {
		ob_start();
		echo '<table class="wp-list-table widefat striped">';
		foreach ( $array as $key => $val ) {
			echo '<tr><td><code>' . $key . '</code></td><td><code>' . $val . '</code></td></tr>';
		}
		echo '</table>';

		return ob_get_clean();
	}

	/**
	 * Creates display value for human-read condition
	 *
	 * @param $item
	 *
	 * @return string
	 */
	protected function create_display( $item ) {
		return $item . ' / ' . $this->get_calculated_total() . ' (' . $item . '%)';
	}

	/**
	 * Confirms no two condition strings are the same
	 *
	 * @return bool
	 */
	protected function has_unique_conditions() {
		return $this->count === count( array_unique( array_keys( $this->created_conditions ) ) );
	}

	/**
	 * Confirms user-provided conditions don't sum to greater than defined total (defaults to 100)
	 *
	 * @return bool
	 */
	protected function weights_dont_excede_total() {
		return array_sum( $this->created_conditions ) <= $this->total;
	}
}
