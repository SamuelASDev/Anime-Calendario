@if($paginator->hasPages())
    <nav class="pagination-bar" role="navigation" aria-label="Paginação">
        <p>{{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} de <strong>{{ $paginator->total() }}</strong></p>
        <div class="pagination-controls">
            @if($paginator->onFirstPage())<span class="btn btn-secondary btn-sm opacity-40" aria-disabled="true">← Anterior</span>@else<a class="btn btn-secondary btn-sm" href="{{ $paginator->previousPageUrl() }}" rel="prev">← Anterior</a>@endif
            <div class="pagination-pages">@foreach($elements as $element) @if(is_string($element))<span class="text-slate-600">{{ $element }}</span>@else @foreach($element as $page => $url) @if($page === $paginator->currentPage())<span class="page-number current" aria-current="page">{{ $page }}</span>@else<a href="{{ $url }}" class="page-number" aria-label="Ir para a página {{ $page }}">{{ $page }}</a>@endif @endforeach @endif @endforeach</div>
            @if($paginator->hasMorePages())<a class="btn btn-secondary btn-sm" href="{{ $paginator->nextPageUrl() }}" rel="next">Próxima →</a>@else<span class="btn btn-secondary btn-sm opacity-40" aria-disabled="true">Próxima →</span>@endif
        </div>
    </nav>
@endif
