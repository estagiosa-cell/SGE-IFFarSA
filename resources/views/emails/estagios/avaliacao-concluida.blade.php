@component('mail::message')
# Olá, {{ $studentName }}!

Temos o prazer de informar que a **avaliação do seu estágio** foi registada com sucesso no sistema.

---

## Detalhes da Avaliação

@component('mail::table')
| | |
|:---|:---|
| **Curso** | {{ $courseName }} |
| **Tipo de Estágio** | {{ $internshipType }} |
| **Nota Atribuída** | **{{ $evaluationGrade }}** |
@endcomponent

---

A sua carga horária foi integralmente cumprida e o estágio encontra-se agora **concluído**.

Caso tenha alguma dúvida sobre a avaliação, entre em contacto com a coordenação do seu curso.

Atenciosamente,<br>
**{{ config('app.name') }}**

@endcomponent
