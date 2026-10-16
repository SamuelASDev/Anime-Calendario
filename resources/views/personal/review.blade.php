<x-app-layout>
    <div class="page-wrap max-w-3xl">
        <x-page-heading eyebrow="CADA IMPRESSÃO CONTA" title="Avaliar anime" :description="$anime->title" />
        <form action="{{ route('personal.completed.review.store', $anime) }}" method="POST" class="surface p-6 sm:p-8 space-y-6">@csrf<x-validation-summary />
            <div class="flex items-center gap-4">@if($anime->image)<img src="{{ $anime->image }}" alt="" class="w-16 h-24 object-cover rounded-lg">@endif<div><h2 class="font-semibold">{{ $anime->title }}</h2><p class="card-note mt-2">Que nota você daria para essa história?</p></div></div>
            <label class="block text-sm text-slate-300" for="rating">Sua nota, de 1 a 10<input id="rating" type="number" name="rating" min="1" max="10" class="form-field" value="{{ old('rating', $myMeta->rating ?? '') }}" placeholder="Escolha uma nota"></label>
            <label class="block text-sm text-slate-300" for="comment">Sua avaliação<textarea id="comment" name="comment" rows="6" maxlength="2000" class="form-field" placeholder="O que mais marcou você? Se houver spoilers, avise no início do comentário.">{{ old('comment', $myMeta->comment ?? '') }}</textarea></label>
            <p class="card-note">A visibilidade da sua avaliação segue as configurações de privacidade do perfil.</p>
            <div class="flex gap-3"><button class="btn btn-primary"><x-icon name="star" />Salvar avaliação</button><a class="btn btn-secondary" href="{{ route('anime.show', $anime) }}">Voltar ao anime</a></div>
        </form>
    </div>
</x-app-layout>
