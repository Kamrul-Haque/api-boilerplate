<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;

class CompositeUnique implements ValidationRule
{
    protected string $table;

    protected string $compositeColumn;

    protected ?string $compositeColumnValue;

    protected ?int $ignoreId;

    public function __construct(string $table, string $compositeColumn, ?string $compositeColumnValue = null, ?int $ignoreId = null)
    {
        $this->table = $table;
        $this->compositeColumn = $compositeColumn;
        $this->compositeColumnValue = $compositeColumnValue;
        $this->ignoreId = $ignoreId;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (
            ! DB::table($this->table)
                ->when($this->ignoreId, function ($query) {
                    $query->whereNotIn('id', [$this->ignoreId]);
                })
                ->where($attribute, $value)
                ->where($this->compositeColumn, $this->compositeColumnValue)
                ->exists()
        ) {
            $fail('the :attribute must be unique for the given '.$this->compositeColumn);
        }

    }
}
