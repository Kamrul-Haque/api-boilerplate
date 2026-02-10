<?php

namespace Database\Factories;

use App\Models\Module;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class ModuleFactory extends Factory
{
    protected $model = Module::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->words(2);

        return [
            'name' => $name,
            'description' => $this->faker->text(),
            'route_prefix' => Str::slug($name),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
