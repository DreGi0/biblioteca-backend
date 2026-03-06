# Matriz de Pruebas — Biblioteca Backend

## Autenticación

| # | Prueba | Descripción | Tipo | Endpoint | Resultado Esperado |
|---|--------|-------------|------|----------|--------------------|
| 1 | test_it_can_login | Usuario se autentica con credenciales válidas y recibe token | Feature | POST /api/v1/login | 200 + access_token |
| 2 | test_login_fails_with_invalid_credentials | Login con credenciales incorrectas | Feature | POST /api/v1/login | 422 + mensaje de error |
| 3 | test_user_can_logout | Usuario autenticado cierra sesión exitosamente | Feature | POST /api/v1/logout | 200 + mensaje confirmación |
| 4 | test_logout_fails_without_auth | Logout sin token de autenticación | Feature | POST /api/v1/logout | 401 |
| 5 | test_user_can_see_profile | Usuario autenticado puede ver su perfil | Feature | GET /api/v1/profile | 200 + datos del usuario |
| 6 | test_profile_fails_without_auth | Ver perfil sin token de autenticación | Feature | GET /api/v1/profile | 401 |

## Libros

| # | Prueba | Descripción | Tipo | Endpoint | Resultado Esperado |
|---|--------|-------------|------|----------|--------------------|
| 7 | test_user_can_list_books | Usuario autenticado puede listar libros | Feature | GET /api/v1/books | 200 + lista de libros |
| 8 | test_list_books_fails_without_auth | Listar libros sin token | Feature | GET /api/v1/books | 401 |
| 9 | test_bibliotecario_can_create_book | Bibliotecario puede crear un libro | Feature | POST /api/v1/books | 201 + datos del libro |
| 10 | test_create_book_fails_for_student | Estudiante no puede crear libros | Feature | POST /api/v1/books | 403 |
| 11 | test_bibliotecario_can_update_book | Bibliotecario puede actualizar un libro | Feature | PUT /api/v1/books/{id} | 200 + datos actualizados |
| 12 | test_update_book_fails_for_docente | Docente no puede actualizar libros | Feature | PUT /api/v1/books/{id} | 403 |
| 13 | test_bibliotecario_can_delete_book | Bibliotecario puede eliminar un libro | Feature | DELETE /api/v1/books/{id} | 200 |
| 14 | test_delete_book_fails_for_student | Estudiante no puede eliminar libros | Feature | DELETE /api/v1/books/{id} | 403 |
| 15 | test_user_can_see_book_detail | Usuario autenticado puede ver detalle de un libro | Feature | GET /api/v1/books/{id} | 200 + detalle del libro |

## Préstamos

| # | Prueba | Descripción | Tipo | Endpoint | Resultado Esperado |
|---|--------|-------------|------|----------|--------------------|
| 16 | test_docente_can_create_loan | Docente puede prestar un libro disponible | Feature | POST /api/v1/loans | 201 + datos del préstamo |
| 17 | test_estudiante_can_create_loan | Estudiante puede prestar un libro disponible | Feature | POST /api/v1/loans | 201 + datos del préstamo |
| 18 | test_bibliotecario_cannot_create_loan | Bibliotecario no puede prestar libros | Feature | POST /api/v1/loans | 403 |
| 19 | test_user_can_return_loan | Usuario puede devolver un préstamo activo | Feature | POST /api/v1/loans/{id}/return | 200 |
| 20 | test_bibliotecario_cannot_return_loan | Bibliotecario no puede registrar devolución | Feature | POST /api/v1/loans/{id}/return | 403 |
| 21 | test_user_can_list_loans | Usuario autenticado puede ver historial de préstamos | Feature | GET /api/v1/loans | 200 + lista de préstamos |
| 22 | test_cannot_loan_unavailable_book | No se puede prestar un libro sin stock | Unit | — | false / excepción |
| 23 | test_cannot_return_inactive_loan | No se puede devolver un préstamo no activo | Unit | — | false / excepción |