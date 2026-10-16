@if($errors->any())
    <div role="alert" class="rounded-lg border border-red-500 bg-red-500/10 p-4 text-red-200">
        <p class="font-semibold mb-2">Confira os campos abaixo:</p>
        <ul class="list-disc pl-5">
            @foreach($errors->all() as $error) <li>{{ $error }}</li> @endforeach
        </ul>
    </div>
@endif
