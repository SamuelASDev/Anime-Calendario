<x-app-layout><div class="page-wrap">
    <x-page-heading eyebrow="DO SEU JEITO" title="Configurações" description="Cuide da sua conta, personalize o perfil e escolha o que compartilhar."><x-slot:actions>@if($user->username)<a class="btn btn-secondary" href="{{ route('profile.show', $user->username) }}"><x-icon name="user" />Ver meu perfil</a>@endif</x-slot:actions></x-page-heading>
    @if(session('success'))<p class="flash" role="status">{{ session('success') }}</p>@endif
    <div class="settings-grid"><nav class="settings-sidebar" aria-label="Configurações da conta"><a href="#appearance">Aparência e privacidade</a><a href="#account">Dados da conta</a><a href="#password">Senha e segurança</a><a href="#delete-account">Excluir conta</a></nav>
        <div><div id="appearance" class="surface settings-section">@include('profile.partials.update-public-profile-form')</div><div id="account" class="surface settings-section">@include('profile.partials.update-profile-information-form')</div><div id="password" class="surface settings-section">@include('profile.partials.update-password-form')</div><div id="delete-account" class="surface settings-section">@include('profile.partials.delete-user-form')</div></div>
    </div>
</div></x-app-layout>
