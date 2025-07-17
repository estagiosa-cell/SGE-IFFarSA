<x-mail::message>
{{-- Saudação --}}
# Olá!

{{-- Linhas de Introdução --}}
@foreach ($introLines as $line)
{{ $line }}

@endforeach

{{-- Botão de Ação --}}
@isset($actionText)
<x-mail::button :url="$actionUrl" color="primary">
{{ $actionText }}
</x-mail::button>
@endisset

{{-- Linhas de Saída --}}
@foreach ($outroLines as $line)
{{ $line }}

@endforeach

{{-- Saudação Final --}}
Atenciosamente,<br>
{{ config('app.name') }}

{{-- Rodapé com o Link --}}
@isset($actionText)
<x-slot:subcopy>
Se você estiver com problemas para clicar no botão "{{ $actionText }}", copie e cole a URL abaixo no seu navegador: <span class="break-all">[{{ $actionUrl }}]({{ $actionUrl }})</span>
</x-slot:subcopy>
@endisset
</x-mail::message>
