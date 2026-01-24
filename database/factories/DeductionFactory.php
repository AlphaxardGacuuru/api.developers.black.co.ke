<?php

namespace Database\Factories;

use App\Models\Invoice;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Deduction>
 */
class DeductionFactory extends Factory
{
	/**
	 * Define the model's default state.
	 *
	 * @return array<string, mixed>
	 */
	public function definition(): array
	{
		return [
			'user_id' => User::factory(),
			'invoice_id' => Invoice::factory(),
			'amount' => $this->faker->randomFloat(2, 10, 1000),
			'issue_date' => $this->faker->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
			'notes' => $this->faker->optional()->sentence(),
		];
	}
}
