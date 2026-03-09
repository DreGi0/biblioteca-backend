<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;


class LibroTest extends TestCase
{
    use RefreshDatabase;

    //Helpers

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

    public function test_user_can_list_books(): void
    {
        $this->userLibrarian();
        Book::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/books');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                        '*' => ['id', 'title', 'description', 'ISBN', 'total_copies', 'available_copies', 'is_available'],
         ]);

    }

    public function test_list_books_fails_without_auth(): void
    {
    $response = $this->getJson('/api/v1/books');

    $response->assertStatus(401);
    }

    public function test_bibliotecario_can_create_book(): void
    {
        $this->userLibrarian();

        $response = $this->postJson('/api/v1/books', [

            'title'            => 'Alicia en el país de las maravillas',
            'description'      => 'Narra el viaje de Alicia, una niña que cae por una madriguera',
            'ISBN'             => '9780307350435',
            'total_copies'     => 5,
            'available_copies' => 5,
            'is_available'     => true,
       ]);

       $response->assertStatus(201)
                ->assertJsonFragment(['title' => 'Alicia en el país de las maravillas']);
        
        $this->assertDatabaseHas('books', ['ISBN' => '9780307350435']);

    }

    public function test_create_book_fails_for_student(): void
    {
        $this->userStudent();

        $response = $this->postJson('/api/v1/books', [

            'title'            => 'Sin permiso para esta acción ',
            'description'      => 'Sin acceso',
            'ISBN'             => '0000000000001',
            'total_copies'     => 1,
            'available_copies' => 1,
       ]);

       $response->assertStatus(403);
       $this->assertDatabaseMissing('books', ['ISBN' => '0000000000001']);
    }

    public function test_bibliotecario_can_update_book(): void
    {
        $this->userLibrarian();

        $book = Book::factory()->create(['title' => 'Titulo nuevo']);

        $response =$this->putJson("/api/v1/books/{$book->id}", [
            'title' => 'Titulo actualizado',
        ]);
        $response->assertStatus(200)
                ->assertJsonFragment(['title'=> 'Titulo actualizado']);
        
        $this->assertDatabaseHas('books', ['id' => $book->id, 'title' => 'Titulo actualizado']);
    }

    public function test_update_book_fails_for_docente (): void
    {
        $this->userTeacher();
        $book = Book::factory()->create(['title'=> 'Sin acceso']);

        $response =$this->putJson("/api/v1/books/{$book->id}", [
            'title' => 'Sin permiso para modificar',
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseHas('books', ['id' => $book->id, 'title'=> 'Sin acceso']);
    }

    public function test_bibliotecario_can_delete_book(): void
    {
        $this->userLibrarian();
        $book = Book::factory()->create();

        $response =$this->deleteJson("/api/v1/books/{$book->id}");

        $response->assertStatus(200)
                ->assertJsonFragment(['message' => 'Book deleted']);
                
        $this->assertDatabaseMissing('books', ['id'=> $book->id]);
    }

    public function test_delete_book_fails_for_student ():void 
    {
        $this->userStudent();
        $book = Book::factory()-> create();
        
       $response =$this->deleteJson("/api/v1/books/{$book->id}");

       $response->assertStatus(403);
       $this->assertDatabaseHas('books', ['id' => $book->id]);
    }

    public function test_use_can_see_book_detail(): void
    {
        $this->userStudent();
        $book = Book::factory()->create();

        $response = $this->getJson("/api/v1/books/{$book->id}");

        $response->assertStatus(200)
        ->assertJsonFragment(['id'=> $book->id, 'title' => $book->title]);
    }

}