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
| **Nota Atribuída** | **{{ $evaluationGrade.'/'.$internshipWeight }}** |
@endcomponent

@if(!empty($internship->evaluation_considerations) || !empty($internship->evaluation_performance_issues))
### Observações do Supervisor

@if(!empty($internship->evaluation_considerations))
**Você tem alguma consideração a apresentar em relação ao estagiário que esteve sob sua supervisão?**

*Resposta do supervisor:*
> {{ $internship->evaluation_considerations }}

@endif

@if(!empty($internship->evaluation_performance_issues))
**Você percebeu algum aspecto que possa ter prejudicado o rendimento do aluno no estágio? Se sim, quais?**

*Resposta do supervisor:*
> {{ $internship->evaluation_performance_issues }}

@endif
@endif
---

A sua carga horária foi integralmente cumprida e o estágio encontra-se agora **concluído**.

Caso tenha alguma dúvida sobre a avaliação, entre em contacto com a coordenação do seu curso.

Atenciosamente,<br>
**{{ config('app.name') }}**

@endcomponent
