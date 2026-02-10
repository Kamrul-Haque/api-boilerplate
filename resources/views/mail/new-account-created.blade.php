{{--@formatter:off--}}
<x-mail::message>
# Welcome Aboard

Dear {{ $user->name }},<br>
we have created an account so that you can access our online platform.

### Account Details:
User ID: {{ $user->email }}<br>
Password: {{ $password }}

<x-mail::button :url="route('login')">
    Visit
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
{{--@formatter:off--}}
