<?php

namespace WPAZ_RWC\V_1_0;

class Random_Weighted_Conditions {
	public $count;
	public $total = 100;
	public $valid = false;
	public $debug = null;
	public $created_conditions;
	public $weighted_conditions = array();

	function __construct( $conditions = array() ) {
		$this->created_conditions = $conditions;
		$this->count              = count( $this->created_conditions );
		$this->validate();
		if ( ! $this->valid ) {
			return $this->debug;
		}
		$this->set_condition_weights();
	}

	public function get_random_condition() {
		if ( empty( $this->weighted_conditions ) ) {
			return '';
		}
		$random_key = mt_rand( 0, count( array_keys( $this->weighted_conditions ) ) - 1 );

		return $this->weighted_conditions[ $random_key ];
	}

	public function get_distribution() {
		return array_count_values( array_combine( array_fill( 0, count( $this->weighted_conditions ), 'smh' ), $this->weighted_conditions ) );
	}

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
				$ext_weights        = $auto_weighting + $auto_weighting + $auto_weighting + $auto_weighting + $auto_weighting
				                      + $auto_weighting + $auto_weighting + $auto_weighting + $auto_weighting + $auto_weighting; // 🙈 it works ¯\_(ツ)_/¯
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

	protected function validate() {
		if ( $this->has_unique_keys()
		     && $this->weights_dont_exede_total()
		) {
			$this->valid = true;
		} else {
			$this->valid = false;
			$this->debug = array(
				'has_unique_keys'           => $this->has_unique_keys(),
				'weights_dont_exede_total' => $this->weights_dont_exede_total(),
			);
		}
	}

	protected function has_unique_keys() {
		return $this->count === count( array_unique( array_keys( $this->created_conditions ) ) );
	}

	protected function weights_dont_exede_total() {
		return array_sum( $this->created_conditions ) <= $this->total;
	}

}
