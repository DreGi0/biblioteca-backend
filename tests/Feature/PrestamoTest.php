<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Loan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PrestamoTest extends TestCase
{
    use RefreshDatabase;

    private function userLibrarian(): User
    {
        Role::firstOrCreate(['name' => 'librarian', 'guard_name' => 'api']);
        $user = User::factory()->create();
        $user->assignRole('librarian');
        $this->actingAs($user, 'sanctum');
        return $user;
    }

    private function userStudent(): User
    {
        Role::firstOrCreate(['name' => 'student', 'guard_name' => 'api']);
        $user = User::factory()->create();
        $user->assignRole('student');
        $this->actingAs($user, 'sanctum');
        return $user;
    }

    private function userTeacher(): User
    {
        Role::firstOrCreate(['name' => 'teacher', 'guard_name' => 'api']);
        $user = User::factory()->create();
        $user->assignRole('teacher');
        $this->actingAs($user, 'sanctum');
        return $user;
    }

    public function test_teacher_can_create_loan(): void
    {
        $this->userTeacher();

        $book = Book::factory()->create([
            'available_copies' => 3,
            'is_available' => true
        ]);

        $response = $this->postJson('/api/v1/loans', [
            'requester_name' => 'Maria Lopez',
            'book_id' => $book->id,
        ]);

        $response->assertStatus(201)
                 ->assertJsonFragment([
                     'book_id' => $book->id
                 ]);

        $this->assertDatabaseHas('loans', [
            'book_id' => $book->id
        ]);
    }

    public function test_student_can_create_loan(): void
    {
        $this->userStudent();

        $book = Book::factory()->create([
            'available_copies' => 5,
            'is_available' => true
        ]);

        $response = $this->postJson('/api/v1/loans', [
            'requester_name' => 'Juan Perez',
            'book_id' => $book->id,
        ]);

        $response->assertStatus(201)
                 ->assertJsonFragment([
                     'book_id' => $book->id
                 ]);

        $this->assertDatabaseHas('loans', [
            'book_id' => $book->id,
            'requester_name' => 'Juan Perez'
        ]);
    }

    public function test_librarian_cannot_create_loan(): void
    {
        $this->userLibrarian();

        $book = Book::factory()->create([
            'available_copies' => 5,
            'is_available' => true
        ]);

        $response = $this->postJson('/api/v1/loans', [
            'requester_name' => 'Carlos',
            'book_id' => $book->id,
        ]);

        $response->assertStatus(403);

        $this->assertDatabaseMissing('loans', [
            'book_id' => $book->id
        ]);
    }

    public function test_user_can_return_loan(): void
    {
        $this->userStudent();

        $book = Book::factory()->create([
            'available_copies' => 2,
            'is_available' => true
        ]);

        $loan = Loan::factory()->create([
            'book_id' => $book->id,
            'requester_name' => 'Harry Styles',
            'return_at' => null
        ]);

        $response = $this->postJson("/api/v1/loans/{$loan->id}/return");

        $response->assertStatus(200);

        $this->assertDatabaseHas('loans', [
            'id' => $loan->id,
        ]);
    }

    public function test_librarian_cannot_return_loan(): void
    {
        $this->userLibrarian();

        $book = Book::factory()->create();

        $loan = Loan::factory()->create([
            'book_id' => $book->id,
            'requester_name' => 'Harry Styles'
        ]);

        $response = $this->postJson("/api/v1/loans/{$loan->id}/return");

        $response->assertStatus(403);
    }

    public function test_user_can_list_loans(): void
    {
        $this->userStudent();

        $book = Book::factory()->create();

        Loan::factory()->count(2)->create([
            'book_id' => $book->id,
            'requester_name' => 'Harry Styles'
        ]);

        $response = $this->getJson('/api/v1/loans');

        $response->assertStatus(200)
                 ->assertJsonCount(2);
    }
}