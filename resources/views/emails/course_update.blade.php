# Update: {{ $subject }}

Hello {{ $name }},

We've just updated your course on **Amter.in**.

{{ $message }}

@component('mail::button', ['url' => url('/admin')])
Jump Back In
@endcomponent

Keep practicing,
The Amter Team
