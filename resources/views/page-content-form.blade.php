<div class="py-12 website-base-dt-forms" @if ($modelName ?? null) x-data="getNewForm('{{ $modelName }}')" @endif>
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-12">
                            @error('name'){{ $message }}@enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            @if (is_array($contentView))
                                @foreach($contentView as $contentViewItem)
                                    @include($contentViewItem)
                                @endforeach
                            @else
                                @include($contentView)
                            @endif

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
