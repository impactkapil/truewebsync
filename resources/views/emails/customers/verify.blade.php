@component('mail::message')
# Welcome {{ $customer->name }}

Thank you for registering with us. Below are your login credentials:

**Email:** {{ $customer->email }}

**Password:** {{ $plainPassword }}

@component('mail::button', ['url' => route('customer.verification.verify', ['id' => $customer->id, 'hash' => sha1($customer->email)])])
Verify Email
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
