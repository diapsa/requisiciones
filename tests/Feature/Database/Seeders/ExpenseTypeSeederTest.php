<?php

namespace Tests\Feature\Database\Seeders;

use App\Models\ExpenseType;
use Database\Seeders\ExpenseTypeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpenseTypeSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_creates_the_initial_expense_types_in_screen_order(): void
    {
        $this->seed(ExpenseTypeSeeder::class);

        $expenseTypes = ExpenseType::query()->orderBy('sort_order')->get();

        $this->assertCount(13, $expenseTypes);
        $this->assertSame('fuel', $expenseTypes->first()->code);
        $this->assertSame('fuel_by_distance', $expenseTypes->first()->calculation_method);
        $this->assertSame('other', $expenseTypes->last()->code);
        $this->assertFalse($expenseTypes->firstWhere('code', 'airfare')->is_requestable);
        $this->assertTrue($expenseTypes->firstWhere('code', 'lodging')->is_requestable);
    }

    public function test_running_it_again_neither_duplicates_nor_overwrites_edits(): void
    {
        $this->seed(ExpenseTypeSeeder::class);
        ExpenseType::query()->where('code', 'tolls')->update(['name' => 'Peajes']);

        $this->seed(ExpenseTypeSeeder::class);

        $this->assertDatabaseCount('expense_types', 13);
        $this->assertDatabaseHas('expense_types', ['code' => 'tolls', 'name' => 'Peajes']);
    }
}
